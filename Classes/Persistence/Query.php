<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Persistence;

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Backend\Search\LiveSearch\QueryParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception;
use TYPO3\CMS\Extbase\Persistence\ForwardCompatibleQueryInterface;
use TYPO3\CMS\Extbase\Persistence\ForwardCompatibleQueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidNumberOfConstraintsException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\AndInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\NotInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;

/**
 * The Query class used to run queries against the database
 *
 * @todo v12: Drop ForwardCompatibleQueryInterface when merged into QueryInterface
 * @todo v12: Candidate to declare final - Can be decorated or standalone class implementing the interface
 */
class Query implements QueryInterface, ForwardCompatibleQueryInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var DataMapFactory
     */
    protected $dataMapFactory;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var QueryObjectModelFactory
     */
    protected $qomFactory;

    /**
     * @var QueryParser
     */
    protected $queryParser;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SelectorInterface
     */
    protected $source;

    /**
     * Empty values are removed automatically in ContentObjectRenderer
     *
     * @var array
     */
    protected $conf = [
        'uidInList' => '',
        'pidInList' => '',
        'recursive' => '',
        'orderBy' => '',
        'groupBy' => '',
        'max' => '',
        'begin' => '',
        'where' => '',
        'languageField' => '',
        'includeRecordsWithoutDefaultTranslation' => '',
        'selectFields' => '',
        'join' => '',
        'leftjoin' => '',
        'rightjoin' => '',
        'markers' => '',
    ];

    /**
     * @var array
     */
    protected $constraint = [];

    /**
     * @var array
     */
    protected $orderings = [];

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * The query settings.
     *
     * @var QuerySettingsInterface
     */
    protected $querySettings;

    /**
     * @var QueryInterface|null
     * @internal
     */
    protected $parentQuery;

    public function __construct(
        DataMapFactory $dataMapFactory,
        ConfigurationManagerInterface $configurationManager,
        QueryObjectModelFactory $qomFactory,
        QueryParser $queryParser,
        ContainerInterface $container
    ) {
        $this->dataMapFactory = $dataMapFactory;
        $this->configurationManager = $configurationManager;
        $this->qomFactory = $qomFactory;
        $this->queryParser = $queryParser;
        $this->container = $container;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ?QueryInterface
     * @internal
     */
    public function getParentQuery(): ?QueryInterface
    {
        return $this->parentQuery;
    }

    /**
     * @param QueryInterface|null $parentQuery
     * @internal
     */
    public function setParentQuery(?QueryInterface $parentQuery): void
    {
        $this->parentQuery = $parentQuery;
    }

    /**
     * Sets the Query Settings. These Query settings must match the settings expected by
     * the specific Storage Backend.
     *
     * @param QuerySettingsInterface $querySettings The Query Settings
     */
    public function setQuerySettings(QuerySettingsInterface $querySettings)
    {
        $this->querySettings = $querySettings;
    }

    /**
     * Returns the Query Settings.
     *
     * @throws Exception
     */
    public function getQuerySettings(): QuerySettingsInterface
    {
        if (!$this->querySettings instanceof QuerySettingsInterface) {
            throw new Exception('Tried to get the query settings without setting them before.', 1248689115);
        }

        return $this->querySettings;
    }

    /**
     * Returns the type (model classname) this query cares for.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the source to fetch the result from
     *
     * @param SourceInterface|SelectorInterface $source
     */
    public function setSource(SourceInterface $source): void
    {
        // Do not allow to overwrite source, if it is not of type SelectorInterface
        if ($source instanceof SelectorInterface) {
            $this->source = $source;
        }
    }

    /**
     * Returns the selector's name (tablename) or an empty string, if the source is not a SelectorInterface
     */
    public function getTableName(): string
    {
        $source = $this->getSource();
        if ($source instanceof SelectorInterface) {
            return $source->getSelectorName();
        }

        return '';
    }

    /**
     * Gets the node-tuple source for this query.
     */
    public function getSource(): SourceInterface
    {
        if ($this->source === null) {
            $this->source = $this->qomFactory->selector($this->getType(), $this->dataMapFactory->buildDataMap($this->getType())->getTableName());
        }

        return $this->source;
    }

    /**
     * Executes the query against the database and returns the result
     *
     * @return QueryResultInterface|array The query result object or an array if $returnRawQueryResult is TRUE
     */
    public function execute($returnRawQueryResult = false)
    {
        if (isset($this->conf['pidInList']) && $this->conf['pidInList'] === '') {
            $this->conf['pidInList'] = implode(',', $this->getQuerySettings()->getStoragePageIds());
        }

        if ($returnRawQueryResult) {
            return $this->configurationManager->getContentObject()->getRecords(
                $this->getTableName(),
                $this->getConf()
            );
        }

        if ($this->container->has(QueryResult::class)) {
            $queryResult = $this->container->get(QueryResult::class);
            if ($queryResult instanceof ForwardCompatibleQueryResultInterface) {
                $queryResult->setQuery($this);
                return $queryResult;
            }
        }

        // @deprecated since v11, will be removed in v12. Fallback to ObjectManager, drop together with ForwardCompatibleQueryResultInterface.
        return GeneralUtility::makeInstance(ObjectManager::class)->get(QueryResult::class, $this);
    }

    /**
     * Sets the property names to order the result by. Expected like this:
     *
     * array(
     *     'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     *     'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     * where 'foo' and 'bar' are property names.
     */
    public function setOrderings(array $orderings): QueryInterface
    {
        $orderBy = [];
        $this->orderings = $orderings;

        $dataMap = $this->dataMapFactory->buildDataMap($this->type);
        foreach ($this->orderings as $propertyName => $orderDirection) {
            $columnMap = $dataMap->getColumnMap($propertyName);
            $orderBy[] = $columnMap->getColumnName() . ' ' . $orderDirection;
        }

        $this->conf['orderBy'] = implode(', ', $orderBy);

        return $this;
    }

    /**
     * Returns the property names to order the result by. Like this:
     *
     * array(
     *     'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     *     'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     */
    public function getOrderings(): array
    {
        return $this->orderings;
    }

    /**
     * Sets the maximum size of the result set to limit. Returns $this to allow
     * for chaining (fluid interface)
     *
     * @param int $limit
     * @throws \InvalidArgumentException
     */
    public function setLimit($limit): QueryInterface
    {
        if (!is_int($limit) || $limit < 1) {
            throw new \InvalidArgumentException('The limit must be an integer >= 1', 1245071870);
        }

        $this->limit = $limit;

        $this->conf['max'] = (string)$this->limit;

        return $this;
    }

    /**
     * Resets a previously set maximum size of the result set. Returns $this to allow
     * for chaining (fluid interface)
     */
    public function unsetLimit(): QueryInterface
    {
        unset($this->limit);

        return $this;
    }

    /**
     * Returns the maximum size of the result set to limit.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets the start offset of the result set to offset. Returns $this to
     * allow for chaining (fluid interface)
     *
     * @param int $offset
     * @throws \InvalidArgumentException
     * @return QueryInterface
     */
    public function setOffset($offset)
    {
        if (!is_int($offset) || $offset < 0) {
            throw new \InvalidArgumentException('The offset must be a positive integer', 1245071872);
        }

        $this->offset = $offset;

        $this->conf['begin'] = (string)$this->offset;

        return $this;
    }

    /**
     * Returns the start offset of the result set.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * The constraint used to limit the result set.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint
     * @return QueryInterface
     */
    public function matching($constraint): QueryInterface
    {
        $dataMap = $this->dataMapFactory->buildDataMap($this->type);

        $where = [];
        if ($comparison->getOperator() === QueryInterface::OPERATOR_CONTAINS) {

        }
        if ($constraint instanceof ComparisonInterface) {
            if ($constraint->getOperator() === QueryInterface::OPERATOR_EQUAL_TO) {
                $where[] = sprintf(
                    '({#%s} = {#%s})',
                    $dataMap->getColumnMap($constraint->getOperand1()->getPropertyName())->getColumnName(),
                    $constraint->getOperand2()
                );
            }
        }

        $this->constraint = $constraint;

        return $this;
    }

    /**
     * Returns the statement of this query.
     */
    public function getStatement()
    {
        // Not implemented
        return null;
    }

    /**
     * Gets the constraint for this query.
     */
    public function getConstraint(): array
    {
        return $this->constraint;
    }

    /**
     * Gets the TypoScript query parts
     */
    public function getConf(): array
    {
        return $this->conf;
    }

    /**
     * Performs a logical conjunction of the given constraints. The method takes one or more constraints and concatenates them with a boolean AND.
     * It also accepts a single array of constraints to be concatenated.
     *
     * @param ConstraintInterface $constraint1 First constraint
     * @param ConstraintInterface $constraint2 Second constraint
     * @param ConstraintInterface ...$furtherConstraints Further constraints
     * @throws InvalidNumberOfConstraintsException
     * @return AndInterface
     */
    public function logicalAnd($constraint1, $constraint2 = null, ...$furtherConstraints)
    {
        /*
         * todo: Deprecate accepting an array as $constraint1
         *       Add param type hints for $constraint1 and $constraint2
         *       Make $constraint2 mandatory
         *       Add AndInterface return type hint
         *       Adjust method signature in interface
         */
        $constraints = [];
        if (is_array($constraint1)) {
            $constraints = array_merge($constraints, $constraint1);
        } else {
            $constraints[] = $constraint1;
        }

        $constraints[] = $constraint2;
        $constraints = array_merge($constraints, $furtherConstraints);
        $constraints = array_filter($constraints, static function ($constraint): bool {
            return $constraint instanceof ConstraintInterface;
        });

        // todo: remove this check as soon as first and second constraint are mandatory
        if (count($constraints) < 1) {
            throw new InvalidNumberOfConstraintsException('There must be at least one constraint or a non-empty array of constraints given.', 1268056288);
        }

        $resultingConstraint = array_shift($constraints);
        foreach ($constraints as $constraint) {
            $resultingConstraint = $this->qomFactory->_and($resultingConstraint, $constraint);
        }

        return $resultingConstraint;
    }

    /**
     * Performs a logical disjunction of the two given constraints
     *
     * @param ConstraintInterface $constraint1 First constraint
     * @param ConstraintInterface $constraint2 Second constraint
     * @param ConstraintInterface ...$furtherConstraints Further constraints
     * @throws InvalidNumberOfConstraintsException
     * @return OrInterface
     */
    public function logicalOr($constraint1, $constraint2 = null, ...$furtherConstraints)
    {
        /*
         * todo: Deprecate accepting an array as $constraint1
         *       Add param type hints for $constraint1 and $constraint2
         *       Make $constraint2 mandatory
         *       Add AndInterface return type hint
         *       Adjust method signature in interface
         */
        $constraints = [];
        if (is_array($constraint1)) {
            $constraints = array_merge($constraints, $constraint1);
        } else {
            $constraints[] = $constraint1;
        }

        $constraints[] = $constraint2;
        $constraints = array_merge($constraints, $furtherConstraints);
        $constraints = array_filter($constraints, static function ($constraint): bool {
            return $constraint instanceof ConstraintInterface;
        });

        // todo: remove this check as soon as first and second constraint are mandatory
        if (count($constraints) < 1) {
            throw new InvalidNumberOfConstraintsException('There must be at least one constraint or a non-empty array of constraints given.', 1268056289);
        }

        $resultingConstraint = array_shift($constraints);
        foreach ($constraints as $constraint) {
            $resultingConstraint = $this->qomFactory->_or($resultingConstraint, $constraint);
        }
        return $resultingConstraint;
    }

    /**
     * Performs a logical negation of the given constraint
     *
     * @param ConstraintInterface $constraint Constraint to negate
     * @throws \RuntimeException
     * @return NotInterface
     */
    public function logicalNot(ConstraintInterface $constraint)
    {
        return $this->qomFactory->not($constraint);
    }

    /**
     * Returns an equals criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @param bool $caseSensitive Whether the equality test should be done case-sensitive
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     */
    public function equals($propertyName, $operand, $caseSensitive = true)
    {
        if (is_object($operand) || $caseSensitive) {
            $comparison = $this->qomFactory->comparison(
                $this->qomFactory->propertyValue($propertyName, $this->getTableName()),
                QueryInterface::OPERATOR_EQUAL_TO,
                $operand
            );
        } else {
            $comparison = $this->qomFactory->comparison(
                $this->qomFactory->lowerCase($this->qomFactory->propertyValue($propertyName, $this->getTableName())),
                QueryInterface::OPERATOR_EQUAL_TO,
                mb_strtolower($operand, \TYPO3\CMS\Extbase\Persistence\Generic\Query::CHARSET)
            );
        }
        return $comparison;
    }

    /**
     * Returns a like criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return ComparisonInterface
     */
    public function like($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getTableName()),
            QueryInterface::OPERATOR_LIKE,
            $operand
        );
    }

    /**
     * Returns a "contains" criterion used for matching objects against a query.
     * It matches if the multivalued property contains the given operand.
     *
     * @param string $propertyName The name of the (multivalued) property to compare against
     * @param mixed $operand The value to compare with
     * @return ComparisonInterface
     */
    public function contains($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getTableName()),
            QueryInterface::OPERATOR_CONTAINS,
            $operand
        );
    }

    /**
     * Returns an "in" criterion used for matching objects against a query. It
     * matches if the property's value is contained in the multivalued operand.
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with, multivalued
     * @throws UnexpectedTypeException
     * @return ComparisonInterface
     */
    public function in($propertyName, $operand)
    {
        if (!TypeHandlingUtility::isValidTypeForMultiValueComparison($operand)) {
            throw new UnexpectedTypeException('The "in" operator must be given a multivalued operand (array, ArrayAccess, Traversable).', 1264678095);
        }
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getTableName()),
            QueryInterface::OPERATOR_IN,
            $operand
        );
    }

    /**
     * Returns a less than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return ComparisonInterface
     */
    public function lessThan($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getTableName()),
            QueryInterface::OPERATOR_LESS_THAN,
            $operand
        );
    }

    /**
     * Returns a less or equal than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return ComparisonInterface
     */
    public function lessThanOrEqual($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getTableName()),
            QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO,
            $operand
        );
    }

    /**
     * Returns a greater than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return ComparisonInterface
     */
    public function greaterThan($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getTableName()),
            QueryInterface::OPERATOR_GREATER_THAN,
            $operand
        );
    }

    /**
     * Returns a greater than or equal criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return ComparisonInterface
     */
    public function greaterThanOrEqual($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getTableName()),
            QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,
            $operand
        );
    }

    /**
     * Returns a greater than or equal criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operandLower The value of the lower boundary to compare against
     * @param mixed $operandUpper The value of the upper boundary to compare against
     * @return AndInterface
     * @throws InvalidNumberOfConstraintsException
     */
    public function between($propertyName, $operandLower, $operandUpper)
    {
        return $this->logicalAnd(
            $this->greaterThanOrEqual($propertyName, $operandLower),
            $this->lessThanOrEqual($propertyName, $operandUpper)
        );
    }

    /**
     * Returns the query result count.
     *
     * @return int The query result count
     */
    public function count()
    {
        return $this->execute()->count();
    }
}

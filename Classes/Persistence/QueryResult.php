<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Persistence;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ForwardCompatibleQueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * A lazy result list that is returned by Query::execute()
 *
 * @todo v12: Drop ForwardCompatibleQueryResultInterface when merged into QueryResultInterface
 * @todo v12: Candidate to declare final - Can be decorated or standalone class implementing the interface
 */
class QueryResult implements QueryResultInterface, ForwardCompatibleQueryResultInterface
{
    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var int|null
     */
    protected $numberOfResults;

    /**
     * @var Query|null
     */
    protected $query;

    /**
     * @var array|null
     */
    protected $queryResult;

    public function __construct(
        DataMapper $dataMapper,
        ConfigurationManagerInterface $configurationManager
    ) {
        $this->dataMapper = $dataMapper;
        $this->configurationManager = $configurationManager;
    }

    public function setQuery(QueryInterface $query): void
    {
        $this->query = $query;
        $this->dataMapper->setQuery($query);
    }

    /**
     * Loads the objects this QueryResult is supposed to hold
     */
    protected function initialize()
    {
        if (!is_array($this->queryResult)) {
            $this->queryResult = $this->dataMapper->map(
                $this->query->getType(),
                $this->configurationManager->getContentObject()->getRecords(
                    $this->query->getTableName(),
                    $this->query->getConf()
                )
            );
        }
    }

    /**
     * Returns a clone of the query object
     *
     * @return QueryInterface
     */
    public function getQuery()
    {
        return clone $this->query;
    }

    /**
     * Returns the first object in the result set
     *
     * @return object
     */
    public function getFirst()
    {
        if (is_array($this->queryResult)) {
            $queryResult = $this->queryResult;
            reset($queryResult);
        } else {
            $query = $this->getQuery();
            $query->setLimit(1);
            $queryResult = $this->dataMapper->map(
                $query->getType(),
                $this->configurationManager->getContentObject()->getRecords(
                    $this->query->getTableName(),
                    $this->query->getConf()
                )
            );
        }

        $firstResult = current($queryResult);
        if ($firstResult === false) {
            $firstResult = null;
        }

        return $firstResult;
    }

    /**
     * Returns the number of objects in the result
     *
     * @return int The number of matching objects
     * @todo Set to return type int as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        if ($this->numberOfResults === null) {
            if (is_array($this->queryResult)) {
                $this->numberOfResults = count($this->queryResult);
            } else {
                $conf = $this->query->getConf();
                $conf['select.']['selectFields'] = 'count(*)';
                $statement = $this->configurationManager->getContentObject()->exec_getQuery(
                    $this->query->getTableName(),
                    $conf
                );

                $this->numberOfResults = (int)$statement->fetchOne();
            }
        }
        return $this->numberOfResults;
    }

    /**
     * Returns an array with the objects in the result set
     *
     * @return array
     */
    public function toArray()
    {
        $this->initialize();
        return iterator_to_array($this);
    }

    /**
     * This method is needed to implement the ArrayAccess interface,
     * but it isn't very useful as the offset has to be an integer
     *
     * @param mixed $offset
     * @return bool
     * @see ArrayAccess::offsetExists()
     * @todo Set $offset to mixed type as breaking change in v12.
     * @todo Set to return type bool as breaking change in v12.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        $this->initialize();
        return isset($this->queryResult[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     * @see ArrayAccess::offsetGet()
     * @todo Set $offset to mixed type as breaking change in v12.
     * @todo Set return type to ?mixed as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $this->initialize();
        return $this->queryResult[$offset] ?? null;
    }

    /**
     * This method has no effect on the persisted objects but only on the result set
     *
     * @param mixed $offset
     * @param mixed $value
     * @see ArrayAccess::offsetSet()
     * @todo Set $offset and $value to mixed type as breaking change in v12.
     * @todo Set return type to void as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->initialize();
        $this->numberOfResults = null;
        $this->queryResult[$offset] = $value;
    }

    /**
     * This method has no effect on the persisted objects but only on the result set
     *
     * @param mixed $offset
     * @see ArrayAccess::offsetUnset()
     * @todo Set $offset to mixed type as breaking change in v12.
     * @todo Set return type to void as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->initialize();
        $this->numberOfResults = null;
        unset($this->queryResult[$offset]);
    }

    /**
     * @return mixed
     * @see Iterator::current()
     * @todo Set return type to mixed as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->initialize();
        return current($this->queryResult);
    }

    /**
     * @return mixed
     * @see Iterator::key()
     * @todo Set return type to mixed as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        $this->initialize();
        return key($this->queryResult);
    }

    /**
     * @see Iterator::next()
     * @todo Set return type to void as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->initialize();
        next($this->queryResult);
    }

    /**
     * @see Iterator::rewind()
     * @todo Set return type to void as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->initialize();
        reset($this->queryResult);
    }

    /**
     * @return bool
     * @see Iterator::valid()
     * @todo Set return type to bool as breaking patch in v12.
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        $this->initialize();
        return current($this->queryResult) !== false;
    }
}

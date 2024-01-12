<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Repository;

use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Event\ModifyQueryToFindCompanyByLetterEvent;
use JWeiland\Yellowpages2\Event\ModifyQueryToSearchForCompaniesEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository to retrieve company records
 *
 * @method Company|null findByIdentifier(int $companyUid)
 * @method QueryResultInterface findByFeUser(int $frontendUserUid)
 */
class CompanyRepository extends Repository implements HiddenRepositoryInterface
{
    protected $defaultOrderings = [
        'company' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function findHiddenObject($value, string $property = 'uid'): ?object
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(['disabled']);
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query->matching($query->equals($property, $value))->execute()->getFirst();
    }

    public function findByLetter(string $letter, array $settings = []): QueryResultInterface
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $constraints = [];

        if ($letter) {
            $glossaryService = GeneralUtility::makeInstance(GlossaryService::class);
            $constraints[] = $glossaryService->getLetterConstraintForExtbaseQuery(
                $query,
                'company',
                $letter
            );
        }

        if ($settings['presetTrade']) {
            $orConstraints = [
                $query->contains('mainTrade', (int)$settings['presetTrade']),
                $query->contains('trades', (int)$settings['presetTrade']),
            ];

            $constraints[] = $query->logicalOr(...$orConstraints);
        }

        if ($settings['district']) {
            $constraints[] = $query->equals('district', (int)$settings['district']);
        }

        $queryResult = $query->execute();
        if ($constraints !== []) {
            $queryResult = $query->matching($query->logicalAnd(...$constraints))->execute();
        }

        $this->eventDispatcher->dispatch(new ModifyQueryToFindCompanyByLetterEvent($queryResult, $settings));

        return $queryResult;
    }

    public function searchCompanies(string $search, int $categoryUid, array $settings): QueryResultInterface
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $constraints = [];
        $longStreetSearch = $smallStreetSearch = trim($search);

        // unify street search
        if (strtolower(mb_substr($search, -6)) === 'straße') {
            $smallStreetSearch = str_ireplace('straße', 'str', $search);
        }
        if (strtolower(mb_substr($search, -4)) === 'str.') {
            $longStreetSearch = str_ireplace('str.', 'straße', $search);
            $smallStreetSearch = str_ireplace('str.', 'str', $search);
        }
        if (strtolower(mb_substr($search, -3)) === 'str') {
            $longStreetSearch = str_ireplace('str', 'straße', $search);
        }

        if ($longStreetSearch !== '') {
            $orConstraints = [];
            $orConstraints[] = $query->like('company', '%' . $search . '%');
            $orConstraints[] = $query->like('street', '%' . $smallStreetSearch . '%');
            $orConstraints[] = $query->like('street', '%' . $longStreetSearch . '%');

            $constraints[] = $query->logicalOr(...$orConstraints);
        }

        if ($categoryUid !== 0) {
            $orConstraints = [
                $query->contains('mainTrade', $categoryUid),
                $query->contains('trades', $categoryUid),
            ];

            $constraints[] = $query->logicalOr(...$orConstraints);
        }

        if ((int)($settings['district'] ?? 0) > 0) {
            $constraints[] = $query->equals('district', (int)$settings['district']);
        }

        $queryResult = $query->execute();
        if ($constraints !== []) {
            $queryResult = $query->matching($query->logicalAnd(...$constraints))->execute();
        }

        $this->eventDispatcher->dispatch(
            new ModifyQueryToSearchForCompaniesEvent($queryResult, $search, $categoryUid, $settings)
        );

        return $queryResult;
    }

    /**
     * Find all records which are older than given days.
     * Hint: Needed by scheduler
     *
     * @return QueryResultInterface|Company[]
     */
    public function findOlderThan(int $days): QueryResultInterface
    {
        $today = date('U');
        $history = $today - ($days * 60 * 60 * 24);

        $query = $this->createQuery();

        return $query->matching($query->lessThan('tstamp', $history))->execute();
    }

    /**
     * Declared as "public" as needed by Glossary API
     */
    public function getExtbaseQueryToFindAllEntries(): QueryResultInterface
    {
        return $this->createQuery()->execute();
    }
}

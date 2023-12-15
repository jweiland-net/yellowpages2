<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Traits;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * This is a modified copy of the Typo3DbQueryParser::getLanguageStatement as the TYPO3 method
 * was not public available.
 */
trait GetLanguageStatementTrait
{
    private function getLanguageStatement(
        string $tableName,
        string $tableAlias,
        Typo3QuerySettings $querySettings,
        QueryBuilder $queryBuilder
    ): array {
        if (empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])) {
            return [];
        }

        // Select all entries for the current language
        // If any language is set -> get those entries which are not translated yet
        // They will be removed by \TYPO3\CMS\Core\Domain\Repository\PageRepository::getRecordOverlay if not matching overlay mode
        $languageField = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];

        $transOrigPointerField = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? '';
        if (!$transOrigPointerField || !$querySettings->getLanguageUid()) {
            return [$queryBuilder->expr()->in(
                $tableAlias . '.' . $languageField,
                [$querySettings->getLanguageUid(), -1]
            )];
        }

        $mode = $querySettings->getLanguageOverlayMode();
        if (!$mode) {
            return [$queryBuilder->expr()->in(
                $tableAlias . '.' . $languageField,
                [$querySettings->getLanguageUid(), -1]
            )];
        }

        $defLangTableAlias = $tableAlias . '_dl';
        $defaultLanguageRecordsSubSelect = $queryBuilder->getConnection()->createQueryBuilder();
        $defaultLanguageRecordsSubSelect
            ->select($defLangTableAlias . '.uid')
            ->from($tableName, $defLangTableAlias)
            ->where(
                $defaultLanguageRecordsSubSelect->expr()->and(
                    $defaultLanguageRecordsSubSelect->expr()->eq($defLangTableAlias . '.' . $transOrigPointerField, 0),
                    $defaultLanguageRecordsSubSelect->expr()->eq($defLangTableAlias . '.' . $languageField, 0)
                )
            );

        $andConditions = [];
        // records in language 'all'
        $andConditions[] = $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, -1);
        // translated records where a default language exists
        $andConditions[] = $queryBuilder->expr()->and(
            $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, $querySettings->getLanguageUid()),
            $queryBuilder->expr()->in(
                $tableAlias . '.' . $transOrigPointerField,
                $defaultLanguageRecordsSubSelect->getSQL()
            )
        );

        if ($mode !== 'hideNonTranslated') {
            // $mode = TRUE
            // returns records from current language which have default language
            // together with not translated default language records
            $translatedOnlyTableAlias = $tableAlias . '_to';
            $queryBuilderForSubselect = $queryBuilder->getConnection()->createQueryBuilder();
            $queryBuilderForSubselect
                ->select($translatedOnlyTableAlias . '.' . $transOrigPointerField)
                ->from($tableName, $translatedOnlyTableAlias)
                ->where(
                    $queryBuilderForSubselect->expr()->and(
                        $queryBuilderForSubselect->expr()->gt($translatedOnlyTableAlias . '.' . $transOrigPointerField, 0),
                        $queryBuilderForSubselect->expr()->eq($translatedOnlyTableAlias . '.' . $languageField, (int)$querySettings->getLanguageUid())
                    )
                );
            // records in default language, which do not have a translation
            $andConditions[] = $queryBuilder->expr()->and(
                $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, 0),
                $queryBuilder->expr()->notIn(
                    $tableAlias . '.uid',
                    $queryBuilderForSubselect->getSQL()
                )
            );
        }

        return $andConditions;
    }
}

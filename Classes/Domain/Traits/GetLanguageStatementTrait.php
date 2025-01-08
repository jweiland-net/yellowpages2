<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Traits;

use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
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
    ): array | string {
        if (empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])) {
            return [];
        }

        // Select all entries for the current language
        // If any language is set -> get those entries which are not translated yet
        // They will be removed by \TYPO3\CMS\Core\Domain\Repository\PageRepository::getRecordOverlay if not matching overlay mode
        $languageField = (string)$GLOBALS['TCA'][$tableName]['ctrl']['languageField'];

        $languageAspect = $querySettings->getLanguageAspect();

        $transOrigPointerField = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? '';
        if (!$transOrigPointerField || !$languageAspect->getContentId()) {
            return $queryBuilder->expr()->in(
                $tableAlias . '.' . $languageField,
                [$languageAspect->getContentId(), -1]
            );
        }

        if (!$languageAspect->doOverlays()) {
            return $queryBuilder->expr()->in(
                $tableAlias . '.' . $languageField,
                [$languageAspect->getContentId(), -1]
            );
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
            $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, $languageAspect->getContentId()),
            $queryBuilder->expr()->in(
                $tableAlias . '.' . $transOrigPointerField,
                $defaultLanguageRecordsSubSelect->getSQL()
            )
        );

        // Records in translation with no default language
        if ($languageAspect->getOverlayType() === LanguageAspect::OVERLAYS_ON_WITH_FLOATING) {
            $andConditions[] = $queryBuilder->expr()->and(
                $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, $languageAspect->getContentId()),
                $queryBuilder->expr()->eq($tableAlias . '.' . $transOrigPointerField, 0),
                $queryBuilder->expr()->notIn(
                    $tableAlias . '.' . $transOrigPointerField,
                    $defaultLanguageRecordsSubSelect->getSQL()
                )
            );
        }

        if ($languageAspect->getOverlayType() === LanguageAspect::OVERLAYS_MIXED) {
            // returns records from current language which have a default language
            // together with not translated default language records
            $translatedOnlyTableAlias = $tableAlias . '_to';
            $queryBuilderForSubselect = $queryBuilder->getConnection()->createQueryBuilder();
            $queryBuilderForSubselect
                ->select($translatedOnlyTableAlias . '.' . $transOrigPointerField)
                ->from($tableName, $translatedOnlyTableAlias)
                ->where(
                    $queryBuilderForSubselect->expr()->and(
                        $queryBuilderForSubselect->expr()->gt($translatedOnlyTableAlias . '.' . $transOrigPointerField, 0),
                        $queryBuilderForSubselect->expr()->eq($translatedOnlyTableAlias . '.' . $languageField, $languageAspect->getContentId())
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

        return $queryBuilder->expr()->or(...$andConditions);
    }
}

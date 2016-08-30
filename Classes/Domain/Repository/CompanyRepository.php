<?php
namespace JWeiland\Yellowpages2\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use JWeiland\Yellowpages2\Domain\Model\Company;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CompanyRepository extends Repository
{

    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'company' => QueryInterface::ORDER_ASCENDING
    );

    /**
     * charset converter
     * We need some UTF-8 compatible functions for search
     *
     * @var \TYPO3\CMS\Core\Charset\CharsetConverter
     * @inject
     */
    protected $charsetConverter;

    /**
     * find company by uid whether it is hidden or not
     *
     * @param int $companyUid
     * @return \JWeiland\Yellowpages2\Domain\Model\Company
     */
    public function findHiddenEntryByUid($companyUid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(array('disabled'));
        
        /** @var Company $company */
        $company = $query->matching($query->equals('uid', (int)$companyUid))->execute()->getFirst();
        return $company;
    }

    /**
     * find all records starting with given letter
     *
     * @param string $letter
     * @param array $settings
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByStartingLetter($letter, array $settings = array())
    {
        $query = $this->createQuery();

        $constraintAnd = array();

        if ($letter) {
            $constraintOr = array();
            if ($letter == '0-9') {
                $constraintOr[] = $query->like('company', '0%');
                $constraintOr[] = $query->like('company', '1%');
                $constraintOr[] = $query->like('company', '2%');
                $constraintOr[] = $query->like('company', '3%');
                $constraintOr[] = $query->like('company', '4%');
                $constraintOr[] = $query->like('company', '5%');
                $constraintOr[] = $query->like('company', '6%');
                $constraintOr[] = $query->like('company', '7%');
                $constraintOr[] = $query->like('company', '8%');
                $constraintOr[] = $query->like('company', '9%');
            } else {
                $constraintOr[] = $query->like('company', $letter . '%');
            }
            $constraintAnd[] = $query->logicalOr($constraintOr);
        }

        if ($settings['district']) {
            $constraintAnd[] = $query->equals('district', $settings['district']);
        }

        if ($settings['showWspMembers']) {
            $constraintAnd[] = $query->equals('wspMember', $settings['showWspMembers']);
        }

        if (count($constraintAnd)) {
            return $query->matching($query->logicalAnd($constraintAnd))->execute();
        } else {
            return $query->execute();
        }
    }

    /**
     * get an array with available starting letters
     *
     * @param boolean $isWsp
     * @return array
     */
    public function getStartingLetters($isWsp)
    {
        if ($isWsp) {
            $addWhere = 'AND wsp_member=1';
        } else {
            $addWhere = '';
        }
        /** @var Query $query */
        $query = $this->createQuery();
        return $query->statement('
			SELECT UPPER(LEFT(company, 1)) as letter
			FROM tx_yellowpages2_domain_model_company
			WHERE 1=1 ' . $addWhere .
            BackendUtility::BEenableFields('tx_yellowpages2_domain_model_company')    .
            BackendUtility::deleteClause('tx_yellowpages2_domain_model_company')    . '
			GROUP BY letter
			ORDER by letter;
		')->execute(true);
    }

    /**
     * search records
     *
     * @param string $search
     * @param int $category
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function searchCompanies($search, $category)
    {
        // strtolower is not UTF-8 compatible
        // $search = strtolower($search);
        $longStreetSearch = $search;
        $smallStreetSearch = $search;

        // unify street search
        if (strtolower($this->charsetConverter->utf8_substr($search, -6) === 'straße')) {
            $smallStreetSearch = str_ireplace('straße', 'str', $search);
        }
        if (strtolower($this->charsetConverter->utf8_substr($search, -4)) === 'str.') {
            $longStreetSearch = str_ireplace('str.', 'straße', $search);
            $smallStreetSearch = str_ireplace('str.', 'str', $search);
        }
        if (strtolower($this->charsetConverter->utf8_substr($search, -3)) === 'str') {
            $longStreetSearch = str_ireplace('str', 'straße', $search);
        }

        /** @var Query $query */
        $query = $this->createQuery();

        $constraint = array();
        $constraint[] = $query->like('company', '%' . $search . '%');
        $constraint[] = $query->like('street', '%' . $smallStreetSearch . '%');
        $constraint[] = $query->like('street', '%' . $longStreetSearch . '%');

        if ($category) {
            return $query->matching(
                $query->logicalAnd(
                    $query->logicalOr($constraint),
                    $query->logicalOr(array(
                        $query->equals('mainTrade', $category),
                        $query->contains('trades', $category),
                    ))
                )
            )->execute();
        } else {
            return $query->matching(
                $query->logicalOr($constraint)
            )->execute();
        }
    }

    /**
     * return grouped categories
     *
     * @return array
     */
    public function getGroupedCategories()
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $results = $query->statement('
			SELECT sys_category.uid, sys_category.title
			FROM sys_category, tx_yellowpages2_domain_model_company
			WHERE tx_yellowpages2_domain_model_company.main_trade = sys_category.uid
			AND tx_yellowpages2_domain_model_company.main_trade > 0 ' .
                BackendUtility::BEenableFields('sys_category') .
                BackendUtility::deleteClause('sys_category') .
                BackendUtility::BEenableFields('tx_yellowpages2_domain_model_company') .
                BackendUtility::deleteClause('tx_yellowpages2_domain_model_company') . '
				GROUP BY sys_category.uid
				ORDER BY sys_category.title'
        )->execute(true);

        $groupedCategories = array();
        $groupedCategories[] = LocalizationUtility::translate('allBranches', 'yellowpages2');
        foreach ($results as $result) {
            $groupedCategories[$result['uid']] = $result['title'];
        }

        return $groupedCategories;
    }

    /**
     * find all records which are older than given days
     * Hint: Needed by scheduler
     *
     * @param int $days
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findOlderThan($days)
    {
        $days = (int) $days;
        $today = date('U');
        $history = $today - ($days * 60 * 60 * 24);
        $query = $this->createQuery();
        return $query->matching($query->lessThan('tstamp', $history))->execute();
    }
}

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
use TYPO3\CMS\Core\Database\PreparedStatement;
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
    
        if ($settings['showWspMembers']) {
            $constraintAnd[] = $query->equals('wspMember', $settings['showWspMembers']);
        }
    
        if ($settings['presetTrade']) {
            $constraintAnd[] = $query->logicalOr(array(
                $query->contains('mainTrade.uid', $settings['presetTrade']),
                $query->contains('trades.uid', $settings['presetTrade'])
            ));
        }
    
        if ($settings['district']) {
            $constraintAnd[] = $query->equals('district', $settings['district']);
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
        $addWhere = '';
        if ($isWsp) {
            $addWhere = 'AND wsp_member=1';
        }
        /** @var Query $query */
        $query = $this->createQuery();
        return $query->statement('
            SELECT UPPER(LEFT(company, 1)) as letter
            FROM tx_yellowpages2_domain_model_company
            WHERE 1=1 ' . $addWhere .
            BackendUtility::BEenableFields('tx_yellowpages2_domain_model_company') .
            BackendUtility::deleteClause('tx_yellowpages2_domain_model_company') . '
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
        /** @var Query $query */
        $query = $this->createQuery();
    
        // strtolower is not UTF-8 compatible
        // $search = strtolower($search);
        $longStreetSearch = trim($search);
        $smallStreetSearch = trim($search);

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
        
        $constraint = array();
        
        if (!empty($longStreetSearch)) {
            $searchConstraint = array();
            $searchConstraint[] = $query->like('company', '%' . $search . '%');
            $searchConstraint[] = $query->like('street', '%' . $smallStreetSearch . '%');
            $searchConstraint[] = $query->like('street', '%' . $longStreetSearch . '%');
            $constraint[] = $query->logicalOr($searchConstraint);
        }
        
        if (!empty($category)) {
            $constraint[] = $query->logicalOr(array(
                $query->contains('mainTrade.uid', $category),
                $query->contains('trades.uid', $category),
            ));
        }

        if (!empty($constraint)) {
            return $query->matching($query->logicalAnd($constraint))->execute();
        } else {
            return $query->execute();
        }
    }

    /**
     * Collect all categories used as main_trade and group them
     *
     * @return array
     */
    public function getGroupedCategories()
    {
        $where = array();
        $where[] = ' sys_category_record_mm.tablenames=?';
        $where[] = ' AND sys_category_record_mm.fieldname=?';
        $where[] = BackendUtility::BEenableFields('sys_category');
        $where[] = BackendUtility::deleteClause('sys_category');
        $where[] = BackendUtility::BEenableFields('tx_yellowpages2_domain_model_company');
        $where[] = BackendUtility::deleteClause('tx_yellowpages2_domain_model_company');
    
        $sql = '
            SELECT sys_category.uid, sys_category.title
            
            FROM tx_yellowpages2_domain_model_company
            
            LEFT JOIN sys_category_record_mm
            ON tx_yellowpages2_domain_model_company.uid = sys_category_record_mm.uid_foreign
            
            LEFT JOIN sys_category
            ON sys_category_record_mm.uid_local = sys_category.uid
            
            WHERE ' . implode(LF, $where) . '
            
            GROUP BY sys_category.uid
            ORDER BY sys_category.title
        ';
    
        /** @var PreparedStatement $preparedStatement */
        $preparedStatement = $this->objectManager->get(
            'TYPO3\\CMS\\Core\\Database\\PreparedStatement',
            $sql,
            'tx_yellowpages2_domain_model_company'
        );

        /** @var Query $query */
        $query = $this->createQuery();
        $results = $query->statement(
            $preparedStatement,
            array(
                'tx_yellowpages2_domain_model_company',
                'main_trade'
            )
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

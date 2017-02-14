<?php
namespace JWeiland\Yellowpages2;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update class for the extension manager.
 */
class ext_update
{
    /**
     * Array of flash messages (params) array[][status,title,message]
     *
     * @var array
     */
    protected $messageArray = array();

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     */
    public function main()
    {
        $this->processUpdates();
        return $this->generateOutput();
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     */
    public function access()
    {
        // find all records which have a main_trade assigned,
        // but does not have a relation in sys_category_record_mm table
        $subQuery = sprintf(
            'SELECT mm.uid_foreign FROM sys_category_record_mm mm WHERE mm.tablenames=%s AND mm.fieldname=%s',
            $this->getDatabaseConnection()->fullQuoteStr(
                'tx_yellowpages2_domain_model_company',
                'sys_category_record_mm'
            ),
            $this->getDatabaseConnection()->fullQuoteStr(
                'main_trade',
                'sys_category_record_mm'
            )
        );
        $amountOfRecords = $this->getDatabaseConnection()->exec_SELECTcountRows(
            '*',
            'tx_yellowpages2_domain_model_company c',
            sprintf(
                'c.uid NOT IN (%s) AND c.main_trade > %d',
                $subQuery,
                0
            )
        );
        return (bool)$amountOfRecords;
    }

    /**
     * The actual update function. Add your update task in here.
     *
     * @return void
     */
    protected function processUpdates()
    {
        $this->createSysCategoryRelation();
    }

    /**
     * Remove SwitchableControllerActions from tt_content records as they are not needed anymore
     *
     * @return void
     */
    protected function createSysCategoryRelation()
    {
        $companies = $this->getCompaniesWithMainTrade();
        foreach ($companies as $company) {
            $this->getDatabaseConnection()->exec_INSERTquery(
                'sys_category_record_mm',
                array(
                    'uid_local' => $company['main_trade'],
                    'uid_foreign' => $company['uid'],
                    'tablenames' => 'tx_yellowpages2_domain_model_company',
                    'fieldname' => 'main_trade',
                    'sorting' => 0,
                    'sorting_foreign' => 1
                )
            );
            if ($this->getDatabaseConnection()->sql_insert_id()) {
                $this->getDatabaseConnection()->exec_UPDATEquery(
                    'tx_yellowpages2_domain_model_company',
                    'uid=' . (int)$company['uid'],
                    array(
                        'main_trade' => 1
                    )
                );
            }
        }
        $this->messageArray[] = array(
            FlashMessage::OK,
            'Update records successful',
            sprintf(
                'We have created %d sys_category_record_mm records and updated %d tx_yellowpages2_domain_model_company records',
                count($companies),
                count($companies)
            )
        );
    }
    
    /**
     * Get companies which have a main_trade assigned
     * But does not have a relation in sys_category_record_mm
     *
     * @return array
     */
    protected function getCompaniesWithMainTrade()
    {
        $subQuery = sprintf(
            'SELECT mm.uid_foreign FROM sys_category_record_mm mm WHERE mm.tablenames=%s AND mm.fieldname=%s',
            $this->getDatabaseConnection()->fullQuoteStr(
                'tx_yellowpages2_domain_model_company',
                'sys_category_record_mm'
            ),
            $this->getDatabaseConnection()->fullQuoteStr(
                'main_trade',
                'sys_category_record_mm'
            )
        );
        $companies = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'uid, main_trade',
            'tx_yellowpages2_domain_model_company c',
            sprintf(
                'c.uid NOT IN (%s) AND c.main_trade > %d',
                $subQuery,
                0
            )
        );
        
        if (empty($companies)) {
            $companies = array();
        }
        
        return $companies;
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function generateOutput()
    {
        $output = '';
        foreach ($this->messageArray as $messageItem) {
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                $messageItem[2],
                $messageItem[1],
                $messageItem[0]);
            if (GeneralUtility::compat_version('8.0')) {
                $output .= $flashMessage->getMessageAsMarkup();
            } else {
                $output .= $flashMessage->render();
            }
        }
        return $output;
    }

    /**
     * Get TYPO3s Database Connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}

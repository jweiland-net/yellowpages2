<?php
namespace JWeiland\Yellowpages2;

/*
 * This file is part of the maps2 project.
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
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
    protected $messageArray = [];

    /**
     * @var FlexFormTools
     */
    protected $flexFormTools;

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
        $grantAccess = $this->getDatabaseConnection()->exec_SELECTquery(
            'COUNT(*) AS rowsToUpdate',
            'tx_yellowpages2_domain_model_company',
            'tx_yellowpages2_domain_model_company.main_trade != 0'
        );

        return (bool)$grantAccess->fetch_assoc()['rowsToUpdate'];
    }

    /**
     * The actual update function. Add your update task in here.
     *
     * @return void
     */
    protected function processUpdates()
    {
        $this->migrateMainTradeToMM();
    }

    /**
     * Migrate records
     * TODO: Properly check if update is needed
     */
    protected function migrateMainTradeToMM()
    {
        $companyMainTradeMapping = $this->getDatabaseConnection()->exec_SELECTquery(
            'tx_yellowpages2_domain_model_company.main_trade AS uid_local, tx_yellowpages2_domain_model_company.uid AS uid_foreign',
            'tx_yellowpages2_domain_model_company',
            'tx_yellowpages2_domain_model_company.main_trade != 0'
        );

        $rows = [];
        foreach ($companyMainTradeMapping as $row) {
            if ((int)$row['uid_local'] !== 0) {
                $row['tablenames'] = 'tx_yellowpages2_domain_model_company';
                $row['fieldname'] = 'main_trade';
                $rows[] = $row;
            }
        }

        DebuggerUtility::var_dump($rows);

        $insertSuccessfull = $this->getDatabaseConnection()->exec_INSERTmultipleRows(
            'sys_category_record_mm',
            ['uid_local', 'uid_foreign', 'tablenames', 'fieldname'],
            $rows
        );

        if ($insertSuccessfull) {
            foreach ($rows as $row) {
                $this->getDatabaseConnection()->exec_UPDATEquery(
                    'tx_yellowpages2_domain_model_company',
                    'uid = ' . $row['uid_foreign'],
                    ['main_trade' => 1]
                );
            }

            $this->messageArray[] = [
                FlashMessage::OK,
                'Update records successful',
                'Update records successful'
            ];
        } else {
            $this->messageArray[] = [
                FlashMessage::ERROR,
                'Error while selecting tt_content records',
                'SQL-Error: ' . $this->getDatabaseConnection()->sql_error()
            ];
        }
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
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $messageItem[2],
                $messageItem[1],
                $messageItem[0]);

            if (version_compare(TYPO3_branch, '8.6') >= 0) {
                $flashMessages = [$flashMessage];
                $output .= GeneralUtility::makeInstance(FlashMessageRendererResolver::class)->resolve()->render($flashMessages);
            } elseif (version_compare(TYPO3_branch, '8.0') >= 0) {
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

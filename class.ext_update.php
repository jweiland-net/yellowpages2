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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;

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
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_yellowpages2_domain_model_company');
        $rowsToUpdate = $queryBuilder
            ->count('*')
            ->from('tx_yellowpages2_domain_model_company')
            ->where(
                $queryBuilder->expr()->neq(
                    'main_trade',
                    0
                )
            )
            ->execute()
            ->fetchColumn(0);

        return (bool)$rowsToUpdate;
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
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_yellowpages2_domain_model_company');
        $companyMainTradeMapping = $queryBuilder
            ->select('main_trade AS uid_local', 'uid AS uid_foreign')
            ->from('tx_yellowpages2_domain_model_company')
            ->where(
                $queryBuilder->expr()->neq(
                    'main_trade',
                    0
                )
            )
            ->execute()
            ->fetchAll();

        $rows = [];
        foreach ($companyMainTradeMapping as $row) {
            if ((int)$row['uid_local'] !== 0) {
                $row['tablenames'] = 'tx_yellowpages2_domain_model_company';
                $row['fieldname'] = 'main_trade';
                $rows[] = $row;
            }
        }

        $connection = $this->getConnectionPool()->getConnectionForTable('sys_category_record_mm');
        $insertSuccessfull = $connection->bulkInsert(
            'sys_category_record_mm',
            $rows,
            ['uid_local', 'uid_foreign', 'tablenames', 'fieldname']
        );

        if ($insertSuccessfull) {
            foreach ($rows as $row) {
                $connection = $this->getConnectionPool()->getConnectionForTable('tx_yellowpages2_domain_model_company');
                $connection->update(
                    'tx_yellowpages2_domain_model_company',
                    ['main_trade' => 1],
                    ['uid' => $row['uid_foreign']]
                );
            }

            $this->messageArray[] = [
                FlashMessage::OK,
                'Update records successful',
                'Update records successful'
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
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}

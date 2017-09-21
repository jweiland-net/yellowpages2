<?php
declare(strict_types=true);
namespace JWeiland\Yellowpages2\Tasks;

/*
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

use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Update extends AbstractTask
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    /**
     * @var MailMessage
     */
    protected $mail;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * constructor of this class
     */
    public function __construct()
    {
        // first we have to call the parent constructor
        parent::__construct();

        // initialize some global variables
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $this->mail = $this->objectManager->get(MailMessage::class);
        $this->extConf = $this->objectManager->get(ExtConf::class);
        $this->companyRepository = $this->objectManager->get(CompanyRepository::class);
        $this->companyRepository->setDefaultQuerySettings($this->getDefaultQuerySettings());
    }

    /**
     * generate default query settings to access all records
     *
     * @return QuerySettingsInterface
     */
    protected function getDefaultQuerySettings()
    {
        /** @var QuerySettingsInterface $settings */
        $settings = $this->objectManager->get(QuerySettingsInterface::class);
        $settings->setIgnoreEnableFields(true);
        $settings->setRespectSysLanguage(false);
        $settings->setRespectStoragePage(false);
        return $settings;
    }

    /**
     * The first method which will be executed when task starts
     *
     * @return boolean
     */
    public function execute()
    {
        // hide companies which are older than 13 months
        $companies = $this->companyRepository->findOlderThan(396);
        if ($companies instanceof QueryResult) {
            /** @var $company Company */
            foreach ($companies as $company) {
                $company->setHidden(true);
                $this->companyRepository->update($company);
                if ($company->getEmail()) {
                    $this->informUser($company, 'deactivated');
                }
                $this->informAdmin($company);
            }
            $this->persistenceManager->persistAll();
        }

        // inform users about entries older than 12 month
        $companies = $this->companyRepository->findOlderThan(365);
        if ($companies instanceof QueryResult) {
            /** @var $company Company */
            foreach ($companies as $company) {
                $this->informUser($company, 'inform');
            }
        }

        // Task must return TRUE to signal that it was executed successfully
        return true;
    }

    /**
     * mail to user
     *
     * @param Company $company
     * @param string $type "inform" or "deactivated"
     * @return void
     */
    public function informUser(Company $company, $type)
    {
        $this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $this->mail->setTo($company->getEmail(), $company->getCompany());
        $this->mail->setSubject(LocalizationUtility::translate('email.subject.' . $type . '.user', 'yellowpages2'));
        $this->mail->setBody(
            LocalizationUtility::translate(
                'email.body.' . $type . '.user',
                'yellowpages2',
                [
                    $company->getUid(),
                    $company->getCompany(),
                    $this->extConf->getEditLink()
                ]
            ),
            'text/html'
        );

        $this->mail->send();
    }

    /**
     * inform admin about old company entries
     *
     * @param Company $company
     * @return void
     */
    public function informAdmin(Company $company)
    {
        $this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
        $this->mail->setSubject(LocalizationUtility::translate('email.subject.deactivated.admin', 'yellowpages2'));
        $this->mail->setBody(
            LocalizationUtility::translate(
                'email.body.deactivated.admin',
                'yellowpages2',
                [
                    $company->getUid(),
                    $company->getCompany()
                ]
            ),
            'text/html'
        );

        $this->mail->send();
    }

    /**
     * scheduler serializes this object so we have to tell unserialize() what to do
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $this->mail = $this->objectManager->get(MailMessage::class);
        $this->extConf = $this->objectManager->get(ExtConf::class);
        $this->companyRepository = $this->objectManager->get(CompanyRepository::class);
        $this->companyRepository->setDefaultQuerySettings($this->getDefaultQuerySettings());
    }

    /**
     * the result of serialization is too big for db. So we reduce the return value
     *
     * @return array
     */
    public function __sleep()
    {
        return ['scheduler', 'taskUid', 'disabled', 'execution', 'executionTime'];
    }
}

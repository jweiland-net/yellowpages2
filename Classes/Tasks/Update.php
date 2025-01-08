<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tasks;

use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Hide companies which are older than 13 months.
 * Inform users about entries older than 12 month.
 */
class Update extends AbstractTask
{
    /**
     * The first method which will be executed when task starts
     */
    public function execute(): bool
    {
        $companyRepository = $this->getCompanyRepository();
        $persistenceManager = $this->getPersistenceManager();

        // Hide companies which are older than 13 months
        $companies = $companyRepository->findOlderThan(396);
        foreach ($companies as $company) {
            $company->setHidden(true);
            $companyRepository->update($company);
            if ($company->getEmail()) {
                $this->informUser($company, 'deactivated');
            }

            $this->informAdmin($company);
        }

        $persistenceManager->persistAll();

        // Inform users about entries older than 12 month
        $companies = $companyRepository->findOlderThan(365);
        foreach ($companies as $company) {
            $this->informUser($company, 'inform');
        }

        return true;
    }

    /**
     * Inform user by mail
     */
    public function informUser(Company $company, string $type): void
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $extConf = GeneralUtility::makeInstance(ExtConf::class);

        $mail->setFrom($extConf->getEmailFromAddress(), $extConf->getEmailFromName());
        $mail->setTo($company->getEmail(), $company->getCompany());
        $mail->setSubject(LocalizationUtility::translate('email.subject.' . $type . '.user', 'yellowpages2'));

        $bodyHtml = LocalizationUtility::translate(
            'email.body.' . $type . '.user',
            'yellowpages2',
            [
                $company->getUid(),
                $company->getCompany(),
                $extConf->getEditLink(),
            ],
        );

        $mail->html($bodyHtml);
        $mail->send();
    }

    /**
     * Inform admin about old company entries
     */
    public function informAdmin(Company $company): void
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $extConf = GeneralUtility::makeInstance(ExtConf::class);

        $mail->setFrom($extConf->getEmailFromAddress(), $extConf->getEmailFromName());
        $mail->setTo($extConf->getEmailToAddress(), $extConf->getEmailToName());
        $mail->setSubject(LocalizationUtility::translate('email.subject.deactivated.admin', 'yellowpages2'));

        $bodyHtml = LocalizationUtility::translate(
            'email.body.deactivated.admin',
            'yellowpages2',
            [
                $company->getUid(),
                $company->getCompany(),
            ],
        );

        $mail->html($bodyHtml);
        $mail->send();
    }

    /**
     * Generate default query settings to access all records
     */
    protected function getDefaultQuerySettings(): QuerySettingsInterface
    {
        $settings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
        $settings->setIgnoreEnableFields(true);
        $settings->setRespectSysLanguage(false);
        $settings->setRespectStoragePage(false);

        return $settings;
    }

    public function getPersistenceManager(): PersistenceManagerInterface
    {
        return GeneralUtility::makeInstance(PersistenceManagerInterface::class);
    }

    public function getExtConf(): ExtConf
    {
        return GeneralUtility::makeInstance(ExtConf::class);
    }

    public function getCompanyRepository(): CompanyRepository
    {
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        $companyRepository->setDefaultQuerySettings($this->getDefaultQuerySettings());

        return $companyRepository;
    }
}

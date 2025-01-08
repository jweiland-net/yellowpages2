<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use Doctrine\DBAL\Exception;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Mailer\NotificationMailer;

class CompanyUpdateService
{
    private const DAYS_TO_HIDE_COMPANY = 396; // 13 months
    private const DAYS_TO_INFORM_USER = 365; // 12 months

    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly NotificationMailer $notificationMailer
    ) {}

    /**
     * @throws Exception
     */
    public function updateCompanies(): void
    {
        // Hide companies older than 13 months
        $oldCompanies = $this->companyRepository->findOlderThan(self::DAYS_TO_HIDE_COMPANY);

        if ($oldCompanies !== []) {
            foreach ($oldCompanies as $company) {
                $this->companyRepository->hideCompany($company['uid']);
                if ($company['email']) {
                    $this->notificationMailer->informUser($company, 'deactivated');
                }
                $this->notificationMailer->informAdmin($company);
            }

            // Inform users about entries older than 12 months
            $companiesToInform = $this->companyRepository->findOlderThan(self::DAYS_TO_INFORM_USER);
            foreach ($companiesToInform as $company) {
                $this->notificationMailer->informUser($company, 'inform');
            }
        }
    }
}

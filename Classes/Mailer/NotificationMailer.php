<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Mailer;

use JWeiland\Yellowpages2\Configuration\ExtConf;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final readonly class NotificationMailer
{
    public function __construct(
        private ExtConf $extConf,
    ) {}

    public function informUser(array $company, string $type)
    {
        $mail = new MailMessage();
        $mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName())
            ->setTo($company['email'], $company['company'])
            ->setSubject($this->translateSubject($type, 'user'))
            ->html($this->translateBody($type, 'user', [
                $company['uid'],
                $company['company'],
                $this->extConf->getEditLink(),
            ]))
            ->send();
    }

    public function informAdmin(array $company): void
    {
        $mail = new MailMessage();
        $mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName())
            ->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName())
            ->setSubject($this->translateSubject('deactivated', 'admin'))
            ->html($this->translateBody('deactivated', 'admin', [
                $company['uid'],
                $company['company'],
            ]))
            ->send();
    }

    private function translateSubject(string $type, string $recipient): string
    {
        return LocalizationUtility::translate("email.subject.{$type}.{$recipient}", 'yellowpages2') ?? '';
    }

    private function translateBody(string $type, string $recipient, array $arguments): string
    {
        return LocalizationUtility::translate("email.body.{$type}.{$recipient}", 'yellowpages2', $arguments) ?? '';
    }
}

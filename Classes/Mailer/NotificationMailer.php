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
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Not final on purpose: Integrators may XCLASS this notifier to change the mails of the update command.
 */
readonly class NotificationMailer
{
    public function __construct(
        protected ExtConf $extConf,
        protected MailerInterface $mailer,
        protected LoggerInterface $logger,
    ) {}

    /**
     * @param array<string, mixed> $company
     */
    public function informUser(array $company, string $type): void
    {
        $mail = new MailMessage();
        $mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName())
            ->setTo($company['email'], $company['company'])
            ->setSubject($this->translateSubject($type, 'user'))
            ->html($this->translateBody($type, 'user', [
                $company['uid'],
                $company['company'],
                $this->extConf->getEditLink(),
            ]));

        $this->send($mail, (int)$company['uid'], $type . '.user');
    }

    /**
     * @param array<string, mixed> $company
     */
    public function informAdmin(array $company): void
    {
        $mail = new MailMessage();
        $mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName())
            ->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName())
            ->setSubject($this->translateSubject('deactivated', 'admin'))
            ->html(
                $this->translateBody('deactivated', 'admin', [
                    $company['uid'],
                    $company['company'],
                ]),
            );

        $this->send($mail, (int)$company['uid'], 'deactivated.admin');
    }

    /**
     * A failing mail must not stop the update of the remaining companies.
     */
    protected function send(MailMessage $mail, int $companyUid, string $mailType): void
    {
        try {
            $this->mailer->send($mail);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error(
                'The notification mail could not be sent.',
                ['companyUid' => $companyUid, 'mailType' => $mailType, 'exception' => $exception],
            );
        }
    }

    protected function translateSubject(string $type, string $recipient): string
    {
        $translationKey = sprintf('email.subject.%s.%s', $type, $recipient);
        return LocalizationUtility::translate($translationKey, ExtConf::EXT_KEY) ?? '';
    }

    /**
     * @param array<int, mixed> $arguments
     */
    protected function translateBody(string $type, string $recipient, array $arguments): string
    {
        return LocalizationUtility::translate("email.body.{$type}.{$recipient}", ExtConf::EXT_KEY, $arguments) ?? '';
    }
}

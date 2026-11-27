<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Helper;

use JWeiland\Yellowpages2\Configuration\ExtConf;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\ExceptionInterface as MimeExceptionInterface;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * Not final on purpose: Integrators may XCLASS this mail helper to change the moderation mails.
 */
readonly class MailHelper
{
    public function __construct(
        protected MailerInterface $mailer,
        protected ExtConf $extConf,
        protected LoggerInterface $logger,
    ) {}

    /**
     * Sends the mail to the moderator. Returns false (and logs an error), if the mail could not be sent.
     *
     * @param array<string, mixed> $logContext Context for the log entry in case of an error, e.g. the company UID
     */
    public function sendMail(string $mailContent, string $subject, array $logContext = []): bool
    {
        try {
            $mailMessage = new MailMessage();
            $mailMessage
                ->from(new Address(
                    $this->extConf->getEmailFromAddress(),
                    $this->extConf->getEmailFromName(),
                ))
                ->to(new Address(
                    $this->extConf->getEmailToAddress(),
                    $this->extConf->getEmailToName(),
                ))
                ->subject($subject)
                ->html($mailContent);

            $this->mailer->send($mailMessage);
        } catch (TransportExceptionInterface|MimeExceptionInterface $exception) {
            $this->logger->error(
                'The moderation mail could not be sent.',
                [...$logContext, 'exception' => $exception],
            );

            return false;
        }

        return true;
    }
}

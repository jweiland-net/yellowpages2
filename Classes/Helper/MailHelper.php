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
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailHelper
{
    public function __construct(
        protected readonly MailerInterface $mailer,
        protected readonly ExtConf $extConf,
    ) {}

    public function sendMail(string $mailContent, string $subject): void
    {
        $mailMessage = GeneralUtility::makeInstance(MailMessage::class);

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
    }
}

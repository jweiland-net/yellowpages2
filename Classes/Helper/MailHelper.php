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
use TYPO3\CMS\Core\Mail\MailMessage;

/*
 * Helper class to send a mail.
 */
class MailHelper
{
    /**
     * @var MailMessage
     */
    protected $mailMessage;

    /**
     * @var ExtConf
     */
    protected $extConf;

    public function __construct(
        MailMessage $mailMessage,
        ExtConf $extConf
    ) {
        $this->mailMessage = $mailMessage;
        $this->extConf = $extConf;
    }

    public function sendMail(
        string $mailContent,
        string $subject
    ): void {
        $this->mailMessage
            ->setFrom(
                $this->extConf->getEmailFromAddress(),
                $this->extConf->getEmailFromName()
            )
            ->setTo(
                $this->extConf->getEmailToAddress(),
                $this->extConf->getEmailToName()
            )
            ->setSubject($subject)
            ->html($mailContent);

        $this->mailMessage->send();
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Configuration;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class to get configuration from ExtensionManager of this extension
 */
class ExtConf implements SingletonInterface
{
    protected string $editLink = '';

    protected string $emailFromAddress = '';

    protected string $emailFromName = '';

    protected string $emailToAddress = '';

    protected string $emailToName = '';

    public function __construct()
    {
        try {
            $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('yellowpages2');
            if (is_array($extConf) && $extConf !== []) {
                foreach ($extConf as $key => $value) {
                    $methodName = 'set' . ucfirst($key);
                    if (method_exists($this, $methodName)) {
                        $this->$methodName((string)$value);
                    }
                }
            }
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException) {
        }
    }

    public function getEditLink(): string
    {
        return $this->editLink;
    }

    public function setEditLink(string $editLink): void
    {
        $this->editLink = $editLink;
    }

    public function getEmailFromAddress(): string
    {
        if ($this->emailFromAddress === '') {
            $senderMail = (string)($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? '');
            if ($senderMail === '') {
                throw new \InvalidArgumentException('You have forgotten to set a sender email address in extension configuration or in install tool');
            }

            return $senderMail;
        }

        return $this->emailFromAddress;
    }

    public function setEmailFromAddress(string $emailFromAddress): void
    {
        $this->emailFromAddress = $emailFromAddress;
    }

    public function getEmailFromName(): string
    {
        if ($this->emailFromName === '') {
            $senderName = (string)($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? '');
            if ($senderName === '') {
                throw new \InvalidArgumentException('You have forgotten to set a sender name in extension configuration or in install tool');
            }

            return $senderName;
        }

        return $this->emailFromName;
    }

    public function setEmailFromName(string $emailFromName): void
    {
        $this->emailFromName = $emailFromName;
    }

    public function getEmailToAddress(): string
    {
        return $this->emailToAddress;
    }

    public function setEmailToAddress(string $emailToAddress): void
    {
        $this->emailToAddress = $emailToAddress;
    }

    public function getEmailToName(): string
    {
        return $this->emailToName;
    }

    public function setEmailToName(string $emailToName): void
    {
        $this->emailToName = $emailToName;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Configuration;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Class to get configuration from ExtensionManager of this extension
 */
#[Autoconfigure(constructor: 'create')]
final readonly class ExtConf
{
    public const EXT_KEY = 'yellowpages2';

    private const DEFAULT_SETTINGS = [
        'editLink' => '',
        'emailFromAddress' => '',
        'emailFromName' => '',
        'emailToAddress' => '',
        'emailToName' => '',
    ];

    public function __construct(
        private string $editLink = self::DEFAULT_SETTINGS['editLink'],
        private string $emailFromAddress = self::DEFAULT_SETTINGS['emailFromAddress'],
        private string $emailFromName = self::DEFAULT_SETTINGS['emailFromName'],
        private string $emailToAddress = self::DEFAULT_SETTINGS['emailToAddress'],
        private string $emailToName = self::DEFAULT_SETTINGS['emailToName'],
    ) {}

    public static function create(ExtensionConfiguration $extensionConfiguration): self
    {
        $extensionSettings = self::DEFAULT_SETTINGS;
        // Overwrite default extension settings with values from EXT_CONF
        try {
            $extensionSettings = array_merge(
                $extensionSettings,
                $extensionConfiguration->get(self::EXT_KEY),
            );
        } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
        }

        return new self(
            editLink: $extensionSettings['editLink'],
            emailFromAddress: $extensionSettings['emailFromAddress'],
            emailFromName: $extensionSettings['emailFromName'],
            emailToAddress: $extensionSettings['emailToAddress'],
            emailToName: $extensionSettings['emailToName'],
        );
    }

    public function getEditLink(): string
    {
        return $this->editLink;
    }

    public function getEmailFromAddress(): string
    {
        if ($this->emailFromAddress === '') {
            $senderMail = (string)($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? '');
            if ($senderMail === '') {
                throw new \InvalidArgumentException(
                    'You have forgotten to set a sender email address in extension configuration or in install tool'
                );
            }

            return $senderMail;
        }

        return $this->emailFromAddress;
    }

    public function getEmailFromName(): string
    {
        if ($this->emailFromName === '') {
            $senderName = (string)($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? '');
            if ($senderName === '') {
                throw new \InvalidArgumentException(
                    'You have forgotten to set a sender name in extension configuration or in install tool'
                );
            }

            return $senderName;
        }

        return $this->emailFromName;
    }

    public function getEmailToAddress(): string
    {
        return $this->emailToAddress;
    }

    public function getEmailToName(): string
    {
        return $this->emailToName;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Configuration;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class to get configuration from ExtensionManager of this extension
 */
class ExtConf implements SingletonInterface
{
    /**
     * @var string
     */
    protected $editLink = '';

    /**
     * @var string
     */
    protected $emailFromAddress = '';

    /**
     * @var string
     */
    protected $emailFromName = '';

    /**
     * @var string
     */
    protected $emailToAddress = '';

    /**
     * @var string
     */
    protected $emailToName = '';

    public function __construct()
    {
        // get global configuration
        $extConf = [];
        if (class_exists(ExtensionConfiguration::class)) {
            $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('yellowpages2');
        } elseif (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['yellowpages2'])) {
            $extConf = unserialize(
                $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['yellowpages2'],
                ['allowed_classes' => false]
            );
        }
        if (is_array($extConf) && count($extConf)) {
            // call setter method foreach configuration entry
            foreach ($extConf as $key => $value) {
                $methodName = 'set' . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($value);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getEditLink(): string
    {
        return $this->editLink;
    }

    /**
     * @param string $editLink
     */
    public function setEditLink(string $editLink): void
    {
        $this->editLink = $editLink;
    }

    /**
     * @throws \Exception
     * @return string
     */
    public function getEmailFromAddress(): string
    {
        if (empty($this->emailFromAddress)) {
            $senderMail = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
            if (empty($senderMail)) {
                throw new \Exception('You have forgotten to set a sender email address in extension configuration or in install tool');
            }

            return $senderMail;
        }
        return $this->emailFromAddress;
    }

    /**
     * @param string $emailFromAddress
     */
    public function setEmailFromAddress(string $emailFromAddress): void
    {
        $this->emailFromAddress = $emailFromAddress;
    }

    /**
     * @throws \Exception
     * @return string
     */
    public function getEmailFromName(): string
    {
        if (empty($this->emailFromName)) {
            $senderName = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
            if (empty($senderName)) {
                throw new \Exception('You have forgotten to set a sender name in extension configuration or in install tool');
            }

            return $senderName;
        }
        return $this->emailFromName;
    }

    /**
     * @param string $emailFromName
     */
    public function setEmailFromName(string $emailFromName): void
    {
        $this->emailFromName = $emailFromName;
    }

    /**
     * @return string
     */
    public function getEmailToAddress(): string
    {
        return $this->emailToAddress;
    }

    /**
     * @param string $emailToAddress
     */
    public function setEmailToAddress(string $emailToAddress): void
    {
        $this->emailToAddress = $emailToAddress;
    }

    /**
     * @return string
     */
    public function getEmailToName(): string
    {
        return $this->emailToName;
    }

    /**
     * @param string $emailToName
     */
    public function setEmailToName(string $emailToName): void
    {
        $this->emailToName = $emailToName;
    }
}

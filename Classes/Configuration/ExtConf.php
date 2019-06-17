<?php
declare(strict_types=1);
namespace JWeiland\Yellowpages2\Configuration;

/*
 * This file is part of the yellowpages2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class to get configuration from ExtensionManager of this extension
 */
class ExtConf implements SingletonInterface
{
    /**
     * editLink
     *
     * @var string
     */
    protected $editLink = '';

    /**
     * email from address
     *
     * @var string
     */
    protected $emailFromAddress = '';

    /**
     * email from name
     *
     * @var string
     */
    protected $emailFromName = '';

    /**
     * email to address
     *
     * @var string
     */
    protected $emailToAddress = '';

    /**
     * email to name
     *
     * @var string
     */
    protected $emailToName = '';

    /**
     * constructor of this class
     * This method reads the global configuration and calls the setter methods
     */
    public function __construct()
    {
        // get global configuration
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['yellowpages2']);
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
     * getter for editLink
     *
     * @return string
     */
    public function getEditLink()
    {
        return $this->editLink;
    }

    /**
     * setter for editLink
     *
     * @param string $editLink
     * @return void
     */
    public function setEditLink(string $editLink)
    {
        $this->editLink = $editLink;
    }

    /**
     * getter for email from address
     *
     * @throws \Exception
     * @return string
     */
    public function getEmailFromAddress()
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
     * setter for email from address
     *
     * @param string $emailFromAddress
     * @return void
     */
    public function setEmailFromAddress(string $emailFromAddress)
    {
        $this->emailFromAddress = $emailFromAddress;
    }

    /**
     * getter for email from name
     *
     * @throws \Exception
     * @return string
     */
    public function getEmailFromName()
    {
        if (empty($this->emailFromName)) {
            $senderName = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
            if (empty($senderName)) {
                throw new \Exception('You have forgotten to set a sender name in extension configuration or in install tool');
            }

            return $senderName;
        } else {
            return $this->emailFromName;
        }
    }

    /**
     * setter for emailFromName
     *
     * @param string $emailFromName
     * @return void
     */
    public function setEmailFromName(string $emailFromName)
    {
        $this->emailFromName = $emailFromName;
    }

    /**
     * getter for email to address
     *
     * @return string
     */
    public function getEmailToAddress()
    {
        return $this->emailToAddress;
    }

    /**
     * setter for email to address
     *
     * @param string $emailToAddress
     * @return void
     */
    public function setEmailToAddress(string $emailToAddress)
    {
        $this->emailToAddress = $emailToAddress;
    }

    /**
     * getter for email to name
     *
     * @return string
     */
    public function getEmailToName()
    {
        return $this->emailToName;
    }

    /**
     * setter for emailToName
     *
     * @param string $emailToName
     * @return void
     */
    public function setEmailToName(string $emailToName)
    {
        $this->emailToName = $emailToName;
    }
}

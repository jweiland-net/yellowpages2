<?php
namespace JWeiland\Yellowpages2\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EmailController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \TYPO3\CMS\Core\Mail\MailMessage
     */
    protected $mail;

    /**
     * @var \JWeiland\Yellowpages2\Configuration\ExtConf
     */
    protected $extConf;

    /**
     * inject mail
     *
     * @param \TYPO3\CMS\Core\Mail\MailMessage $mail
     * @return void
     */
    public function injectMail(\TYPO3\CMS\Core\Mail\MailMessage $mail)
    {
        $this->mail = $mail;
    }

    /**
     * inject extConf
     *
     * @param \JWeiland\Yellowpages2\Configuration\ExtConf $extConf
     * @return void
     */
    public function injectExtConf(\JWeiland\Yellowpages2\Configuration\ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * action send
     *
     * @param string $templateFile Template to use for sending
     * @param array $assignVariables Array containing variables to replace in template
     * @param array $redirect An Array containing action, controller and maybe some more informations for redirekt after mail processing
     * @return void
     */
    public function sendAction($templateFile = null, array $assignVariables = array(), array $redirect = array())
    {
        if ($templateFile !== null) {
            $this->view->setTemplatePathAndFilename($this->getTemplatePath() . ucfirst($templateFile));
            $this->view->assignMultiple($assignVariables);

            $this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
            $this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
            $this->mail->setSubject(LocalizationUtility::translate(lcfirst($templateFile), 'yellowpages2'));
            $this->mail->setBody($this->view->render(), 'text/html');

            $this->mail->send();
        }

        $this->redirect($redirect['actionName'], $redirect['controllerName'], $redirect['extensionName'], $redirect['arguments'], $redirect['pageUid'], $redirect['delay'], $redirect['statusCode']);
    }

    /**
     * get template path for email templates
     *
     * @return string email template path
     */
    public function getTemplatePath()
    {
        $extKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
        $controllerName = $this->controllerContext->getRequest()->getControllerName();
        return ExtensionManagementUtility::extPath($extKey) . 'Resources/Private/Templates/' . $controllerName . '/';
    }
}

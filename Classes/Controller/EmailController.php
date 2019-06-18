<?php
declare(strict_types=1);
namespace JWeiland\Yellowpages2\Controller;

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

use JWeiland\Yellowpages2\Configuration\ExtConf;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to send an email to fe user accounts
 */
class EmailController extends ActionController
{
    /**
     * @var MailMessage
     */
    protected $mail;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * inject mail
     *
     * @param MailMessage $mail
     */
    public function injectMail(MailMessage $mail)
    {
        $this->mail = $mail;
    }

    /**
     * inject extConf
     *
     * @param ExtConf $extConf
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * action send
     *
     * @param string $templateFile Template to use for sending
     * @param array $assignVariables Array containing variables to replace in template
     * @param array $redirect An Array containing action, controller and maybe some more informations for redirekt after mail processing
     */
    public function sendAction($templateFile = null, array $assignVariables = [], array $redirect = [])
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

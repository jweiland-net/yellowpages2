<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Controller;

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

    public function injectMail(MailMessage $mail): void
    {
        $this->mail = $mail;
    }

    public function injectExtConf(ExtConf $extConf): void
    {
        $this->extConf = $extConf;
    }

    /**
     * @param string $templateFile Template to use for sending
     * @param array $assignVariables Array containing variables to replace in template
     * @param array $redirect An Array containing action, controller and maybe some more informations for redirekt after mail processing
     */
    public function sendAction(string $templateFile, array $assignVariables = [], array $redirect = []): void
    {
        if ($templateFile !== null) {
            $this->view->setTemplatePathAndFilename($this->getTemplatePathForMail() . ucfirst($templateFile));
            $this->view->assignMultiple($assignVariables);

            $this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
            $this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
            $this->mail->setSubject(LocalizationUtility::translate(lcfirst($templateFile), 'yellowpages2'));
            if (method_exists($this->mail, 'addPart')) {
                // TYPO3 < 10 (Swift_Message)
                $this->mail->setBody($this->view->render(), 'text/html');
            } else {
                $isSymfonyEmail = true;
                // TYPO3 >= 10 (Symfony Mail)
                $this->mail->html($this->view->render());
            }

            $this->mail->send();
        }

        $this->redirect($redirect['actionName'], $redirect['controllerName'], $redirect['extensionName'], $redirect['arguments'], $redirect['pageUid'], $redirect['delay'], $redirect['statusCode']);
    }

    public function getTemplatePathForMail(): string
    {
        $extKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
        $controllerName = $this->controllerContext->getRequest()->getControllerName();
        return ExtensionManagementUtility::extPath($extKey) . 'Resources/Private/Templates/' . $controllerName . '/';
    }
}

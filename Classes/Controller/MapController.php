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
class MapController extends AbstractController {

	/**
	 * page renderer
	 *
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 * @inject
	 */
	protected $pageRenderer;

	/**
	 * @var \JWeiland\Maps2\Configuration\ExtConf
	 * @inject
	 */
	protected $extConfOfMaps2;





	/**
	 * initialize show action
	 *
	 * @return void
	 */
	public function initializeAction() {
		if ($this->settings['includeJQueryLibrary']) {
			$this->pageRenderer->addJsLibrary('maps2JQuery', '//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js', 'text/javascript', FALSE, TRUE, '', TRUE);
		}
		$this->pageRenderer->addJsLibrary('maps2GoogleMapsApi', $this->extConfOfMaps2->getGoogleMapsLibrary(), 'text/javascript', FALSE, TRUE, '', TRUE);
	}

	/**
	 * action new
	 * Hint: no "validate" Annotation: company was saved in previously called action
	 *
	 * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
	 * @return void
	 */
	public function newAction(\JWeiland\Yellowpages2\Domain\Model\Company $company = NULL) {
		$this->view->assign('company', $company);
	}

	/**
	 * initialize create action
	 * allow modification of submodel
	 * Hint: submodel was created already in companyController, that's why we need modification here
	 *
	 * @return void
	 */
	public function initializeCreateAction() {
		$this->arguments->getArgument('company')->getPropertyMappingConfiguration()->allowModificationForSubProperty('txMaps2Uid');
		$this->arguments->getArgument('company')->getPropertyMappingConfiguration()
			->allowProperties('txMaps2Uid')
			->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude', '__identity');
	}

	/**
	 * action create
	 * "create" means adding a new poi to company, but company itself has to be updated
	 *
	 * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
	 * @return void
	 */
	public function createAction(\JWeiland\Yellowpages2\Domain\Model\Company $company) {
		$this->sendMail('create', $company);
		$company->setHidden(TRUE);
		$this->companyRepository->update($company);
		$this->redirect('listMyCompanies', 'Company');
	}

	/**
	 * initialize edit action
	 *
	 * @return void
	 */
	public function initializeEditAction() {
		$this->registerCompanyFromRequest('company');
	}

	/**
	 * action edit
	 *
	 * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
	 * @return void
	 */
	public function editAction(\JWeiland\Yellowpages2\Domain\Model\Company $company) {
		$this->view->assign('company', $company);
	}

	/**
	 * initialize update action
	 * allow editing of submodel
	 *
	 * @return void
	 */
	public function initializeUpdateAction() {
		$this->registerCompanyFromRequest('company');
		if ($this->arguments->hasArgument('company')) {
			$this->arguments->getArgument('company')->getPropertyMappingConfiguration()->allowCreationForSubProperty('txMaps2Uid');
			$this->arguments->getArgument('company')->getPropertyMappingConfiguration()->allowModificationForSubProperty('txMaps2Uid');
			$this->arguments->getArgument('company')->getPropertyMappingConfiguration()->allowProperties('txMaps2Uid');
			$this->arguments->getArgument('company')->getPropertyMappingConfiguration()->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude');
		}
	}

	/**
	 * action update
	 *
	 * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
	 * @return void
	 */
	public function updateAction(\JWeiland\Yellowpages2\Domain\Model\Company $company) {
		// if webko edits this hidden record, mail should not be send
		if (!$company->getHidden()) {
			$this->sendMail('update', $company);
		}
		$company->setHidden(TRUE);
		$this->companyRepository->update($company);
		$this->redirect('listMyCompanies', 'Company');
	}

	/**
	 * send email on new/update
	 *
	 * @param string $subjectKey
	 * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
	 * @return integer The amount of email receivers
	 */
	public function sendMail($subjectKey, \JWeiland\Yellowpages2\Domain\Model\Company $company) {
		$this->view->assign('company', $company);

		$this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
		$this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
		$this->mail->setSubject(LocalizationUtility::translate('email.subject.' . $subjectKey, 'yellowpages2'));
		$this->mail->setBody($this->view->render(), 'text/html');

		return $this->mail->send();
	}

	/**
	 * get template path for email templates
	 *
	 * @return string email template path
	 */
	public function getTemplatePath() {
		$extKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
		return ExtensionManagementUtility::extPath($extKey) . 'Resources/Private/Templates/Email/';
	}

}
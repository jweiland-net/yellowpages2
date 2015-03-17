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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\CMS\Core\Mail\MailMessage
	 * @inject
	 */
	protected $mail;

	/**
	 * @var \JWeiland\Yellowpages2\Configuration\ExtConf
	 * @inject
	 */
	protected $extConf;

	/**
	 * persistenceManager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * googleMaps
	 *
	 * @var \JWeiland\Maps2\Utility\GoogleMaps
	 * @inject
	 */
	protected $googleMaps;

	/**
	 * companyRepository
	 *
	 * @var \JWeiland\Yellowpages2\Domain\Repository\CompanyRepository
	 * @inject
	 */
	protected $companyRepository;

	/**
	 * districtRepository
	 *
	 * @var \JWeiland\Yellowpages2\Domain\Repository\DistrictRepository
	 * @inject
	 */
	protected $districtRepository;

	/**
	 * categoryRepository
	 *
	 * @var \JWeiland\Yellowpages2\Domain\Repository\CategoryRepository
	 * @inject
	 */
	protected $categoryRepository;

	/**
	 * feUserRepository
	 *
	 * @var \JWeiland\Yellowpages2\Domain\Repository\FeUserRepository
	 * @inject
	 */
	protected $feUserRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Session
	 * @inject
	 */
	protected $session;

	protected $letters = '0-9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';





	/**
	 * preprocessing of all actions
	 *
	 * @return void
	 */
	public function initializeAction() {
		// if this value was not set, then it will be filled with 0
		// but that is not good, because UriBuilder accepts 0 as pid, so it's better to set it to NULL
		if (empty($this->settings['pidOfDetailPage'])) {
			$this->settings['pidOfDetailPage'] = NULL;
		}
	}

	/**
	 * get an array with letters as keys for the glossar
	 *
	 * @param boolean $isWsp
	 * @return array Array with starting letters as keys
	 */
	protected function getGlossar($isWsp) {
		$glossar = array();
		$availableLetters = $this->companyRepository->getStartingLetters($isWsp);
		$possibleLetters = GeneralUtility::trimExplode(',', $this->letters);

		// add all letters which we have found in DB
		foreach ($availableLetters as $availableLetter) {
			if (MathUtility::canBeInterpretedAsInteger($availableLetter['letter'])) {
				$availableLetter['letter'] = '0-9';
			}
			// add only letters which are valid (do not add "§$%")
			if (array_search($availableLetter['letter'], $possibleLetters) !== FALSE) {
				$glossar[$availableLetter['letter']] = TRUE;
			}
		}

		// add all valid letters which are not set/found by previous foreach
		foreach ($possibleLetters as $possibleLetter) {
			if (!array_key_exists($possibleLetter, $glossar)) {
				$glossar[$possibleLetter] = FALSE;
			}
		}

		ksort($glossar, SORT_STRING);

		return $glossar;
	}

	/**
	 * This is a workaround to help controller actions to find (hidden) companies
	 *
	 * @param $argumentName
	 */
	protected function registerCompanyFromRequest($argumentName) {
		$argument = $this->request->getArgument($argumentName);
		if (is_array($argument)) {
			// get company from form ($_POST)
			$topic = $this->companyRepository->findHiddenEntryByUid($argument['__identity']);
		} else {
			// get company from UID
			$topic = $this->companyRepository->findHiddenEntryByUid($argument);
		}
		$this->session->registerObject($topic, $topic->getUid());
	}

	/**
	 * A template method for displaying custom error flash messages, or to
	 * display no flash message at all on errors. Override this to customize
	 * the flash message in your action controller.
	 *
	 * @return string The flash message or FALSE if no flash message should be set
	 * @api
	 */
	protected function getErrorFlashMessage() {
		return LocalizationUtility::translate('errorFlashMessage', 'yellowpages2', array(
			get_class($this),
			$this->actionMethodName
		));
	}

	/**
	 * remove empty arguments from request
	 *
	 * @return void
	 */
	protected function removeEmptyArgumentsFromRequest() {
		$company = $this->request->getArgument('company');
		$company['trades'] = GeneralUtility::removeArrayEntryByValue($company['trades'], '');
		if ($company['trades'] === array()) {
			unset($company['trades']);
		}
		$this->request->setArgument('company', $company);
	}

	/**
	 * files will be uploaded in typeConverter automatically
	 * But, if an error occurs we have to remove them
	 *
	 * @param string $argument
	 * @return void
	 */
	protected function deleteUploadedFilesOnValidationErrors($argument) {
		if ($this->getControllerContext()->getRequest()->hasArgument($argument)) {
			$company = $this->getControllerContext()->getRequest()->getArgument($argument);
			if ($company['images'] !== array()) {
				unset($company['images']);
			}
			if ($company['logo'] !== array()) {
				unset($company['logo']);
			}
			$this->getControllerContext()->getRequest()->setArgument($argument, $company);
		}
	}

}
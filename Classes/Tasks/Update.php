<?php
namespace JWeiland\Yellowpages2\Tasks;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Update extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 */
	protected $persistenceManager;

	/**
	 * @var \JWeiland\Yellowpages2\Domain\Repository\CompanyRepository
	 */
	protected $companyRepository;

	/**
	 * @var \TYPO3\CMS\Core\Mail\MailMessage
	 */
	protected $mail;

	/**
	 * @var \JWeiland\Yellowpages2\Configuration\ExtConf
	 */
	protected $extConf;





	/**
	 * constructor of this class
	 */
	public function __construct() {
		// first we have to call the parent constructor
		parent::__construct();

		// initialize some global variables
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$this->mail = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$this->extConf = $this->objectManager->get('JWeiland\\Yellowpages2\\Configuration\\ExtConf');
		$this->companyRepository = $this->objectManager->get('JWeiland\\Yellowpages2\\Domain\\Repository\\CompanyRepository');
		$this->companyRepository->setDefaultQuerySettings($this->getDefaultQuerySettings());
	}

	/**
	 * generate default query settings to access all records
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
	 */
	protected function getDefaultQuerySettings() {
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $settings */
		$settings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface');
		$settings->setIgnoreEnableFields(TRUE);
		$settings->setRespectSysLanguage(FALSE);
		$settings->setRespectStoragePage(FALSE);
		return $settings;
	}

	/**
	 * The first method which will be executed when task starts
	 *
	 * @return boolean
	 */
	public function execute() {
		// hide companies which are older than 13 months
		$companies = $this->companyRepository->findOlderThan(396);
		if ($companies instanceof \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult) {
			/** @var $company \JWeiland\Yellowpages2\Domain\Model\Company */
			foreach ($companies as $company) {
				$company->setHidden(TRUE);
				$this->companyRepository->update($company);
				if ($company->getEmail()) {
					$this->informUser($company, 'deactivated');
				}
				$this->informAdmin($company, 'deactivated');
			}
			$this->persistenceManager->persistAll();
		}

		// inform users about entries older than 12 month
		$companies = $this->companyRepository->findOlderThan(365);
		if ($companies instanceof \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult) {
			/** @var $company \JWeiland\Yellowpages2\Domain\Model\Company */
			foreach ($companies as $company) {
				$this->informUser($company, 'inform');
			}
		}

		// Task must return TRUE to signal that it was executed successfully
		return TRUE;
	}

	/**
	 * mail to user
	 *
	 * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
	 * @param string $type "inform" or "deactivated"
	 * @return void
	 */
	public function informUser(\JWeiland\Yellowpages2\Domain\Model\Company $company, $type) {
		$this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
		$this->mail->setTo($company->getEmail(), $company->getCompany());
		$this->mail->setSubject(LocalizationUtility::translate('email.subject.' . $type . '.user', 'yellowpages2'));
		$this->mail->setBody(
			LocalizationUtility::translate(
				'email.body.' . $type . '.user',
				'yellowpages2',
				array(
					$company->getUid(),
					$company->getCompany(),
					$this->extConf->getEditLink()
				)
			),
			'text/html'
		);

		$this->mail->send();
	}

	/**
	 * inform admin about old company entries
	 *
	 * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
	 * @return void
	 */
	public function informAdmin(\JWeiland\Yellowpages2\Domain\Model\Company $company) {
		$this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
		$this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
		$this->mail->setSubject(LocalizationUtility::translate('email.subject.deactivated.admin', 'yellowpages2'));
		$this->mail->setBody(
			LocalizationUtility::translate(
				'email.body.deactivated.admin',
				'yellowpages2',
				array(
					$company->getUid(),
					$company->getCompany()
				)
			),
			'text/html'
		);

		$this->mail->send();
	}

	/**
	 * scheduler serializes this object so we have to tell unserialize() what to do
	 *
	 * @return void
	 */
	public function __wakeup() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$this->mail = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$this->extConf = $this->objectManager->get('JWeiland\\Yellowpages2\\Configuration\\ExtConf');
		$this->companyRepository = $this->objectManager->get('JWeiland\\Yellowpages2\\Domain\\Repository\\CompanyRepository');
		$this->companyRepository->setDefaultQuerySettings($this->getDefaultQuerySettings());
	}

	/**
	 * the result of serialization is too big for db. So we reduce the return value
	 *
	 * @return array
	 */
	public function __sleep() {
		return array('scheduler', 'taskUid', 'disabled', 'execution', 'executionTime');
	}

}
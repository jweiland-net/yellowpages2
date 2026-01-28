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
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Helper\MailHelper;
use JWeiland\Yellowpages2\Traits\PostProcessControllerActionTrait;
use JWeiland\Yellowpages2\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Yellowpages2\Traits\PreProcessControllerActionTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to show and save PoiCollections on a map
 */
class MapController extends ActionController
{
    use PostProcessFluidVariablesTrait;
    use PostProcessControllerActionTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected readonly CompanyRepository $companyRepository,
        protected readonly PersistenceManagerInterface $persistenceManager,
        protected readonly MailHelper $mailHelper,
    ) {}

    public function newAction(Company $company): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
        ]);

        return $this->htmlResponse();
    }

    /**
     * "create" means adding a new poi to company, but company itself has to be updated
     */
    public function createAction(Company $company): ResponseInterface
    {
        $company->setHidden(true);
        $this->companyRepository->update($company);

        $this->postProcessControllerAction($company);

        $this->sendMail('create', $company);

        $this->addFlashMessage(LocalizationUtility::translate('companyCreated', ExtConf::EXT_KEY));

        return $this->redirect('listMyCompanies', 'Company');
    }

    public function initializeEditAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function editAction(Company $company): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
        ]);

        return $this->htmlResponse();
    }

    /**
     * Allow editing of SubModel
     */
    public function initializeUpdateAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function updateAction(Company $company): ResponseInterface
    {
        // If an admin edits this hidden record, mail should not be sent.
        if (!$company->getHidden()) {
            $this->sendMail('update', $company);
        }

        $company->setHidden(true);
        $this->companyRepository->update($company);
        $this->postProcessControllerAction($company);

        $this->addFlashMessage(LocalizationUtility::translate('companyUpdated', ExtConf::EXT_KEY));

        return $this->redirect('listMyCompanies', 'Company');
    }

    public function sendMail(string $subjectKey, Company $company): void
    {
        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
        ]);

        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.' . $subjectKey, ExtConf::EXT_KEY),
        );
    }
}

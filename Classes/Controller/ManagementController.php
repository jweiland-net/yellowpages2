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
use JWeiland\Yellowpages2\Domain\Model\District;
use JWeiland\Yellowpages2\Domain\Repository\CategoryRepository;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Domain\Repository\DistrictRepository;
use JWeiland\Yellowpages2\Domain\Repository\FeUserRepository;
use JWeiland\Yellowpages2\Helper\MailHelper;
use JWeiland\Yellowpages2\Service\LocationService;
use JWeiland\Yellowpages2\Traits\InitializeActionTrait;
use JWeiland\Yellowpages2\Traits\PostProcessControllerActionTrait;
use JWeiland\Yellowpages2\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Yellowpages2\Traits\PreProcessControllerActionTrait;
use JWeiland\Yellowpages2\Utility\CacheUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to let frontend users create, edit and manage their own business listings
 */
class ManagementController extends ActionController
{
    use InitializeActionTrait;
    use PostProcessFluidVariablesTrait;
    use PostProcessControllerActionTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected readonly Context $context,
        protected readonly CompanyRepository $companyRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly DistrictRepository $districtRepository,
        protected readonly FeUserRepository $feUserRepository,
        protected readonly LocationService $locationService,
        protected readonly MailHelper $mailHelper,
        protected readonly PersistenceManagerInterface $persistenceManager,
    ) {}

    public function initializeListMyCompaniesAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     */
    public function listMyCompaniesAction(): ResponseInterface
    {
        $user = $this->context->getAspect('frontend.user')->get('id');
        $companies = $this->companyRepository->findByFeUser((int)$user);
        $this->postProcessAndAssignFluidVariables([
            'companies' => $companies,
            'categories' => $this->categoryRepository->findRelated(),
        ]);

        CacheUtility::addPageCacheTagsByQuery($companies->getQuery(), $this->request);

        return $this->htmlResponse();
    }

    public function initializeNewAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function newAction(): ResponseInterface
    {
        $company = GeneralUtility::makeInstance(Company::class);

        $district = $this->districtRepository->findByUid($this->settings['uidOfDefaultDistrict']);
        if ($district instanceof District) {
            $company->setDistrict($district);
        }

        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
            'districts' => $this->districtRepository->getDistricts(),
            'categories' => $this->categoryRepository->findByParent($this->settings['startingUidForCategories']),
        ]);

        return $this->htmlResponse();
    }

    public function initializeCreateAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function createAction(Company $company): ResponseInterface
    {
        $frontendUserAuthenticationObject = $this->request->getAttribute('frontend.user');
        if ($frontendUserAuthenticationObject->user['uid'] > 0) {
            $feUser = $this->feUserRepository->findByUid($frontendUserAuthenticationObject->user['uid']);
            $company->setFeUser($feUser);
        }

        $this->postProcessControllerAction($company);

        if (ExtensionManagementUtility::isLoaded('maps2')) {
            $poiCreated = $this->locationService->createPoiForCompany($company, $this->getFlashMessageQueue());

            if ($poiCreated) {
                // Data entry is not finished yet - the user still has to confirm the POI position on the
                // map in MapController::newAction()/createAction(). Keep the record hidden until then, so it
                // does not become publicly visible on the frontend in the meantime.
                $company->setHidden(true);
                $this->companyRepository->add($company);
                // Persist immediately to generate UIDs needed for the redirect logic or relations
                $this->persistenceManager->persistAll();

                return $this->redirect('new', 'Map', 'yellowpages2', ['company' => $company]);
            }

            $response = new ForwardResponse('new');
            $response = $response->withControllerName('Company');
            $response = $response->withArguments(['company' => $company]);

            return $response->withArguments(['company' => $company]);
        }

        $this->companyRepository->add($company);
        $this->addFlashMessage(LocalizationUtility::translate('companyCreated', ExtConf::EXT_KEY));

        return $this->redirect('listMyCompanies');
    }

    /**
     * Will be called when the link in mail is clicked
     */
    public function initializeEditAction(): void
    {
        $this->preProcessControllerAction();
    }

    #[IgnoreValidation(['value' => 'company'])]
    public function editAction(Company $company): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
            'districts' => $this->districtRepository->getDistricts(),
            'categories' => $this->categoryRepository->findByParent((int)$this->settings['startingUidForCategories']),
        ]);

        return $this->htmlResponse();
    }

    public function initializeUpdateAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function updateAction(Company $company): ResponseInterface
    {
        $this->companyRepository->update($company);
        $this->postProcessControllerAction($company);

        if (ExtensionManagementUtility::isLoaded('maps2')) {
            return $this->redirect(
                'update',
                'Map',
                ExtConf::EXT_KEY,
                ['company' => $company],
            );
        }

        $this->addFlashMessage(LocalizationUtility::translate('companyUpdated', ExtConf::EXT_KEY));

        return $this->redirect('listMyCompanies');
    }

    public function initializeActivateAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function initializePerformAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function performAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * @throws \Exception
     */
    public function activateAction(int $company): ResponseInterface
    {
        $companyObject = $this->companyRepository->findByIdentifier($company);
        $companyObject->setHidden(false);

        $this->companyRepository->update($companyObject);

        $this->postProcessControllerAction($companyObject);

        $this->postProcessAndAssignFluidVariables([
            'company' => $companyObject,
        ]);

        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.activate', ExtConf::EXT_KEY),
        );

        return $this->redirect('list', 'Company');
    }
}

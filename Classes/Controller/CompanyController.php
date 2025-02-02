<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Controller;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Model\District;
use JWeiland\Yellowpages2\Domain\Repository\CategoryRepository;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Domain\Repository\DistrictRepository;
use JWeiland\Yellowpages2\Domain\Repository\FeUserRepository;
use JWeiland\Yellowpages2\Helper\MailHelper;
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
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to list, show and search for companies
 */
class CompanyController extends ActionController
{
    use PostProcessFluidVariablesTrait;
    use PostProcessControllerActionTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        private readonly Context $context,
        private readonly CompanyRepository $companyRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly DistrictRepository $districtRepository,
        private readonly FeUserRepository $feUserRepository,
        private readonly MailHelper $mailHelper,
    ) {}

    public function initializeAction(): void
    {
        // if this value was not set, then it will be filled with 0
        // but that is not good, because UriBuilder accepts 0 as pid, so it's better to set it to NULL
        if (empty($this->settings['pidOfDetailPage'])) {
            $this->settings['pidOfDetailPage'] = null;
        }

        if (empty($this->settings['pidOfListPage'])) {
            $this->settings['pidOfListPage'] = null;
        }
    }

    #[Validate(['validator' => 'String', 'param' => 'letter'])]
    #[Validate(['validator' => 'StringLength', 'param' => 'letter', 'options' => ['minimum' => 0, 'maximum' => 3]])]
    public function listAction(string $letter = ''): ResponseInterface
    {
        $companies = $this->companyRepository->findByLetter($letter, $this->settings);
        $this->postProcessAndAssignFluidVariables([
            'companies' => $companies,
            'categories' => $this->categoryRepository->findRelated(),
        ]);

        CacheUtility::addPageCacheTagsByQuery($companies->getQuery(), $this->request);

        return $this->htmlResponse();
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

    public function showAction(int $company): ResponseInterface
    {
        $companyObject = $this->companyRepository->findByIdentifier($company);
        $this->postProcessAndAssignFluidVariables([
            'company' => $companyObject,
        ]);

        CacheUtility::addCacheTagsByCompanyRecords([$companyObject], $this->request);

        return $this->htmlResponse();
    }

    public function initializeSearchAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function searchAction(string $search, int $category = 0): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'search' => $search,
            'category' => $category,
            'companies' => $this->companyRepository->searchCompanies($search, $category, $this->settings),
            'categories' => $this->categoryRepository->findRelated(),
        ]);

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

        $this->companyRepository->add($company);

        $this->postProcessControllerAction($company);

        if (ExtensionManagementUtility::isLoaded('maps2')) {
            $this->redirect(
                'new',
                'Map',
                'yellowpages2',
                ['company' => $company],
            );
        }

        $this->addFlashMessage(LocalizationUtility::translate('companyCreated', 'yellowpages2'));

        return $this->redirect('listMyCompanies');
    }

    /**
     * Will be called when link in mail will be clicked
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
            $this->redirect(
                'update',
                'Map',
                'yellowpages2',
                ['company' => $company],
            );
        }

        $this->addFlashMessage(LocalizationUtility::translate('companyUpdated', 'yellowpages2'));

        return $this->redirect('listMyCompanies', 'Company');
    }

    public function initializeActivateAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * @throws \Exception
     */
    public function activateAction(int $company): void
    {
        /** @var Company $companyObject */
        $companyObject = $this->companyRepository->findByIdentifier($company);
        $companyObject->setHidden(false);

        $this->companyRepository->update($companyObject);

        $this->postProcessControllerAction($companyObject);

        $this->postProcessAndAssignFluidVariables([
            'company' => $companyObject,
        ]);

        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.activate', 'yellowpages2'),
        );

        $this->redirect('list', 'Company');
    }
}

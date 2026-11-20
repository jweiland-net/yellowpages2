<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Traits;

use JWeiland\Yellowpages2\Service\ModerationLinkTokenService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Builds requests of the company form of the Management plugin
 */
trait CompanyFormRequestTrait
{
    private function loginFrontendUser(int $frontendUserUid, int $userGroup = 1): void
    {
        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->user = ['uid' => $frontendUserUid, 'username' => 'user' . $frontendUserUid];
        $frontendUser->userGroups = [$userGroup => ['uid' => $userGroup, 'title' => 'Companies']];

        GeneralUtility::makeInstance(Context::class)->setAspect('frontend.user', new UserAspect($frontendUser));
    }

    /**
     * @param array<string, mixed> $pluginQueryParameters Query parameters of tx_yellowpages2_management
     */
    private function buildFormRequest(array $pluginQueryParameters = [], string $userGroup = '1'): Request
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_yellowpages2.' => [
                    'persistence.' => ['storagePid' => '10'],
                    'settings.' => [
                        'userGroup' => $userGroup,
                        'startingUidForCategories' => '1',
                        'uidOfDefaultDistrict' => '2',
                    ],
                ],
            ],
        ]);

        $request = (new ServerRequest('https://example.com/management'))
            ->withQueryParams(['tx_yellowpages2_management' => $pluginQueryParameters])
            ->withAttribute('frontend.typoscript', $frontendTypoScript)
            ->withAttribute('routing', new PageArguments(2, '0', []))
            ->withAttribute('extbase', new ExtbaseRequestParameters())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);

        return new Request($request);
    }

    private function buildModerationRequest(int $companyUid): ServerRequestInterface
    {
        return $this->buildFormRequest([
            'company' => (string)$companyUid,
            'moderationToken' => $this->get(ModerationLinkTokenService::class)->generateToken($companyUid),
        ]);
    }
}

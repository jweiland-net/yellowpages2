<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Contains the rules of the company form based on EXT:form. It is used by
 * the "perform" action of the ManagementController, by the
 * CompanyFormFactory and by the SaveCompanyFinisher, so all of them share
 * the same rules:
 *
 * - Create: Logged-in frontend users of the configured user group
 *   (TypoScript "settings.userGroup").
 * - Edit: The owner (frontend user) of the company, a backend administrator
 *   or everyone with a valid "moderationToken" of the signed mail link.
 *
 * The company to edit and the token are always taken from the query
 * parameters "tx_yellowpages2_management[company]" and
 * "tx_yellowpages2_management[moderationToken]". So a manipulated POST body
 * can not change the company, which was checked.
 */
readonly class CompanyAccessService
{
    public const PLUGIN_NAMESPACE = 'tx_yellowpages2_management';

    private const TABLE = 'tx_yellowpages2_domain_model_company';

    private const REASON_UNAUTHORIZED = 'unauthorizedCompanyUser';

    public function __construct(
        private Context $context,
        private ConnectionPool $connectionPool,
        private ModerationLinkTokenService $moderationLinkTokenService,
        private PluginSettingsService $pluginSettingsService,
    ) {}

    /**
     * Returns the UID of the company to edit. 0 means: create a new company.
     */
    public function getCompanyUid(ServerRequestInterface $request): int
    {
        $companyUid = $this->getPluginQueryParameters($request)['company'] ?? 0;

        return MathUtility::canBeInterpretedAsInteger($companyUid) ? max(0, (int)$companyUid) : 0;
    }

    public function getModerationToken(ServerRequestInterface $request): string
    {
        $moderationToken = $this->getPluginQueryParameters($request)['moderationToken'] ?? '';

        return is_string($moderationToken) ? $moderationToken : '';
    }

    /**
     * Returns the label key of the reason, why the current user is not
     * allowed to create or edit the company of the request. An empty string
     * means: allowed. Not existing and foreign companies result in the same
     * reason.
     */
    public function getDenialReason(ServerRequestInterface $request): string
    {
        $companyUid = $this->getCompanyUid($request);
        if ($companyUid > 0 && $this->isModerator($request, $companyUid)) {
            return $this->companyExists($companyUid) ? '' : self::REASON_UNAUTHORIZED;
        }

        $denialReason = $this->getDenialReasonForFrontendUser($request);
        if ($denialReason !== '' || $companyUid === 0) {
            return $denialReason;
        }

        return $this->companyExists($companyUid, $this->getFrontendUserUid()) ? '' : self::REASON_UNAUTHORIZED;
    }

    public function getFrontendUserUid(): int
    {
        return (int)$this->context->getPropertyFromAspect('frontend.user', 'id', 0);
    }

    private function isModerator(ServerRequestInterface $request, int $companyUid): bool
    {
        $moderationToken = $this->getModerationToken($request);

        return $this->context->getPropertyFromAspect('backend.user', 'isAdmin', false) === true
            || (
                $moderationToken !== ''
                && $this->moderationLinkTokenService->isValidToken($companyUid, $moderationToken)
            );
    }

    private function getDenialReasonForFrontendUser(ServerRequestInterface $request): string
    {
        $userGroup = (int)($this->pluginSettingsService->getSettings($request)['userGroup'] ?? 0);
        $groupIds = $this->context->getPropertyFromAspect('frontend.user', 'groupIds', []);

        return match (true) {
            $userGroup <= 0 => 'userGroupNotConfigured',
            $this->getFrontendUserUid() <= 0 => 'pluginNeedsLogin',
            !in_array($userGroup, is_array($groupIds) ? $groupIds : [], true) => 'notAllowedToCreate',
            default => '',
        };
    }

    /**
     * Checks, if the company exists. With a frontend user it has to be owned
     * by this user, too. Hidden companies are included, deleted ones not.
     */
    private function companyExists(int $companyUid, ?int $frontendUserUid = null): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());
        $queryBuilder
            ->count('uid')
            ->from(self::TABLE)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($companyUid, Connection::PARAM_INT)));

        if ($frontendUserUid !== null) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('fe_user', $queryBuilder->createNamedParameter($frontendUserUid, Connection::PARAM_INT)),
            );
        }

        return (int)$queryBuilder->executeQuery()->fetchOne() > 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function getPluginQueryParameters(ServerRequestInterface $request): array
    {
        $parameters = $request->getQueryParams()[self::PLUGIN_NAMESPACE] ?? [];

        return is_array($parameters) ? $parameters : [];
    }
}

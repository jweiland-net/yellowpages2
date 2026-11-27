<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Pagination;

use JWeiland\Yellowpages2\Utility\RequestUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;

class CompanyPagination implements PaginationInterface
{
    protected const PLUGIN_NAMESPACE = 'tx_yellowpages2_directory';

    /**
     * @var array<string, mixed>
     */
    protected array $arguments = [];

    /**
     * The plugin namespace is required to keep the request arguments (like
     * the search word of the Search plugin) in the page browser links.
     * The request is optional as PaginationInterface defines the paginator as the only
     * constructor argument. Without request no arguments are kept.
     */
    public function __construct(
        protected PaginatorInterface $paginator,
        string $pluginNamespace = self::PLUGIN_NAMESPACE,
        ?ServerRequestInterface $request = null,
    ) {
        $requestArguments = $request instanceof ServerRequestInterface
            ? RequestUtility::getMergedRequestArguments($request, $pluginNamespace)
            : [];
        foreach ($requestArguments as $argumentName => $argument) {
            $argumentName = (string)$argumentName;
            if (str_starts_with($argumentName, '__')) {
                continue;
            }

            if (in_array($argumentName, ['@extension', '@subpackage', '@controller', '@action', '@format'], true)) {
                continue;
            }

            if (in_array($argumentName, ['extension', 'plugin', 'controller', 'action'], true)) {
                continue;
            }

            $this->arguments[$argumentName] = $argument;
        }
    }

    public function getPreviousPageNumber(): ?int
    {
        $previousPage = $this->paginator->getCurrentPageNumber() - 1;

        if ($previousPage > $this->paginator->getNumberOfPages()) {
            return null;
        }

        return $previousPage >= $this->getFirstPageNumber() ? $previousPage : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPreviousPageArguments(): ?array
    {
        $arguments = $this->arguments;
        $arguments['currentPage'] = $this->getPreviousPageNumber();

        return $arguments;
    }

    public function getNextPageNumber(): ?int
    {
        $nextPage = $this->paginator->getCurrentPageNumber() + 1;

        return $nextPage <= $this->paginator->getNumberOfPages() ? $nextPage : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getNextPageArguments(): ?array
    {
        $arguments = $this->arguments;
        $arguments['currentPage'] = $this->getNextPageNumber();

        return $arguments;
    }

    public function getFirstPageNumber(): int
    {
        return 1;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFirstPageArguments(): ?array
    {
        $arguments = $this->arguments;
        $arguments['currentPage'] = $this->getFirstPageNumber();

        return $arguments;
    }

    public function getLastPageNumber(): int
    {
        return $this->paginator->getNumberOfPages();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLastPageArguments(): ?array
    {
        $arguments = $this->arguments;
        $arguments['currentPage'] = $this->getLastPageNumber();

        return $arguments;
    }

    public function getStartRecordNumber(): int
    {
        if ($this->paginator->getCurrentPageNumber() > $this->paginator->getNumberOfPages()) {
            return 0;
        }

        return $this->paginator->getKeyOfFirstPaginatedItem() + 1;
    }

    public function getEndRecordNumber(): int
    {
        if ($this->paginator->getCurrentPageNumber() > $this->paginator->getNumberOfPages()) {
            return 0;
        }

        return $this->paginator->getKeyOfLastPaginatedItem() + 1;
    }

    public function getAllPageNumbers(): array
    {
        return range($this->getFirstPageNumber(), $this->getLastPageNumber());
    }
}

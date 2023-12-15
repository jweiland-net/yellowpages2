<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Middleware;

use JWeiland\Yellowpages2\Modifier\HtmlspecialcharsModifier;
use JWeiland\Yellowpages2\Modifier\RemoveEmptyTradesModifier;
use JWeiland\Yellowpages2\Modifier\RequestFieldModifierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This middleware handles the request variable for createAction [Company]
 * 1. Remove empty trades from request to prevent errors while storing/updating the FE record
 * 2. Files will be processed by TypeConverter
 * But, if an error occurs, we have to remove them.
 * 3. Sanitize search keyword with htmlspecialchars
 */
class ControllerActionsMiddleware implements MiddlewareInterface
{
    protected RequestFieldModifierInterface $modifier;

    public function __construct(RequestFieldModifierInterface $modifier)
    {
        $this->modifier = $modifier;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestBody = $request->getParsedBody();

        // Continue processing the request if it doesn't have plugin variables
        if (!isset($requestBody['tx_yellowpages2_directory'])) {
            return $handler->handle($request);
        }

        if (isset($requestBody['tx_yellowpages2_directory']['company'])) {
            $request = $this->removeEmptyTradesFromRequestBody($request);
        } elseif (isset($requestBody['tx_yellowpages2_directory']['search'])) {
            $request = $this->sanitizeSearchKeyword($request);
        }

        // Handle altered request object
        return $handler->handle($request);
    }

    protected function sanitizeSearchKeyword(ServerRequestInterface $request): ServerRequestInterface
    {
        $this->modifier = GeneralUtility::makeInstance(HtmlspecialcharsModifier::class);
        return $this->sanitizeRequestField($request, 'search');
    }

    protected function removeEmptyTradesFromRequestBody(ServerRequestInterface $request): ServerRequestInterface
    {
        $this->modifier = GeneralUtility::makeInstance(RemoveEmptyTradesModifier::class);
        return $this->sanitizeRequestField($request, 'company');
    }

    protected function sanitizeRequestField(ServerRequestInterface $request, string $field, callable $modifier = null): ServerRequestInterface
    {
        try {
            $requestBody = $request->getParsedBody();

            // Retrieve and modify the data
            $data = ArrayUtility::getValueByPath($requestBody, "tx_yellowpages2_directory/$field");

            // Apply custom modifier
            $data = $this->modifier->modify($data);

            // Update the request body
            $requestBody = ArrayUtility::setValueByPath($requestBody, "tx_yellowpages2_directory/$field", $data);

            // Apply the modified request body to the request
            return $request->withParsedBody($requestBody);

        } catch (MissingArrayPathException | \RuntimeException $exception) {
            // Do nothing inside the catch block
            return $request;
        }
    }
}

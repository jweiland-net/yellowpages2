<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * This middleware handles the request variable for createAction [Company]
 * 1.Remove empty trades from request to prevent errors while storing/updating the FE record
 * 2. Files will be uploaded in typeConverter automatically.
 * But, if an error occurs, we have to remove them.
 * 3. Sanitize search keyword with htmlspecialchars
 */
class CompanyCreateActionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestBody = (array)$request->getParsedBody();

        // Check if the necessary path exists in the request body
        if (ArrayUtility::isValidPath($requestBody, 'tx_yellowpages2_directory/company')) {
            // Retrieve and modify the company data
            $company = ArrayUtility::getValueByPath(
                $requestBody,
                'tx_yellowpages2_directory/company'
            );
            $company['trades'] = array_filter($company['trades']);

            // Update the request body
            $requestBody = ArrayUtility::setValueByPath(
                $requestBody,
                'tx_yellowpages2_directory/company',
                $company
            );

            // Apply the modified request body to the request
            $request = $request->withParsedBody($requestBody);
        }

        // Continue processing the  request
        return $handler->handle($request);
    }
}

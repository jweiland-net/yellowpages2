<?php

declare(strict_types=1);

namespace JWeiland\Yellowpages2\Utility;

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

/*
 * Request Utility class
 */

use TYPO3\CMS\Core\Utility\ArrayUtility;
use Psr\Http\Message\RequestInterface;

class RequestUtility
{
    /**
     * Get request arguments merged from post
     */
    public static function getMergedRequestArguments(string $parameter): array
    {
        $query = self::getRequestFromGlobalVariable()->getQueryParams();
        $body = self::getRequestFromGlobalVariable()->getParsedBody();

        $postParameter = $body[$parameter] ?? [];
        $getParameter = $query[$parameter] ?? [];

        // Create a copy of $getParameter to avoid modifying it in-place
        $mergedParameters = $getParameter;

        // Merge the parameters into $mergedParameters
        ArrayUtility::mergeRecursiveWithOverrule($mergedParameters, $postParameter);

        return $mergedParameters;
    }


    /**
     * TYPO3 provides the request object also in the global variable $GLOBALS['TYPO3_REQUEST'].
     * Whenever it is possible the request should be retrieved within the contexts described above.
     * But this is not always possible by now.
     *
     * @return RequestInterface|null
     */
    public static function getRequestFromGlobalVariable()
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Utility;

/*
 * Request Utility class
 */

use TYPO3\CMS\Core\Utility\ArrayUtility;

class RequestUtility
{
    /**
     * Get request arguments merged from post
     */
    public static function getMergedRequestArguments(string $parameter): array
    {
        $query = self::getRequestFromGlobalVariable()->getQueryParams();
        $body = self::getRequestFromGlobalVariable()->getParsedBody();

        $postParameter = isset($body[$parameter]) && is_array($body[$parameter])
            ? $body[$parameter]
            : [];
        $getParameter = isset($query[$parameter]) && is_array($query[$parameter])
            ? $query[$parameter]
            : [];
        $mergedParameters = $getParameter;
        ArrayUtility::mergeRecursiveWithOverrule($getParameter, $postParameter);

        return $mergedParameters;
    }

    /**
     * TYPO3 provides the request object also in the global variable $GLOBALS['TYPO3_REQUEST'].
     * Whenever it is possible the request should be retrieved within the contexts described above.
     * But this is not always possible by now.
     *
     * @return mixed
     */
    public static function getRequestFromGlobalVariable()
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}

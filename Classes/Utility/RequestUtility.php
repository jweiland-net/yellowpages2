<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

final readonly class RequestUtility
{
    /**
     * Get request arguments merged from post
     *
     * @return array<string, mixed>
     */
    public static function getMergedRequestArguments(ServerRequestInterface $request, string $parameter): array
    {
        $body = $request->getParsedBody();

        $postParameter = is_array($body) && is_array($body[$parameter] ?? null) ? $body[$parameter] : [];
        $getParameter = $request->getQueryParams()[$parameter] ?? [];

        // Create a copy of $getParameter to avoid modifying it in-place
        $mergedParameters = is_array($getParameter) ? $getParameter : [];

        // Merge the parameters into $mergedParameters
        ArrayUtility::mergeRecursiveWithOverrule($mergedParameters, $postParameter);

        return $mergedParameters;
    }
}

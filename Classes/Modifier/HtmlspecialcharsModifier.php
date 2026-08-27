<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Modifier;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;

class HtmlspecialcharsModifier extends AbstractRequestFieldModifier
{
    public function modify(array $requestBody): array
    {
        try {
            $path = 'tx_yellowpages2_directory/search';

            return ArrayUtility::setValueByPath(
                $requestBody,
                $path,
                htmlspecialchars(ArrayUtility::getValueByPath($requestBody, $path)),
            );
        } catch (MissingArrayPathException|\RuntimeException $exception) {
            return $requestBody;
        }
    }
}

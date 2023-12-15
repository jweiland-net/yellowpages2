<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Modifier;

class RemoveEmptyTradesModifier implements RequestFieldModifierInterface
{
    /**
     * @param mixed $data
     */
    public function modify($data): array
    {
        debug(func_get_args());
        $data['trades'] = array_filter($data['trades']);
        return $data;
    }
}

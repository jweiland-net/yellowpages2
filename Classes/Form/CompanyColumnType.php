<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

/**
 * How the value of a form element is stored in a column of the company table
 */
enum CompanyColumnType
{
    /**
     * Column of TCA type "file". The values are UIDs of sys_file.
     */
    case File;

    /**
     * Column with MM relations to sys_category. The values are category UIDs.
     */
    case Category;

    /**
     * Columns of TCA type "check", "number" and "select" without MM relation
     */
    case Integer;

    case Text;
}

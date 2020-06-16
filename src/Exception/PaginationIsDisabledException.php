<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformQueryFieldType\Exception;

use Exception;

class PaginationIsDisabledException extends Exception
{
    public function __construct($contentTypeIdentifier, $fieldDefinitionIdentifier)
    {
        parent::__construct("Pagination is disabled for the query field $contentTypeIdentifier.$fieldDefinitionIdentifier");
    }
}

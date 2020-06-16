<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformQueryFieldType\Exception;

use Exception;

class QueryFieldNotFoundException extends Exception
{
    public function __construct(string $contentTypeIdentifier, string $fieldDefinitionIdentifier)
    {
        parent::__construct("Content type $contentTypeIdentifier doesn't have a query field with identifier $fieldDefinitionIdentifier");
    }
}

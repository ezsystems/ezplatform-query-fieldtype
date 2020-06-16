<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformQueryFieldType\API;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformQueryFieldType\Exception\QueryFieldNotFoundException;

/**
 * Returns the settings for a Query Field.
 * @internal
 */
interface QueryFieldSettingsProvider
{
    /**
     * Returns the query type parameters for a field definition and location.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $fieldDefinitionIdentifier
     *
     * @return QueryFieldSettings
     *
     * @throws QueryFieldNotFoundException if the content type doesn't have a query field with that identifier.
     */
    public function getSettings(ContentType $contentType, string $fieldDefinitionIdentifier): QueryFieldSettings;
}

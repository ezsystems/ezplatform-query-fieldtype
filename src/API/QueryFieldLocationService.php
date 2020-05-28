<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\API;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Executes queries for a query field for a given a location.
 */
interface QueryFieldLocationService
{
    /**
     * Returns the query results for the given location.
     */
    public function loadContentItemsForLocation(Location $location, string $fieldDefinitionIdentifier): iterable;

    /**
     * Returns a slice of the query results for the given location.
     */
    public function loadContentItemsSliceForLocation(Location $location, string $fieldDefinitionIdentifier, int $offset, int $limit): iterable;

    /**
     * Counts the results for the given location.
     */
    public function countContentItemsForLocation(Location $location, string $fieldDefinitionIdentifier): int;
}

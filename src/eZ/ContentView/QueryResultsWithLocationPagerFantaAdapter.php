<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\eZ\ContentView;

use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldLocationService;
use Pagerfanta\Adapter\AdapterInterface;

final class QueryResultsWithLocationPagerFantaAdapter implements AdapterInterface
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldLocationService */
    private $queryFieldService;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var string */
    private $fieldDefinitionIdentifier;

    public function __construct(
        QueryFieldLocationService $queryFieldService,
        Location $location,
        string $fieldDefinitionIdentifier)
    {
        $this->queryFieldService = $queryFieldService;
        $this->location = $location;
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;
    }

    public function getNbResults()
    {
        return $this->queryFieldService->countContentItemsForLocation(
            $this->location,
            $this->fieldDefinitionIdentifier
        );
    }

    public function getSlice($offset, $length)
    {
        return $this->queryFieldService->loadContentItemsSliceForLocation(
            $this->location,
            $this->fieldDefinitionIdentifier,
            $offset,
            $length
        );
    }
}

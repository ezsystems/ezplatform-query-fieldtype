<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\eZ\ContentView;

use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use Pagerfanta\Adapter\AdapterInterface;

final class QueryResultsPagerFantaAdapter implements AdapterInterface
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldService */
    private $queryFieldService;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var string */
    private $fieldDefinitionIdentifier;

    public function __construct(
        QueryFieldService $queryFieldService,
        Content $content,
        string $fieldDefinitionIdentifier)
    {
        $this->queryFieldService = $queryFieldService;
        $this->content = $content;
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;
    }

    public function getNbResults()
    {
        return $this->queryFieldService->countContentItems(
            $this->content,
            $this->fieldDefinitionIdentifier
        );
    }

    public function getSlice($offset, $length)
    {
        return $this->queryFieldService->loadContentItemsSlice(
            $this->content,
            $this->fieldDefinitionIdentifier,
            $offset,
            $length
        );
    }
}

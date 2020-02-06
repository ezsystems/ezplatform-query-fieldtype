<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Controller;

use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzPlatformRest\Server\Values as RestValues;
use Symfony\Component\HttpFoundation\Request;

final class QueryFieldRestController
{
    /** @var \EzSystems\EzPlatformQueryFieldType\API\QueryFieldService */
    private $queryFieldService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    public function __construct(
        QueryFieldService $queryFieldService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService
    ) {
        $this->queryFieldService = $queryFieldService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
    }

    public function getResults(Request $request, $contentId, $versionNumber, $fieldDefinitionIdentifier): RestValues\ContentList
    {
        $offset = (int)$request->query->get('offset', 0);
        $limit = (int)$request->query->get('limit', -1);

        $content = $this->contentService->loadContent($contentId, null, $versionNumber);
        if ($limit === -1 || !method_exists($this->queryFieldService, 'loadContentItemsSlice')) {
            $items = $this->queryFieldService->loadContentItems($content, $fieldDefinitionIdentifier);
        } else {
            $items = $this->queryFieldService->loadContentItemsSlice($content, $fieldDefinitionIdentifier, $offset, $limit);
        }

        return new RestValues\ContentList(
            array_map(
                function (Content $content) {
                    return new RestValues\RestContent(
                        $content->contentInfo,
                        $this->locationService->loadLocation($content->contentInfo->mainLocationId),
                        $content,
                        $this->getContentType($content->contentInfo),
                        $this->contentService->loadRelations($content->getVersionInfo())
                    );
                },
                $items
            ),
            $this->queryFieldService->countContentItems($content, $fieldDefinitionIdentifier)
        );
    }

    private function getContentType(ContentInfo $contentInfo): ContentType
    {
        static $contentTypes = [];

        if (!isset($contentTypes[$contentInfo->contentTypeId])) {
            $contentTypes[$contentInfo->contentTypeId] = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
        }

        return $contentTypes[$contentInfo->contentTypeId];
    }
}

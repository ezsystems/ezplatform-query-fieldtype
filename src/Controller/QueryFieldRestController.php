<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Controller;

use eZ\Publish\Core\REST\Server\Values\TemporaryRedirect;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Server\Values\ContentList;
use eZ\Publish\Core\REST\Server\Values\RestContent;
use Symfony\Component\Routing\RouterInterface;

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

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    public function __construct(
        QueryFieldService $queryFieldService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        RouterInterface $router
    ) {
        $this->queryFieldService = $queryFieldService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->router = $router;
    }

    public function getResults($contentId, $versionNumber, $fieldDefinitionIdentifier): ContentList
    {
        $content = $this->contentService->loadContent($contentId, null, $versionNumber);

        return new ContentList(
            array_map(
                function (Content $content) {
                    return new RestContent(
                        $content->contentInfo,
                        $this->locationService->loadLocation($content->contentInfo->mainLocationId),
                        $content,
                        $this->getContentType($content->contentInfo),
                        $this->contentService->loadRelations($content->getVersionInfo())
                    );
                },
                $this->queryFieldService->loadContentItems($content, $fieldDefinitionIdentifier)
            )
        );
    }

    public function redirectToResults(int $contentId, string $fieldDefinitionIdentifier)
    {
        return new TemporaryRedirect(
            $this->router->generate(
                'ezplatform_ezcontentquery_rest_field_version_items',
                [
                    'contentId' => $contentId,
                    'versionNumber' => $this->contentService->loadContent($contentId)->versionInfo->versionNo,
                    'fieldDefinitionIdentifier' => $fieldDefinitionIdentifier,
                ]
            )
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

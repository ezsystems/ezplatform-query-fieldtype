<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\eZ\ContentView;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;

final class FieldDefinitionIdentifierMatcher extends MultipleValued
{
    /**
     * {@inheritdoc}
     */
    public function matchLocation(Location $location)
    {
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($location->getContentInfo()->contentTypeId);

        return $this->hasFieldDefinition($contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function matchContentInfo(ContentInfo $contentInfo)
    {
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($contentInfo->contentTypeId);

        return $this->hasFieldDefinition($contentType);
    }

    /**
     * @param ContentType $contentType
     *
     * @return bool
     */
    private function hasFieldDefinition(ContentType $contentType): bool
    {
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if (in_array($fieldDefinition->identifier, $this->getValues(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return bool
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function match(View $view)
    {
        if (!$view instanceof ContentValueView) {
            return false;
        }
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($view->getContent()->contentInfo->contentTypeId);

        return $this->hasFieldDefinition($contentType);
    }
}

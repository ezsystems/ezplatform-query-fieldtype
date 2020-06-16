<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformQueryFieldType\API;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\QueryType\QueryType;

/**
 * Value object representing a QueryField's settings.
 */
class QueryFieldSettings
{
    /** @var \eZ\Publish\Core\QueryType\QueryType */
    private $queryType;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType */
    private $returnedType;

    /** @var array */
    private $parameters;

    /** @var bool */
    private $isPaginationEnabled;

    /** @var int */
    public $defaultPageLimit;

    /** @var string */
    private $contentTypeIdentifier;

    /** @var string */
    private $fieldDefinitionIdentifier;

    public function __construct(
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier,
        QueryType $queryType,
        ContentType $returnedType,
        array $parameters,
        bool $isPaginationEnabled,
        int $defaultPageLimit
    )
    {
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;
        $this->queryType = $queryType;
        $this->parameters = $parameters;
        $this->isPaginationEnabled = $isPaginationEnabled;
        $this->defaultPageLimit = $defaultPageLimit;
        $this->returnedType = $returnedType;
    }

    /**
     * @return \eZ\Publish\Core\QueryType\QueryType
     */
    public function getQueryType(): QueryType
    {
        return $this->queryType;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function getReturnedType(): ContentType
    {
        return $this->returnedType;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return bool
     */
    public function isPaginationEnabled(): bool
    {
        return $this->isPaginationEnabled;
    }

    /**
     * @return int
     *
     * @throws \EzSystems\EzPlatformQueryFieldType\Exception\PaginationIsDisabledException if pagination is disabled.
     */
    public function getDefaultPageLimit(): int
    {
        return $this->defaultPageLimit;
    }
}

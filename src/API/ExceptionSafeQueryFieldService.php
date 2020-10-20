<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\API;

use eZ\Publish\API\Repository\Values\Content\Content;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Silences exceptions when they occur in query field service, for example due to field type misconfigurations.
 */
final class ExceptionSafeQueryFieldService implements QueryFieldServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var QueryFieldServiceInterface */
    private $inner;

    public function __construct(QueryFieldServiceInterface $inner, ?LoggerInterface $logger = null)
    {
        $this->inner = $inner;
        $this->logger = $logger ?: new NullLogger();
    }

    public function loadContentItems(Content $content, string $fieldDefinitionIdentifier): iterable
    {
        try {
            return $this->inner->loadContentItems($content, $fieldDefinitionIdentifier);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return [];
        }
    }

    public function countContentItems(Content $content, string $fieldDefinitionIdentifier): int
    {
        try {
            return $this->inner->countContentItems($content, $fieldDefinitionIdentifier);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return 0;
        }
    }

    public function loadContentItemsSlice(Content $content, string $fieldDefinitionIdentifier, int $offset, int $limit): iterable
    {
        try {
            return $this->inner->loadContentItemsSlice($content, $fieldDefinitionIdentifier, $offset, $limit);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return [];
        }
    }

    public function getPaginationConfiguration(Content $content, string $fieldDefinitionIdentifier): int
    {
        return $this->inner->getPaginationConfiguration($content, $fieldDefinitionIdentifier);
    }
}

<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformQueryFieldType\Core\SettingsProvider;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettings;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettingsProvider;
use EzSystems\EzPlatformQueryFieldType\Exception\QueryFieldNotFoundException;

/**
 * Resolves a query field parameter stored in the Field Definition.
 *
 * @internal
 */
final class ChainQueryFieldSettingsProvider implements QueryFieldSettingsProvider
{
    /** QueryFieldSettingsProvider[] $providers */
    private $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @inheritDoc
     */
    public function getSettings(ContentType $contentType, string $fieldDefinitionIdentifier): QueryFieldSettings
    {
        foreach ($this->providers as $provider) {
            try {
                return $provider->getSettings($contentType, $fieldDefinitionIdentifier);
            } catch (QueryFieldNotFoundException $e) {
            }
        }

        throw new QueryFieldNotFoundException($contentType->identifier, $fieldDefinitionIdentifier);
    }
}

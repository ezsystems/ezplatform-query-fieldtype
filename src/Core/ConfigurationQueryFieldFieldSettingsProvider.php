<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformQueryFieldType\Core;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use EzSystems\EzPlatformQueryFieldType\API\QueryFieldSettingsProvider as QueryParametersTransformerInterface;
use EzSystems\EzPlatformQueryFieldType\Exception\ParametersTransformationException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Resolves a query field parameter stored in the Field Definition.
 *
 * @internal
 */
final class ConfigurationQueryFieldFieldSettingsProvider implements QueryParametersTransformerInterface
{
    /** @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage */
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function transform(Location $location, FieldDefinition $fieldDefinition): array
    {
        $content = $location->getContent();
        try {
            return $this->resolveParameters(
                $fieldDefinition->fieldSettings['Parameters'],
                [
                    'content' => $content,
                    'contentInfo' => $content->contentInfo,
                    'mainLocation' => $location,
                    'returnedType' => $fieldDefinition->fieldSettings['ReturnedType'],
                ]
            );
        } catch (\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException $e) {
            throw new ParametersTransformationException($e->getMessage());
        }
    }

    /**
     * @param array $expressions parameters that may include expressions to be resolved
     * @param array $variables
     *
     * @return array
     *
     * @throws InvalidArgumentException if an expression couldn't be converted
     */
    private function resolveParameters(array $expressions, array $variables): array
    {
        foreach ($expressions as $key => $expression) {
            if (is_array($expression)) {
                $expressions[$key] = $this->resolveParameters($expression, $variables);
            } elseif ($this->isExpression($expression)) {
                $expressions[$key] = $this->resolveExpression($expression, $variables);
            } else {
                $expressions[$key] = $expression;
            }
        }

        return $expressions;
    }

    private function isExpression($expression): bool
    {
        return is_string($expression) && substr($expression, 0, 2) === '@=';
    }

    /**
     * @param string $expression
     * @param array $variables
     *
     * @return string
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if $expression is not an expression.
     */
    private function resolveExpression(string $expression, array $variables): string
    {
        if (!$this->isExpression($expression)) {
            throw new InvalidArgumentException('expression', 'is not an expression');
        }

        return (new ExpressionLanguage())->evaluate(substr($expression, 2), $variables);
    }
}

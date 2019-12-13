<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\Symfony\DependencyInjection\Compiler;

use EzSystems\EzPlatformQueryFieldType\eZ\FieldType\Mapper\QueryFormMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class QueryTypesListPass implements CompilerPassInterface
{
    /**
     * @var \Symfony\Component\Serializer\NameConverter\NameConverterInterface
     */
    private $nameConverter;

    public function __construct()
    {
        $this->nameConverter = new CamelCaseToSnakeCaseNameConverter();
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ezpublish.query_type.registry') || !$container->has(QueryFormMapper::class)) {
            return;
        }

        $queryTypes = [];
        foreach ($container->getDefinition('ezpublish.query_type.registry')->getMethodCalls() as $methodCall) {
            if ($methodCall[0] === 'addQueryType') {
                $queryTypes[] = $methodCall[1][0];
            } elseif ($methodCall[0] === 'addQueryTypes') {
                foreach (array_keys($methodCall[1][0]) as $queryTypeIdentifier) {
                    $queryTypes[$this->buildQueryTypeName($queryTypeIdentifier)] = $queryTypeIdentifier;
                }
            }
        }

        $formMapperDefinition = $container->getDefinition(QueryFormMapper::class);
        $formMapperDefinition->setArgument('$queryTypes', $queryTypes);
    }

    /**
     * Builds a human readable name out of a query type identifier.
     *
     * @param $queryTypeIdentifier
     *
     * @return string
     */
    private function buildQueryTypeName($queryTypeIdentifier)
    {
        return ucfirst(
            str_replace('_', ' ', $this->nameConverter->normalize($queryTypeIdentifier))
        );
    }
}

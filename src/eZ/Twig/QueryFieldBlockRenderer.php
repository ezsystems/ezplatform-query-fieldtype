<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */


namespace EzSystems\EzPlatformQueryFieldType\eZ\Twig;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\MVC\Symfony\Templating\FieldBlockRendererInterface;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition as CoreFieldDefinition;
use Twig_Environment;
use Twig_Template;

/**
 * Decorator for ezpublish-kernel's FieldBlockRenderer that handles named query field types.
 */
class QueryFieldBlockRenderer implements FieldBlockRendererInterface
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Templating\FieldBlockRendererInterface */
    private $innerRenderer;

    public function __construct(FieldBlockRendererInterface $innerRenderer)
    {
        $this->innerRenderer = $innerRenderer;
    }

    /**
     * @inheritDoc
     */
    public function renderContentFieldView(Field $field, $fieldTypeIdentifier, array $params = [])
    {
        if ($this->isNamedQueryField($fieldTypeIdentifier)) {
            $fieldTypeIdentifier = 'ezcontentquery';
        }

        return $this->innerRenderer->renderContentFieldView($field, $fieldTypeIdentifier, $params);
    }

    /**
     * @inheritDoc
     */
    public function renderContentFieldEdit(Field $field, $fieldTypeIdentifier, array $params = [])
    {
        if ($this->isNamedQueryField($fieldTypeIdentifier)) {
            $fieldTypeIdentifier = 'ezcontentquery';
        }

        return $this->innerRenderer->renderContentFieldEdit($field, $fieldTypeIdentifier, $params);
    }

    /**
     * @inheritDoc
     */
    public function renderFieldDefinitionView(FieldDefinition $fieldDefinition, array $params = [])
    {
        if ($this->isNamedQueryField($fieldDefinition->fieldTypeIdentifier)) {
            $fieldDefinition = $this->overrideFieldDefinition($fieldDefinition);
        }

        return $this->innerRenderer->renderFieldDefinitionEdit($fieldDefinition, $params);
    }

    /**
     * @inheritDoc
     */
    public function renderFieldDefinitionEdit(FieldDefinition $fieldDefinition, array $params = [])
    {
        if ($this->isNamedQueryField($fieldDefinition->fieldTypeIdentifier)) {
            $fieldDefinition = $this->overrideFieldDefinition($fieldDefinition);
        }

        return $this->innerRenderer->renderFieldDefinitionEdit($fieldDefinition, $params);
    }

    private function isNamedQueryField($identifier): bool
    {
        return strpos($identifier, 'ezcontentquery_') === 0;
    }

    /**
     * @param Twig_Environment $twig
     */
    public function setTwig(Twig_Environment $twig)
    {
        $this->innerRenderer->setTwig($twig);
    }

    /**
     * @param string|Twig_Template $baseTemplate
     */
    public function setBaseTemplate($baseTemplate)
    {
        $this->innerRenderer->setBaseTemplate($baseTemplate);
    }

    /**
     * @param array $fieldViewResources
     */
    public function setFieldViewResources(array $fieldViewResources = null)
    {
        $this->innerRenderer->setFieldViewResources($fieldViewResources);
    }

    /**
     * @param array $fieldEditResources
     */
    public function setFieldEditResources(array $fieldEditResources = null)
    {
        $this->innerRenderer->setFieldEditResources($fieldEditResources);
    }

    /**
     * @param array $fieldDefinitionViewResources
     */
    public function setFieldDefinitionViewResources(array $fieldDefinitionViewResources = null)
    {
        $this->innerRenderer->setFieldDefinitionViewResources($fieldDefinitionViewResources);
    }

    /**
     * @param array $fieldDefinitionEditResources
     */
    public function setFieldDefinitionEditResources(array $fieldDefinitionEditResources = null)
    {
        $this->innerRenderer->setFieldDefinitionEditResources($fieldDefinitionEditResources);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition
     */
    protected function overrideFieldDefinition(FieldDefinition $fieldDefinition): FieldDefinition
    {
        $properties = [];
        foreach ($fieldDefinition->attributes() as $property) {
            $properties[$property] = $fieldDefinition->$property;
        }
        $properties['fieldTypeIdentifier'] = 'ezcontentquery_named';

        return new CoreFieldDefinition($properties);
    }
}

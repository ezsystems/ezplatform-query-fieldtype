<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\eZ\FieldType\Mapper;

use eZ\Publish\API\Repository\ContentTypeService;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class QueryFormMapper implements FieldDefinitionFormMapperInterface
{
    /** @var ContentTypeService */
    private $contentTypeService;

    /**
     * List of query types.
     *
     * @var array
     */
    private $queryTypes;

    public function __construct(ContentTypeService $contentTypeService, array $queryTypes = [])
    {
        $this->contentTypeService = $contentTypeService;
        $this->queryTypes = $queryTypes;
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data)
    {
        $parametersForm = $fieldDefinitionForm->getConfig()->getFormFactory()->createBuilder()
            ->create(
                'Parameters',
                Type\TextareaType::class,
                [
                    'label' => 'Parameters',
                    'property_path' => 'fieldSettings[Parameters]',
                ]
            )
            ->addModelTransformer(new ParametersTransformer())
            ->setAutoInitialize(false)
            ->getForm();

        $fieldDefinitionForm
            ->add('QueryType', Type\ChoiceType::class,
                [
                    'label' => 'Query type',
                    'property_path' => 'fieldSettings[QueryType]',
                    'choices' => $this->queryTypes,
                    'required' => true,
                ]
            )
            ->add('ReturnedType', Type\ChoiceType::class,
                [
                    'label' => 'Returned type',
                    'property_path' => 'fieldSettings[ReturnedType]',
                    'choices' => $this->getContentTypes(),
                    'required' => true,
                ]
            )
            ->add('EnablePagination', Type\CheckboxType::class,
                [
                    'label' => 'Enable pagination',
                    'property_path' => 'fieldSettings[EnablePagination]',
                    'required' => false,
                ]
            )
            ->add('ItemsPerPage', Type\NumberType::class,
                [
                    'label' => 'Items per page',
                    'property_path' => 'fieldSettings[ItemsPerPage]',
                ]
            )
            ->add($parametersForm);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ezrepoforms_content_type',
            ]);
    }

    private function getContentTypes()
    {
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                yield $contentType->getName() => $contentType->identifier;
            }
        }
    }
}

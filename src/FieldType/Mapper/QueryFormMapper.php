<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\FieldType\Mapper;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformQueryFieldType\Form\Type\FieldType\QueryFieldType;
use eZ\Publish\API\Repository\ContentTypeService;
use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use EzSystems\RepositoryForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class QueryFormMapper implements FieldDefinitionFormMapperInterface, FieldValueFormMapperInterface
{
    /** @var ContentTypeService */
    private $contentTypeService;

    /**
     * List of query types.
     *
     * @var array
     */
    private $queryTypes;

    /** @var \Symfony\Component\EventDispatcher\EventSubscriberInterface */
    private $parametersSubscriber;

    public function __construct(ContentTypeService $contentTypeService, EventSubscriberInterface $parametersSubscriber, array $queryTypes = [])
    {
        $this->contentTypeService = $contentTypeService;
        $this->queryTypes = $queryTypes;
        $this->parametersSubscriber = $parametersSubscriber;
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data)
    {
        $parametersBuilder = $fieldDefinitionForm->getConfig()->getFormFactory()
            ->createNamedBuilder(
                'Parameters',
                Type\FormType::class,
                $data->fieldSettings,
                ['property_path' => 'fieldSettings[Parameters]']
            )
            ->setAutoInitialize(false)
            ->setData($data->fieldSettings)
            ->addEventSubscriber($this->parametersSubscriber);

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
            ->add($parametersBuilder->getForm());

    }

    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $names = $fieldDefinition->getNames();
        $label = $fieldDefinition->getName($formConfig->getOption('mainLanguageCode')) ?: reset($names);

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        QueryFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired,
                            'label' => $label,
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
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

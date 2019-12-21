<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\eZ\FieldType\NamedQuery;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformQueryFieldType\eZ\FieldType\Mapper\ParametersTransformer;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Mapper implements FieldDefinitionFormMapperInterface
{
    /** @var ContentTypeService */
    private $contentTypeService;
    /**
     * @var string
     */
    private $queryType;
    /**
     * @var \eZ\Publish\Core\QueryType\QueryTypeRegistry
     */
    private $queryTypeRegistry;

    public function __construct(ContentTypeService $contentTypeService, QueryTypeRegistry $queryTypeRegistry, string $queryType)
    {
        $this->contentTypeService = $contentTypeService;
        $this->queryType = $queryType;
        $this->queryTypeRegistry = $queryTypeRegistry;
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
            ->add('ReturnedType', Type\ChoiceType::class,
                [
                    'label' => 'Returned type',
                    'property_path' => 'fieldSettings[ReturnedType]',
                    'choices' => $this->getContentTypes(),
                    'required' => true,
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

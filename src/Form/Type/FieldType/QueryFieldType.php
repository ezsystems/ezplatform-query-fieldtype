<?php

namespace EzSystems\EzPlatformQueryFieldType\Form\Type\FieldType;

use eZ\Publish\API\Repository\FieldTypeService;
use EzSystems\RepositoryForms\FieldType\DataTransformer\FieldValueTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QueryFieldType extends AbstractType
{
    /** @var FieldTypeService */
    private $fieldTypeService;

    public function __construct(FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_query';
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('query')));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $attributes = [];

        // $view->vars['QueryType'] = array_merge($view->vars['QueryType'], $attributes);
        // $view->vars['Test'] = array_merge($view->vars['Test'], $attributes);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}

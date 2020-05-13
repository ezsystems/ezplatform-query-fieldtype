<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\eZ\FieldType\NamedQuery\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldDefinitionParametersType extends AbstractType
{
    /** @var \Symfony\Component\EventDispatcher\EventSubscriberInterface */
    private $parametersSubscriber;

    public function __construct(EventSubscriberInterface $parametersSubscriber)
    {
        $this->parametersSubscriber = $parametersSubscriber;
    }

    public function getParent()
    {
        return Type\FormType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('query_type', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->parametersSubscriber);
    }
}

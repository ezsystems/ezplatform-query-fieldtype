<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformQueryFieldType\eZ\FieldType\NamedQuery\Form\EventSubscriber;

use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FieldDefinitionParametersSubscriber implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    public function __construct(QueryTypeRegistry $queryTypeRegistry)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'addParametersFormFields'];
    }

    public function addParametersFormFields(FormEvent $event)
    {
        $data = $event->getData();
        if ($data === null) {
            return;
        }

        $queryTypeIdentifier = $event->getForm()->getConfig()->getOption('query_type');
        if ($queryTypeIdentifier === null) {
            return;
        }

        $queryType = $this->queryTypeRegistry->getQueryType($queryTypeIdentifier);
        foreach ($queryType->getSupportedParameters() as $parameter) {
            $event->getForm()->add(
                $parameter,
                Type\TextType::class,
                [
                    'label' => $parameter,
                    'property_path' => sprintf('[%s]', $parameter),
                    'required' => false,
                ]
            );
        }
    }
}

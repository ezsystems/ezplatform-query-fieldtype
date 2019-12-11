<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformQueryFieldType\FieldType\Mapper;

use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ParametersSubscriber implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\QueryType\QueryTypeRegistry
     */
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
        $form = $event->getForm();

        if ($data['QueryType'] === '') {
            return;
        }

        $queryType = $this->queryTypeRegistry->getQueryType($data['QueryType']);
        foreach ($queryType->getSupportedParameters() as $parameter) {
            $form->add(
                $parameter,
                Type\TextType::class,
                [
                    'label' => $parameter,
                    'property_path' => sprintf('[Parameters][%s]', $parameter),
                    'required' => false,
                ]
            );
        }
    }
}

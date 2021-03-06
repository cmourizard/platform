<?php

namespace Oro\Bundle\ApiBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EntityScalarCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new MergeDoctrineCollectionListener());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_api_entity_scalar_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ScalarCollectionType::class;
    }
}

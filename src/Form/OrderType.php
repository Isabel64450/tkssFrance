<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
    ->add('firstName', null, [
        'label' => 'Prénom',
        'attr' => [
            'class' => 'w-full'
        ]
    ])
    ->add('lastName', null, [
        'label' => 'Nom',
        'attr' => [
            'class' => 'w-full'
        ]
    ])
    ->add('telephoneNumber', null, [
        'label' => 'Téléphone',
        'attr' => [
            'class' => 'w-full'
        ]
    ])
    ->add('email', null, [
        'label' => 'Email',
        'attr' => [
            'class' => 'w-full'
        ]
    ])
    ->add('address', null, [
        'label' => 'Adresse',
        'attr' => [
            'class' => 'w-full'
        ]
    ])
    ->add('city', EntityType::class, [
        'class' => City::class,
        'choice_label' => 'name',
        'label' => 'Ville',
        'attr' => [
            'class' => 'w-full'
        ]
    ])
    ->add('payOnDelivery', null, [
        'label' => 'Payer à la livraison'
    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}

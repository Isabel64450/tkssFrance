<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
     ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Mode de paiement',
                'choices' => [
                    'Carte bancaire' => 'stripe',
                    'Paiement en plusieurs fois (espèces)' => 'cash_split',
                ],
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}

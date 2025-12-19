<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AddressForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'Street Address',
                'attr' => ['placeholder' => '123 Main Street'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your street address.']),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'attr' => ['placeholder' => 'Tunis'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your city.']),
                ],
            ])
            ->add('state', TextType::class, [
                'label' => 'State/Province',
                'attr' => ['placeholder' => 'Tunis Governorate'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your state/province.']),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code',
                'attr' => ['placeholder' => '1000'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your postal code.']),
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'Country',
                'attr' => ['placeholder' => 'Tunisia'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your country.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}

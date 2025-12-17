<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Service;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 4],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => 'TND',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'Active',
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('provider', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Provider',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}

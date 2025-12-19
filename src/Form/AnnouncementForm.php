<?php

namespace App\Form;

use App\Entity\Announcement;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Optional;

class AnnouncementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Vehicle Title',
                'attr' => ['placeholder' => 'e.g., Toyota Camry 2020'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Describe your vehicle in detail...', 'rows' => 4],
            ])
            ->add('brand', TextType::class, [
                'label' => 'Brand',
                'attr' => ['placeholder' => 'Toyota'],
                'required' => false,
            ])
            ->add('model', TextType::class, [
                'label' => 'Model',
                'attr' => ['placeholder' => 'Camry'],
                'required' => false,
            ])
            ->add('year', IntegerType::class, [
                'label' => 'Year',
                'attr' => ['placeholder' => '2020'],
                'required' => false,
            ])
            ->add('mileage', IntegerType::class, [
                'label' => 'Mileage (km)',
                'attr' => ['placeholder' => '45000'],
                'required' => false,
            ])
            ->add('fuelType', ChoiceType::class, [
                'label' => 'Fuel Type',
                'choices' => [
                    'Petrol' => 'petrol',
                    'Diesel' => 'diesel',
                    'Hybrid' => 'hybrid',
                    'Electric' => 'electric',
                    'Other' => 'other',
                ],
                'required' => false,
                'placeholder' => 'Select fuel type',
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price (TND)',
                'attr' => ['placeholder' => '18500'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Available' => 'available',
                    'Sold' => 'sold',
                    'Reserved' => 'reserved',
                ],
                'placeholder' => 'Select status',
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'attr' => ['placeholder' => 'Tunis, Tunisia'],
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Select a category',
            ])
            ->add('mainPhotoFile', FileType::class, [
                'label' => 'Main Photo',
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new Optional([
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                            'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WebP)',
                        ])
                    ])
                ],
            ])
            ->add('photoFiles', FileType::class, [
                'label' => 'Additional Photos',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new Optional([
                        new All([
                            new File([
                                'maxSize' => '5M',
                                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                                'mimeTypesMessage' => 'Please upload valid images (JPEG, PNG, WebP)',
                            ])
                        ])
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Announcement::class,
        ]);
    }
}

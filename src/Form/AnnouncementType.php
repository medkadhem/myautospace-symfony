<?php

namespace App\Form;

use App\Entity\Announcement;
use App\Entity\AnnouncementType as AnnouncementTypeEntity;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AnnouncementType extends AbstractType
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
            ->add('brand', TextType::class, [
                'label' => 'Brand',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('model', TextType::class, [
                'label' => 'Model',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('year', IntegerType::class, [
                'label' => 'Year',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1900, 'max' => date('Y') + 1],
            ])
            ->add('mileage', IntegerType::class, [
                'label' => 'Mileage (km)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('fuelType', ChoiceType::class, [
                'label' => 'Fuel Type',
                'required' => false,
                'choices' => [
                    'Gasoline' => 'Gasoline',
                    'Diesel' => 'Diesel',
                    'Electric' => 'Electric',
                    'Hybrid' => 'Hybrid',
                    'LPG' => 'LPG',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => 'TND',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Draft' => 'draft',
                    'Active' => 'active',
                    'Sold' => 'sold',
                    'Inactive' => 'inactive',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isSponsored', ChoiceType::class, [
                'label' => 'Sponsored',
                'choices' => [
                    'No' => false,
                    'Yes' => true,
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Published At',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Start Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'End Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('budget', MoneyType::class, [
                'label' => 'Budget',
                'currency' => 'TND',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('vendor', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Vendor',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', EntityType::class, [
                'class' => AnnouncementTypeEntity::class,
                'choice_label' => 'name',
                'label' => 'Announcement Type',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Categories',
                'multiple' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('mainPhotoFile', FileType::class, [
                'label' => 'Main Photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WEBP)',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('photoFiles', FileType::class, [
                'label' => 'Additional Photos',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload valid images (JPEG, PNG, WEBP)',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
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

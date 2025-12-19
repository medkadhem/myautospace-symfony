<?php

namespace App\Form;

use App\Entity\Announcement;
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
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Optional;

class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter listing title'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Describe your listing...'],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => 'TND',
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Active' => 'active',
                    'Draft' => 'draft',
                    'Inactive' => 'inactive',
                    'Sold' => 'sold',
                ],
                'data' => 'draft',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('brand', TextType::class, [
                'label' => 'Brand',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Toyota, BMW'],
            ])
            ->add('model', TextType::class, [
                'label' => 'Model',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Corolla, X5'],
            ])
            ->add('year', IntegerType::class, [
                'label' => 'Year',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1900, 'max' => date('Y') + 1, 'placeholder' => date('Y')],
            ])
            ->add('mileage', IntegerType::class, [
                'label' => 'Mileage (km)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0, 'placeholder' => '0'],
            ])
            ->add('fuelType', ChoiceType::class, [
                'label' => 'Fuel Type',
                'required' => false,
                'choices' => [
                    'Select fuel type' => '',
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
                'attr' => ['class' => 'form-control', 'placeholder' => 'City, Country'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Select a category',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isSponsored', ChoiceType::class, [
                'label' => 'Sponsored Listing',
                'choices' => [
                    'No' => false,
                    'Yes' => true,
                ],
                'data' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('budget', MoneyType::class, [
                'label' => 'Budget (for sponsored)',
                'currency' => 'TND',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00'],
            ])
            ->add('vendor', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Vendor',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('mainPhotoFile', FileType::class, [
                'label' => 'Main Photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Optional([
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
                    ])
                ],
                'attr' => ['class' => 'form-control', 'accept' => 'image/*'],
            ])
            ->add('photoFiles', FileType::class, [
                'label' => 'Additional Photos',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new Optional([
                        new All([
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
                        ])
                    ])
                ],
                'attr' => ['class' => 'form-control', 'accept' => 'image/*'],
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

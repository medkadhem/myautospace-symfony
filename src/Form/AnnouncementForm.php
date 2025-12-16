<?php

namespace App\Form;

use App\Entity\Announcement;
use App\Entity\AnnouncementType;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('status')
            ->add('publishedAt')
            ->add('isSponsored')
            ->add('startDate')
            ->add('endDate')
            ->add('budget')
            ->add('vendor', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('type', EntityType::class, [
                'class' => self::class,
                'choice_label' => 'id',
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
                'multiple' => true,
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

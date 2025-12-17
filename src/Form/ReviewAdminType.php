<?php

namespace App\Form;

use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Rating',
                'choices' => [
                    '1 Star' => 1,
                    '2 Stars' => 2,
                    '3 Stars' => 3,
                    '4 Stars' => 4,
                    '5 Stars' => 5,
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Comment',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
            ])
            ->add('author', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Author',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'title',
                'label' => 'Service',
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}

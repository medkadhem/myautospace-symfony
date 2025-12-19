<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReviewForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'choices' => [
                    '5 stars' => 5,
                    '4 stars' => 4,
                    '3 stars' => 3,
                    '2 stars' => 2,
                    '1 star' => 1,
                ],
                'expanded' => true,
                'label' => 'Your Rating',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select a rating']),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 5,
                        'notInRangeMessage' => 'Rating must be between {{ min }} and {{ max }}',
                    ]),
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Your Review',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Share your experience... What did you like? What could be improved?',
                ],
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

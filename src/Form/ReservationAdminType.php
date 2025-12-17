<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Service;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reservationDate', DateType::class, [
                'label' => 'Reservation Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Start Time',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'End Time',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Pending' => 'pending',
                    'Confirmed' => 'confirmed',
                    'Cancelled' => 'cancelled',
                    'Completed' => 'completed',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('client', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Client',
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
            'data_class' => Reservation::class,
        ]);
    }
}

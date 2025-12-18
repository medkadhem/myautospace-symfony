<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? true;
        
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $isEdit ? 'New Password (leave empty to keep current)' : 'Password',
                'mapped' => false,
                'required' => !$isEdit,
                'attr' => ['class' => 'form-control', 'placeholder' => $isEdit ? 'Leave empty to keep current password' : 'Enter password'],
            ])
            ->add('phone', IntegerType::class, [
                'label' => 'Phone',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'Active',
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Provider' => 'ROLE_PROVIDER',
                ],
                'multiple' => true,
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => true,
        ]);
    }
}

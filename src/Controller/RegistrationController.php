<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $authenticator
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            if ($plainPassword === '') {
                $this->addFlash('error', 'Password cannot be empty.');
            } else {
                $hashed = $hasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashed);

                // Role selection: Provider or Client
                $role = (string) $form->get('role')->getData();
                $roles = ['ROLE_USER'];
                
                if ($role === 'ROLE_PROVIDER') {
                    $roles[] = 'ROLE_PROVIDER';
                    $user->setUserType('provider');
                } else {
                    $user->setUserType('client');
                }
                
                $user->setRoles(array_values(array_unique($roles)));

                $em->persist($user);
                $em->flush();

                // Automatically log in the user after registration
                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}

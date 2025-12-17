<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
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

                // role selection
                $role = (string) $form->get('role')->getData();
                $roles = ['ROLE_USER'];
                if (in_array($role, ['ROLE_CLIENT','ROLE_SELLER','ROLE_PROVIDER','ROLE_ADMIN'], true)) {
                    $roles[] = $role;
                }
                $user->setRoles(array_values(array_unique($roles)));

                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Account created. You can now sign in.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}

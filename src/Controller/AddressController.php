<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/my-address')]
#[IsGranted('ROLE_USER')]
final class AddressController extends AbstractController
{
    #[Route('', name: 'app_my_address', methods: ['GET', 'POST'])]
    public function manage(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $address = $user->getAddress();

        // Create new address if user doesn't have one
        if (!$address) {
            $address = new Address();
            $address->setOwner($user);
        }

        $form = $this->createForm(AddressForm::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($address);
            $em->flush();

            $this->addFlash('success', 'Address saved successfully!');
            return $this->redirectToRoute('app_my_address');
        }

        return $this->render('address/manage.html.twig', [
            'form' => $form,
            'address' => $address,
        ]);
    }

    #[Route('/delete', name: 'app_address_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $address = $user->getAddress();

        if ($address && $this->isCsrfTokenValid('delete_address', $request->request->get('_token'))) {
            // First, clear the relationship from the user side
            $user->setAddress(null);

            // Then remove the address
            $em->remove($address);
            $em->flush();

            $this->addFlash('success', 'Address deleted successfully!');
        }

        return $this->redirectToRoute('app_my_address');
    }
}

<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceForm;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/service')]
final class ServiceController extends AbstractController
{
    #[Route(name: 'app_service_index', methods: ['GET'])]
    public function index(ServiceRepository $serviceRepository): Response
    {
        $user = $this->getUser();

        // If user is a provider, show only their services
        if ($user && $this->isGranted('ROLE_PROVIDER')) {
            $services = $serviceRepository->findBy(['provider' => $user], ['createdAt' => 'DESC']);
        } else {
            // For other users (or guests), show all active services
            $services = $serviceRepository->findBy(['isActive' => true], ['createdAt' => 'DESC']);
        }

        return $this->render('service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/new', name: 'app_service_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceForm::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user) {
                $service->setProvider($user);
            }

            // Handle photo upload
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/services',
                        $newFilename
                    );
                    $service->setPhoto($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload photo');
                }
            }

            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', 'Service created successfully!');
            return $this->redirectToRoute('app_dashboard_provider', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_service_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceForm::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle photo upload
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/services',
                        $newFilename
                    );
                    $service->setPhoto($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload photo');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Service updated successfully!');
            return $this->redirectToRoute('app_dashboard_provider', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        // Check if user is the provider of this service
        if ($service->getProvider() !== $this->getUser()) {
            $this->addFlash('error', 'You can only delete your own services.');
            return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->getPayload()->getString('_token'))) {
            // Check if service has active reservations
            if ($service->getReservations()->count() > 0) {
                $this->addFlash('error', 'Cannot delete this service because it has ' . $service->getReservations()->count() . ' reservations. Please cancel or complete the reservations first.');
                return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
            }

            try {
                // Reviews will be automatically deleted due to cascade: ['remove']
                $entityManager->remove($service);
                $entityManager->flush();
                $this->addFlash('success', 'Service and all associated reviews deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while deleting the service. Please try again.');
            }
        }

        return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
    }
}

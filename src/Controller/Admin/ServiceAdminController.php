<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Form\ServiceAdminType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/services')]
class ServiceAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_service_index', methods: ['GET'])]
    public function index(ServiceRepository $serviceRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/service/index.html.twig', [
            'services' => $serviceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $service = new Service();
        $form = $this->createForm(ServiceAdminType::class, $service);
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

            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', 'Service created successfully!');
            return $this->redirectToRoute('app_admin_service_index');
        }

        return $this->render('admin/service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ServiceAdminType::class, $service);
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
            return $this->redirectToRoute('app_admin_service_index');
        }

        return $this->render('admin/service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            // Check if service has reservations
            if ($service->getReservations()->count() > 0) {
                $this->addFlash('error', 'Cannot delete this service because it has ' . $service->getReservations()->count() . ' reservations. Please delete or complete the reservations first.');
                return $this->redirectToRoute('app_admin_service_index');
            }

            try {
                // Reviews will be automatically deleted due to cascade: ['remove']
                $entityManager->remove($service);
                $entityManager->flush();
                $this->addFlash('success', 'Service and all associated reviews deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while deleting the service: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_admin_service_index');
    }
}

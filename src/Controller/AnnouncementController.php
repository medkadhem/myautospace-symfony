<?php

namespace App\Controller;

use App\Entity\Announcement;
use App\Form\AnnouncementForm;
use App\Repository\AnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/announcement')]
final class AnnouncementController extends AbstractController
{
    #[Route(name: 'app_announcement_index', methods: ['GET'])]
    public function index(AnnouncementRepository $announcementRepository): Response
    {
        return $this->render('announcement/index.html.twig', [
            'announcements' => $announcementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_announcement_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $announcement = new Announcement();
        $form = $this->createForm(AnnouncementForm::class, $announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the current user as vendor
            $user = $this->getUser();
            if ($user) {
                $announcement->setVendor($user);
            }

            // Set default values for required fields
            if (!$announcement->getPublishedAt()) {
                $announcement->setPublishedAt(new \DateTimeImmutable());
            }
            if (!$announcement->getStartDate()) {
                $announcement->setStartDate(new \DateTimeImmutable());
            }
            if (!$announcement->getEndDate()) {
                $announcement->setEndDate(new \DateTimeImmutable('+30 days'));
            }
            if (!$announcement->getBudget()) {
                $announcement->setBudget(0);
            }

            // Handle main photo upload
            $mainPhotoFile = $form->get('mainPhotoFile')->getData();
            if ($mainPhotoFile) {
                $newFilename = uniqid().'.'.$mainPhotoFile->guessExtension();
                try {
                    $mainPhotoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/announcements',
                        $newFilename
                    );
                    $announcement->setMainPhoto($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload main photo');
                }
            }

            // Handle additional photos upload
            $photoFiles = $form->get('photoFiles')->getData();
            if ($photoFiles) {
                $photos = [];
                foreach ($photoFiles as $photoFile) {
                    $newFilename = uniqid().'.'.$photoFile->guessExtension();
                    try {
                        $photoFile->move(
                            $this->getParameter('kernel.project_dir').'/public/uploads/announcements',
                            $newFilename
                        );
                        $photos[] = $newFilename;
                    } catch (\Exception $e) {
                        // Continue with other photos
                    }
                }
                $announcement->setPhotos($photos);
            }

            $entityManager->persist($announcement);
            $entityManager->flush();

            $this->addFlash('success', 'Listing created successfully!');
            return $this->redirectToRoute('app_dashboard_provider', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('announcement/new.html.twig', [
            'announcement' => $announcement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_announcement_show', methods: ['GET'])]
    public function show(Announcement $announcement): Response
    {
        return $this->render('announcement/show.html.twig', [
            'announcement' => $announcement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_announcement_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function edit(Request $request, Announcement $announcement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnnouncementForm::class, $announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle main photo upload
            $mainPhotoFile = $form->get('mainPhotoFile')->getData();
            if ($mainPhotoFile) {
                $newFilename = uniqid().'.'.$mainPhotoFile->guessExtension();
                try {
                    $mainPhotoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/announcements',
                        $newFilename
                    );
                    $announcement->setMainPhoto($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload main photo');
                }
            }

            // Handle additional photos upload
            $photoFiles = $form->get('photoFiles')->getData();
            if ($photoFiles) {
                $existingPhotos = $announcement->getPhotos() ?? [];
                foreach ($photoFiles as $photoFile) {
                    $newFilename = uniqid().'.'.$photoFile->guessExtension();
                    try {
                        $photoFile->move(
                            $this->getParameter('kernel.project_dir').'/public/uploads/announcements',
                            $newFilename
                        );
                        $existingPhotos[] = $newFilename;
                    } catch (\Exception $e) {
                        // Continue with other photos
                    }
                }
                $announcement->setPhotos($existingPhotos);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Listing updated successfully!');
            return $this->redirectToRoute('app_dashboard_provider', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('announcement/edit.html.twig', [
            'announcement' => $announcement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_announcement_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function delete(Request $request, Announcement $announcement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$announcement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($announcement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_announcement_index', [], Response::HTTP_SEE_OTHER);
    }
}

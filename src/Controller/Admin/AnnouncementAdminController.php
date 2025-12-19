<?php

namespace App\Controller\Admin;

use App\Entity\Announcement;
use App\Form\AnnouncementForm;
use App\Repository\AnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/announcements')]
class AnnouncementAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_announcement_index', methods: ['GET'])]
    public function index(AnnouncementRepository $announcementRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/announcement/index.html.twig', [
            'announcements' => $announcementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_announcement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $announcement = new Announcement();
        $form = $this->createForm(AnnouncementForm::class, $announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle category selection
            $category = $form->get('category')->getData();
            if ($category) {
                $announcement->getCategories()->clear();
                $announcement->addCategory($category);
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
            if ($photoFiles && is_iterable($photoFiles)) {
                $photos = [];
                foreach ($photoFiles as $photoFile) {
                    if ($photoFile) {
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
                }
                if (!empty($photos)) {
                    $announcement->setPhotos($photos);
                }
            }

            $entityManager->persist($announcement);
            $entityManager->flush();

            $this->addFlash('success', 'Announcement created successfully!');
            return $this->redirectToRoute('app_admin_announcement_index');
        }

        return $this->render('admin/announcement/new.html.twig', [
            'announcement' => $announcement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_announcement_show', methods: ['GET'])]
    public function show(Announcement $announcement): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/announcement/show.html.twig', [
            'announcement' => $announcement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_announcement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Announcement $announcement, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Set the current category for the form
        $currentCategory = $announcement->getCategories()->first() ?: null;

        $form = $this->createForm(AnnouncementForm::class, $announcement);
        
        // Set the category field value
        if ($currentCategory) {
            $form->get('category')->setData($currentCategory);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle category selection
            $category = $form->get('category')->getData();
            $announcement->getCategories()->clear();
            if ($category) {
                $announcement->addCategory($category);
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
            if ($photoFiles && is_iterable($photoFiles)) {
                $existingPhotos = $announcement->getPhotos() ?? [];
                foreach ($photoFiles as $photoFile) {
                    if ($photoFile) {
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
                }
                $announcement->setPhotos($existingPhotos);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Announcement updated successfully!');
            return $this->redirectToRoute('app_admin_announcement_index');
        }

        return $this->render('admin/announcement/edit.html.twig', [
            'announcement' => $announcement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_announcement_delete', methods: ['POST'])]
    public function delete(Request $request, Announcement $announcement, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$announcement->getId(), $request->request->get('_token'))) {
            // Remove photos
            if ($announcement->getMainPhoto()) {
                $fileUploader->remove($announcement->getMainPhoto());
            }
            if ($announcement->getPhotos()) {
                foreach ($announcement->getPhotos() as $photo) {
                    $fileUploader->remove($photo);
                }
            }

            $entityManager->remove($announcement);
            $entityManager->flush();

            $this->addFlash('success', 'Announcement deleted successfully!');
        }

        return $this->redirectToRoute('app_admin_announcement_index');
    }
}

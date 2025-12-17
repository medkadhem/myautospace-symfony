<?php

namespace App\Controller\Admin;

use App\Entity\Announcement;
use App\Form\AnnouncementType;
use App\Repository\AnnouncementRepository;
use App\Service\FileUploader;
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
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $announcement = new Announcement();
        $form = $this->createForm(AnnouncementType::class, $announcement);
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
                $mainPhotoFilename = $fileUploader->upload($mainPhotoFile, 'announcements');
                $announcement->setMainPhoto($mainPhotoFilename);
            }

            // Handle additional photos upload
            $photoFiles = $form->get('photoFiles')->getData();
            if ($photoFiles) {
                $photos = [];
                foreach ($photoFiles as $photoFile) {
                    $photoFilename = $fileUploader->upload($photoFile, 'announcements');
                    $photos[] = $photoFilename;
                }
                $announcement->setPhotos($photos);
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
    public function edit(Request $request, Announcement $announcement, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $oldMainPhoto = $announcement->getMainPhoto();
        $oldPhotos = $announcement->getPhotos();

        // Set the current category for the form
        $currentCategory = $announcement->getCategories()->first() ?: null;

        $form = $this->createForm(AnnouncementType::class, $announcement);
        
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
                if ($oldMainPhoto) {
                    $fileUploader->remove($oldMainPhoto);
                }
                $mainPhotoFilename = $fileUploader->upload($mainPhotoFile, 'announcements');
                $announcement->setMainPhoto($mainPhotoFilename);
            }

            // Handle additional photos upload
            $photoFiles = $form->get('photoFiles')->getData();
            if ($photoFiles) {
                // Remove old photos
                if ($oldPhotos) {
                    foreach ($oldPhotos as $oldPhoto) {
                        $fileUploader->remove($oldPhoto);
                    }
                }

                $photos = [];
                foreach ($photoFiles as $photoFile) {
                    $photoFilename = $fileUploader->upload($photoFile, 'announcements');
                    $photos[] = $photoFilename;
                }
                $announcement->setPhotos($photos);
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

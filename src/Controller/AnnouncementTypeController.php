<?php

namespace App\Controller;

use App\Entity\AnnouncementType;
use App\Form\AnnouncementTypeForm;
use App\Repository\AnnouncementTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/announcement/type')]
final class AnnouncementTypeController extends AbstractController
{
    #[Route(name: 'app_announcement_type_index', methods: ['GET'])]
    public function index(AnnouncementTypeRepository $announcementTypeRepository): Response
    {
        return $this->render('announcement_type/index.html.twig', [
            'announcement_types' => $announcementTypeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_announcement_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $announcementType = new AnnouncementType();
        $form = $this->createForm(AnnouncementTypeForm::class, $announcementType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($announcementType);
            $entityManager->flush();

            return $this->redirectToRoute('app_announcement_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('announcement_type/new.html.twig', [
            'announcement_type' => $announcementType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_announcement_type_show', methods: ['GET'])]
    public function show(AnnouncementType $announcementType): Response
    {
        return $this->render('announcement_type/show.html.twig', [
            'announcement_type' => $announcementType,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_announcement_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AnnouncementType $announcementType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnnouncementTypeForm::class, $announcementType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_announcement_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('announcement_type/edit.html.twig', [
            'announcement_type' => $announcementType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_announcement_type_delete', methods: ['POST'])]
    public function delete(Request $request, AnnouncementType $announcementType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$announcementType->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($announcementType);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_announcement_type_index', [], Response::HTTP_SEE_OTHER);
    }
}

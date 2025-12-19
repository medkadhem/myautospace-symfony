<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use App\Form\ReviewAdminType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reviews')]
class ReviewAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/review/index.html.twig', [
            'reviews' => $reviewRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_review_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $review = new Review();
        $form = $this->createForm(ReviewAdminType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Review created successfully!');
            return $this->redirectToRoute('app_admin_review_index');
        }

        return $this->render('admin/review/new.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_review_show', methods: ['GET'])]
    public function show(Review $review): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/review/show.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_review_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ReviewAdminType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Review updated successfully!');
            return $this->redirectToRoute('app_admin_review_index');
        }

        return $this->render('admin/review/edit.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_review_delete', methods: ['POST'])]
    public function delete(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->request->get('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();

            $this->addFlash('success', 'Review deleted successfully!');
        }

        return $this->redirectToRoute('app_admin_review_index');
    }
}

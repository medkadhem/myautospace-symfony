<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReservationRepository;
use App\Repository\ReviewRepository;
use App\Repository\ServiceRepository;
use App\Repository\AnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/review')]
#[IsGranted('ROLE_USER')]
final class ReviewController extends AbstractController
{
    #[Route(name: 'app_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        $user = $this->getUser();
        
        // Users see their own reviews
        $reviews = $reviewRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('review/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/create/reservation/{reservationId}', name: 'app_review_create_from_reservation', methods: ['GET', 'POST'])]
    public function createFromReservation(
        int $reservationId,
        Request $request,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $reservation = $reservationRepository->find($reservationId);
        
        if (!$reservation) {
            throw $this->createNotFoundException('Reservation not found');
        }

        // Check permissions: must be the client and reservation must be completed
        if ($reservation->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only review your own reservations');
        }

        if ($reservation->getStatus() !== 'completed') {
            $this->addFlash('error', 'You can only review completed reservations');
            return $this->redirectToRoute('app_reservation_index');
        }

        if ($reservation->hasReview()) {
            $this->addFlash('error', 'You have already reviewed this reservation');
            return $this->redirectToRoute('app_reservation_index');
        }

        $review = new Review();
        $review->setAuthor($this->getUser());
        $review->setReservation($reservation);
        $review->setCreatedAt(new \DateTimeImmutable());

        if ($reservation->getService()) {
            $review->setService($reservation->getService());
            $review->setReviewer($reservation->getService()->getProvider());
        } else {
            $review->setAnnouncement($reservation->getAnnouncement());
            $review->setReviewer($reservation->getAnnouncement()->getVendor());
        }

        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'Invalid rating. Please select between 1 and 5 stars');
            } else {
                $review->setRating($rating);
                $review->setComment($comment);

                if (!$this->isCsrfTokenValid('create_review', $request->request->get('_token'))) {
                    $this->addFlash('error', 'Invalid security token');
                    return $this->redirectToRoute('app_review_create_from_reservation', ['reservationId' => $reservationId]);
                }

                $entityManager->persist($review);
                $entityManager->flush();

                $this->addFlash('success', '⭐ Review submitted successfully!');
                return $this->redirectToRoute('app_reservation_index');
            }
        }

        return $this->render('review/create.html.twig', [
            'reservation' => $reservation,
            'review' => $review,
        ]);
    }

    #[Route('/create/service/{serviceId}', name: 'app_review_create_for_service', methods: ['GET', 'POST'])]
    public function createForService(
        int $serviceId,
        Request $request,
        ServiceRepository $serviceRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $service = $serviceRepository->find($serviceId);
        
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        $review = new Review();
        $review->setAuthor($this->getUser());
        $review->setService($service);
        $review->setReviewer($service->getProvider());
        $review->setCreatedAt(new \DateTimeImmutable());

        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'Invalid rating. Please select between 1 and 5 stars');
            } else {
                $review->setRating($rating);
                $review->setComment($comment);

                if (!$this->isCsrfTokenValid('create_review', $request->request->get('_token'))) {
                    $this->addFlash('error', 'Invalid security token');
                    return $this->redirectToRoute('app_review_create_for_service', ['serviceId' => $serviceId]);
                }

                $entityManager->persist($review);
                $entityManager->flush();

                $this->addFlash('success', '⭐ Review submitted successfully!');
                return $this->redirectToRoute('app_service_show', ['id' => $serviceId]);
            }
        }

        return $this->render('review/create.html.twig', [
            'service' => $service,
            'review' => $review,
        ]);
    }

    #[Route('/create/announcement/{announcementId}', name: 'app_review_create_for_announcement', methods: ['GET', 'POST'])]
    public function createForAnnouncement(
        int $announcementId,
        Request $request,
        AnnouncementRepository $announcementRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $announcement = $announcementRepository->find($announcementId);
        
        if (!$announcement) {
            throw $this->createNotFoundException('Announcement not found');
        }

        $review = new Review();
        $review->setAuthor($this->getUser());
        $review->setAnnouncement($announcement);
        $review->setReviewer($announcement->getVendor());
        $review->setCreatedAt(new \DateTimeImmutable());

        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'Invalid rating. Please select between 1 and 5 stars');
            } else {
                $review->setRating($rating);
                $review->setComment($comment);

                if (!$this->isCsrfTokenValid('create_review', $request->request->get('_token'))) {
                    $this->addFlash('error', 'Invalid security token');
                    return $this->redirectToRoute('app_review_create_for_announcement', ['announcementId' => $announcementId]);
                }

                $entityManager->persist($review);
                $entityManager->flush();

                $this->addFlash('success', '⭐ Review submitted successfully!');
                return $this->redirectToRoute('app_announcement_show', ['id' => $announcementId]);
            }
        }

        return $this->render('review/create.html.twig', [
            'announcement' => $announcement,
            'review' => $review,
        ]);
    }

    #[Route('/{id}', name: 'app_review_show', methods: ['GET'])]
    public function show(Review $review): Response
    {
        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_review_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        // Only author can edit
        if ($review->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own reviews');
        }

        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'Invalid rating. Please select between 1 and 5 stars');
            } else {
                if (!$this->isCsrfTokenValid('edit_review'.$review->getId(), $request->request->get('_token'))) {
                    $this->addFlash('error', 'Invalid security token');
                    return $this->redirectToRoute('app_review_edit', ['id' => $review->getId()]);
                }

                $review->setRating($rating);
                $review->setComment($comment);
                $entityManager->flush();

                $this->addFlash('success', '✏️ Review updated successfully!');
                return $this->redirectToRoute('app_review_index');
            }
        }

        return $this->render('review/edit.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('/{id}', name: 'app_review_delete', methods: ['POST'])]
    public function delete(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        // Only author or admin can delete
        if ($review->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You can only delete your own reviews');
        }

        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();
            
            $this->addFlash('success', '🗑️ Review deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid security token');
        }

        return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);
    }
}

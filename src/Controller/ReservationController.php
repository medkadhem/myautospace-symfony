<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationForm;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationForm::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user) {
                $reservation->setClient($user);
                
                // Set provider based on announcement or service
                if ($reservation->getAnnouncement()) {
                    $reservation->setProvider($reservation->getAnnouncement()->getVendor());
                } elseif ($reservation->getService()) {
                    $reservation->setProvider($reservation->getService()->getProvider());
                }
            }
            
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/create/{serviceId}', name: 'app_reservation_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createFromService(int $serviceId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = $entityManager->getRepository(\App\Entity\Service::class)->find($serviceId);
        
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        if (!$this->isCsrfTokenValid('create_reservation', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid token');
            return $this->redirectToRoute('app_service_show', ['id' => $serviceId]);
        }

        $reservation = new Reservation();
        $reservation->setClient($this->getUser());
        $reservation->setProvider($service->getProvider());
        $reservation->setService($service);
        $reservation->setPrice($service->getPrice());
        $reservation->setStatus('pending');
        
        // Parse date and time from request
        $dateString = $request->request->get('reservationDate');
        $timeString = $request->request->get('startTime');
        $comment = $request->request->get('comment');
        
        if ($dateString) {
            $reservation->setReservationDate(new \DateTimeImmutable($dateString));
        }
        
        if ($timeString) {
            $reservation->setStartTime(new \DateTimeImmutable($timeString));
            
            // Calculate end time based on service duration
            $endTime = (new \DateTimeImmutable($timeString))->modify('+' . $service->getDuration() . ' minutes');
            $reservation->setEndTime($endTime);
        }
        
        if ($comment) {
            $reservation->setComment($comment);
        }

        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Your booking has been submitted! The provider will confirm it shortly.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/{id}/confirm', name: 'app_reservation_confirm', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function confirm(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getProvider() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $reservation->setStatus('confirmed');
        $entityManager->flush();

        $this->addFlash('success', 'Reservation confirmed!');
        return $this->redirectToRoute('app_dashboard_provider');
    }

    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getClient() !== $this->getUser() && $reservation->getProvider() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $reservation->setStatus('cancelled');
        $entityManager->flush();

        $this->addFlash('success', 'Reservation cancelled.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/{id}/complete', name: 'app_reservation_complete', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function complete(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getProvider() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $reservation->setStatus('completed');
        $entityManager->flush();

        $this->addFlash('success', 'Reservation marked as completed!');
        return $this->redirectToRoute('app_dashboard_provider');
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationForm::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}

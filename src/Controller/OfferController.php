<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Announcement;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/offers')]
#[IsGranted('ROLE_USER')]
class OfferController extends AbstractController
{
    #[Route('', name: 'app_offers', methods: ['GET'])]
    public function index(OfferRepository $offerRepo): Response
    {
        $user = $this->getUser();
        
        // Get offers made by user and offers received (if provider)
        $madeOffers = $offerRepo->findByBuyer($user);
        $receivedOffers = [];
        
        if ($user->isProvider()) {
            $receivedOffers = $offerRepo->findBySeller($user);
        }

        return $this->render('offer/index.html.twig', [
            'madeOffers' => $madeOffers,
            'receivedOffers' => $receivedOffers,
        ]);
    }

    #[Route('/make/{announcementId}', name: 'app_offer_make', methods: ['POST'])]
    public function make(
        int $announcementId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $announcement = $em->getRepository(Announcement::class)->find($announcementId);
        
        if (!$announcement) {
            throw $this->createNotFoundException();
        }

        $amount = $request->request->get('amount');
        $message = $request->request->get('message');

        if ($amount) {
            $offer = new Offer();
            $offer->setBuyer($this->getUser());
            $offer->setAnnouncement($announcement);
            $offer->setAmount((float)$amount);
            $offer->setMessage($message);
            
            $em->persist($offer);
            $em->flush();

            $this->addFlash('success', 'Your offer has been sent to the seller!');
        }

        return $this->redirectToRoute('app_announcement_show', ['id' => $announcementId]);
    }

    #[Route('/{id}/accept', name: 'app_offer_accept', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function accept(Offer $offer, EntityManagerInterface $em): Response
    {
        // Check if user owns the announcement
        if ($offer->getAnnouncement()->getVendor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $offer->setStatus('accepted');
        $offer->setRespondedAt(new \DateTimeImmutable());
        $em->flush();

        $this->addFlash('success', 'Offer accepted!');
        return $this->redirectToRoute('app_offers');
    }

    #[Route('/{id}/reject', name: 'app_offer_reject', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function reject(Offer $offer, EntityManagerInterface $em): Response
    {
        // Check if user owns the announcement
        if ($offer->getAnnouncement()->getVendor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $offer->setStatus('rejected');
        $offer->setRespondedAt(new \DateTimeImmutable());
        $em->flush();

        $this->addFlash('success', 'Offer rejected.');
        return $this->redirectToRoute('app_offers');
    }

    #[Route('/{id}/counter', name: 'app_offer_counter', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function counter(
        Offer $offer,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Check if user owns the announcement
        if ($offer->getAnnouncement()->getVendor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $counterAmount = $request->request->get('counter_amount');
        $counterMessage = $request->request->get('counter_message');

        if ($counterAmount) {
            $offer->setStatus('countered');
            $offer->setCounterAmount((float)$counterAmount);
            $offer->setCounterMessage($counterMessage);
            $offer->setRespondedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Counter offer sent!');
        }

        return $this->redirectToRoute('app_offers');
    }

    #[Route('/{id}/accept-counter', name: 'app_offer_accept_counter', methods: ['POST'])]
    public function acceptCounter(Offer $offer, EntityManagerInterface $em): Response
    {
        // Check if user is the buyer
        if ($offer->getBuyer() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($offer->getStatus() !== 'countered') {
            $this->addFlash('error', 'This offer has not been countered.');
            return $this->redirectToRoute('app_offers');
        }

        $offer->setStatus('accepted');
        $offer->setAmount($offer->getCounterAmount());
        $em->flush();

        $this->addFlash('success', 'Counter offer accepted!');
        return $this->redirectToRoute('app_offers');
    }
}

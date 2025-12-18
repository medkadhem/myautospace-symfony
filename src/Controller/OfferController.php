<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Announcement;
use App\Form\OfferType;
use App\Repository\OfferRepository;
use App\Repository\AnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/offer')]
final class OfferController extends AbstractController
{
    #[Route('/announcement/{id}/make-offer', name: 'app_offer_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        Announcement $announcement,
        EntityManagerInterface $entityManager
    ): Response {
        $offer = new Offer();
        $offer->setAnnouncement($announcement);
        $offer->setClient($this->getUser());

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offer);
            $entityManager->flush();

            $this->addFlash('success', 'Your offer has been submitted successfully!');
            return $this->redirectToRoute('app_announcement_show', ['id' => $announcement->getId()]);
        }

        return $this->render('offer/create.html.twig', [
            'form' => $form,
            'announcement' => $announcement,
        ]);
    }

    #[Route('/my-offers', name: 'app_offer_my_offers', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myOffers(OfferRepository $offerRepository): Response
    {
        $offers = $offerRepository->findBy(['client' => $this->getUser()]);

        return $this->render('offer/my_offers.html.twig', [
            'offers' => $offers,
        ]);
    }

    #[Route('/received-offers', name: 'app_offer_received', methods: ['GET'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function receivedOffers(OfferRepository $offerRepository): Response
    {
        $user = $this->getUser();
        $announcements = $user->getAnnouncements();
        $offers = $offerRepository->findOffersForAnnouncements($announcements);

        return $this->render('offer/received_offers.html.twig', [
            'offers' => $offers,
        ]);
    }

    #[Route('/{id}/accept', name: 'app_offer_accept', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function accept(Request $request, Offer $offer, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isOfferOwner($offer)) {
            throw $this->createAccessDeniedException('You cannot accept this offer.');
        }

        if ($this->isCsrfTokenValid('', $request->getPayload()->getString('_token'))) {
            $offer->setStatus('accepted');
            $offer->setRespondedAt(new \DateTimeImmutable());
            
            // Update announcement status to sold/reserved
            $announcement = $offer->getAnnouncement();
            $announcement->setStatus('sold');
            
            // Auto-reject all other pending offers for this announcement
            foreach ($announcement->getOffers() as $otherOffer) {
                if ($otherOffer->getId() !== $offer->getId() && $otherOffer->getStatus() === 'pending') {
                    $otherOffer->setStatus('rejected');
                    $otherOffer->setRespondedAt(new \DateTimeImmutable());
                }
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Offer accepted! Listing status changed to sold and other offers declined.');
        }

        return $this->redirectToRoute('app_offer_received');
    }

    #[Route('/{id}/reject', name: 'app_offer_reject', methods: ['POST'])]
    #[IsGranted('ROLE_PROVIDER')]
    public function reject(Request $request, Offer $offer, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isOfferOwner($offer)) {
            throw $this->createAccessDeniedException('You cannot reject this offer.');
        }

        if ($this->isCsrfTokenValid('', $request->getPayload()->getString('_token'))) {
            $offer->setStatus('rejected');
            $offer->setRespondedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Offer rejected!');
        }

        return $this->redirectToRoute('app_offer_received');
    }

    #[Route('/{id}', name: 'app_offer_show', methods: ['GET'])]
    public function show(Offer $offer): Response
    {
        return $this->render('offer/show.html.twig', [
            'offer' => $offer,
        ]);
    }

    private function isOfferOwner(Offer $offer): bool
    {
        return $offer->getAnnouncement()->getVendor() === $this->getUser();
    }
}

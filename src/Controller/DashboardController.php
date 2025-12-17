<?php

namespace App\Controller;

use App\Repository\AnnouncementRepository;
use App\Repository\ServiceRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly AnnouncementRepository $announcementRepo,
        private readonly ServiceRepository $serviceRepo,
        private readonly ReservationRepository $reservationRepo,
        private readonly UserRepository $userRepo,
        private readonly ReviewRepository $reviewRepo
    ) {
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_dashboard_admin');
        }
        if ($this->isGranted('ROLE_PROVIDER')) {
            return $this->redirectToRoute('app_dashboard_provider');
        }
        if ($this->isGranted('ROLE_SELLER')) {
            return $this->redirectToRoute('app_dashboard_seller');
        }
        return $this->redirectToRoute('app_dashboard_client');
    }

    #[Route('/dashboard/admin', name: 'app_dashboard_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(): Response
    {
        // Redirect to the main admin dashboard
        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/dashboard/client', name: 'app_dashboard_client')]
    #[IsGranted('ROLE_USER')]
    public function client(): Response
    {
        $user = $this->getUser();
        
        try {
            // Get all client reservations
            $reservations = $this->reservationRepo->findBy(
                ['client' => $user],
                ['createdAt' => 'DESC']
            );
            
            // Count confirmed/active reservations
            $activeReservations = count(array_filter($reservations, fn($r) => $r->getStatus() === 'confirmed'));
            $completedReservations = count(array_filter($reservations, fn($r) => $r->getStatus() === 'completed'));
            
            // Get recent reservations (last 5)
            $recentReservations = array_slice($reservations, 0, 5);
            
        } catch (\Exception $e) {
            $reservations = [];
            $activeReservations = 0;
            $completedReservations = 0;
            $recentReservations = [];
        }
        
        return $this->render('dashboard/client.html.twig', [
            'activeReservations' => $activeReservations,
            'completedReservations' => $completedReservations,
            'totalReservations' => count($reservations),
            'reservations' => $reservations,
            'recentReservations' => $recentReservations,
        ]);
    }

    #[Route('/dashboard/provider', name: 'app_dashboard_provider')]
    #[IsGranted('ROLE_PROVIDER')]
    public function provider(): Response
    {
        $user = $this->getUser();
        
        try {
            // Services statistics
            $services = $this->serviceRepo->findBy(['provider' => $user]);
            $activeServices = count(array_filter($services, fn($s) => $s->isActive()));
            
            // Announcements/Listings statistics
            $announcements = $this->announcementRepo->findBy(['vendor' => $user]);
            $activeListings = count(array_filter($announcements, fn($a) => in_array($a->getStatus(), ['active', 'available'])));
            $totalValue = array_sum(array_map(fn($a) => $a->getPrice() ?? 0, $announcements));
            
            // Get all reservations and filter by provider's services/announcements
            $allReservations = $this->reservationRepo->findAll();
            $providerReservations = array_filter($allReservations, function($r) use ($user) {
                $service = $r->getService();
                $announcement = $r->getAnnouncement();
                
                if ($service && $service->getProvider() === $user) {
                    return true;
                }
                if ($announcement && $announcement->getVendor() === $user) {
                    return true;
                }
                return false;
            });
            
            $totalReservations = count($providerReservations);
            $pendingReservations = count(array_filter($providerReservations, fn($r) => $r->getStatus() === 'pending'));
            $confirmedReservations = count(array_filter($providerReservations, fn($r) => $r->getStatus() === 'confirmed'));
            
            // Calculate average rating - reviews about the provider
            $allReviews = $this->reviewRepo->findAll();
            $providerReviews = array_filter($allReviews, function($r) use ($user) {
                $service = $r->getService();
                return $service && $service->getProvider() === $user;
            });
            
            $averageRating = count($providerReviews) > 0 
                ? round(array_sum(array_map(fn($r) => $r->getRating() ?? 0, $providerReviews)) / count($providerReviews), 2)
                : 0;
            
            // Recent data - sorted by creation
            usort($providerReservations, fn($a, $b) => ($b->getCreatedAt() ?? new \DateTimeImmutable()) <=> ($a->getCreatedAt() ?? new \DateTimeImmutable()));
            $recentReservations = array_slice($providerReservations, 0, 5);
            
        } catch (\Exception $e) {
            $activeServices = 0;
            $activeListings = 0;
            $totalValue = 0;
            $totalReservations = 0;
            $pendingReservations = 0;
            $confirmedReservations = 0;
            $averageRating = 0;
            $recentReservations = [];
            $services = [];
            $announcements = [];
        }
        
        return $this->render('dashboard/provider.html.twig', [
            'activeServices' => $activeServices,
            'activeListings' => $activeListings,
            'totalValue' => $totalValue,
            'totalReservations' => $totalReservations,
            'pendingReservations' => $pendingReservations,
            'confirmedReservations' => $confirmedReservations,
            'averageRating' => $averageRating,
            'recentReservations' => $recentReservations,
            'announcements' => $announcements,
            'services' => $services,
        ]);
    }

    #[Route('/dashboard/seller', name: 'app_dashboard_seller')]
    #[IsGranted('ROLE_SELLER')]
    public function seller(): Response
    {
        $user = $this->getUser();
        
        try {
            // Get seller's announcements
            $announcements = $this->announcementRepo->findBy(['vendor' => $user], ['createdAt' => 'DESC']);
            $activeListings = count(array_filter($announcements, fn($a) => in_array($a->getStatus(), ['active', 'available'])));
            $totalValue = array_sum(array_map(fn($a) => $a->getPrice() ?? 0, $announcements));
            
            // Get recent listings (last 5)
            $recentListings = array_slice($announcements, 0, 5);
            
            // Calculate average rating from announcement ratings
            $ratingsArray = array_filter(array_map(fn($a) => $a->getRating(), $announcements), fn($r) => $r !== null && $r > 0);
            $averageRating = count($ratingsArray) > 0 
                ? round(array_sum($ratingsArray) / count($ratingsArray), 2)
                : 0;
            
        } catch (\Exception $e) {
            $announcements = [];
            $activeListings = 0;
            $totalValue = 0;
            $recentListings = [];
            $averageRating = 0;
        }
        
        return $this->render('dashboard/seller.html.twig', [
            'announcements' => $announcements,
            'activeListings' => $activeListings,
            'totalValue' => $totalValue,
            'recentListings' => $recentListings,
            'averageRating' => $averageRating,
        ]);
    }
}


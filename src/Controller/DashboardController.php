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
        return $this->redirectToRoute('app_dashboard_client');
    }

    #[Route('/dashboard/admin', name: 'app_dashboard_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(): Response
    {
        try {
            // Users statistics
            $allUsers = $this->userRepo->findAll();
            $totalUsers = count($allUsers);
            $activeUsers = count(array_filter($allUsers, fn($u) => $u->isActive()));
            
            // Announcements statistics
            $activeListings = count($this->announcementRepo->findBy(['status' => 'active']));
            $allAnnouncements = count($this->announcementRepo->findAll());
            
            // Services statistics
            $activeServices = count($this->serviceRepo->findBy(['isActive' => true]));
            $allServices = count($this->serviceRepo->findAll());
            
            // Reservations statistics
            $allReservations = $this->reservationRepo->findAll();
            $totalReservations = count($allReservations);
            $pendingReservations = count(array_filter($allReservations, fn($r) => $r->getStatus() === 'pending'));
            $confirmedReservations = count(array_filter($allReservations, fn($r) => $r->getStatus() === 'confirmed'));
            
            // Get recent activity
            $recentUsers = array_slice(array_reverse($allUsers), 0, 5);
            
            // Sort announcements by creation date (newest first)
            $announcements = $this->announcementRepo->findAll();
            usort($announcements, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
            $recentListings = array_slice($announcements, 0, 5);
            
        } catch (\Exception $e) {
            $totalUsers = 0;
            $activeUsers = 0;
            $activeListings = 0;
            $allAnnouncements = 0;
            $activeServices = 0;
            $allServices = 0;
            $totalReservations = 0;
            $pendingReservations = 0;
            $confirmedReservations = 0;
            $recentUsers = [];
            $recentListings = [];
        }
        
        return $this->render('dashboard/admin.html.twig', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'activeListings' => $activeListings,
            'allAnnouncements' => $allAnnouncements,
            'activeServices' => $activeServices,
            'allServices' => $allServices,
            'totalReservations' => $totalReservations,
            'pendingReservations' => $pendingReservations,
            'confirmedReservations' => $confirmedReservations,
            'recentUsers' => $recentUsers,
            'recentListings' => $recentListings,
        ]);
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
            $activeListings = count(array_filter($announcements, fn($a) => $a->getStatus() === 'active'));
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
}


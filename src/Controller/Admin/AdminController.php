<?php

namespace App\Controller\Admin;

use App\Repository\AnnouncementRepository;
use App\Repository\UserRepository;
use App\Repository\ServiceRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(
        UserRepository $userRepo,
        AnnouncementRepository $announcementRepo,
        ServiceRepository $serviceRepo,
        ReservationRepository $reservationRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get statistics
        $totalUsers = $userRepo->count([]);
        $activeUsers = $userRepo->count(['isActive' => true]);
        $allAnnouncements = $announcementRepo->count([]);
        $activeListings = $announcementRepo->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'available'])
            ->getQuery()
            ->getSingleScalarResult();
        $allServices = $serviceRepo->count([]);
        $activeServices = $serviceRepo->count(['isActive' => true]);
        $totalReservations = $reservationRepo->count([]);
        $pendingReservations = $reservationRepo->count(['status' => 'pending']);
        $confirmedReservations = $reservationRepo->count(['status' => 'confirmed']);

        // Get recent users (last 5)
        $recentUsers = $userRepo->findBy([], ['createdAt' => 'DESC'], 5);

        // Get recent listings (last 5)
        $recentListings = $announcementRepo->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'allAnnouncements' => $allAnnouncements,
            'activeListings' => $activeListings,
            'allServices' => $allServices,
            'activeServices' => $activeServices,
            'totalReservations' => $totalReservations,
            'pendingReservations' => $pendingReservations,
            'confirmedReservations' => $confirmedReservations,
            'recentUsers' => $recentUsers,
            'recentListings' => $recentListings,
        ]);
    }

    #[Route('/reservations', name: 'app_admin_reservations')]
    public function reservations(ReservationRepository $reservationRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $reservations = $reservationRepo->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/reservation/admin_index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/reports', name: 'app_admin_reports')]
    public function reports(
        UserRepository $userRepo,
        AnnouncementRepository $announcementRepo,
        ServiceRepository $serviceRepo,
        ReservationRepository $reservationRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Monthly statistics
        $currentMonth = new \DateTimeImmutable('first day of this month');
        $lastMonth = new \DateTimeImmutable('first day of last month');
        
        // Get all data for analysis
        $allUsers = $userRepo->findAll();
        $allAnnouncements = $announcementRepo->findAll();
        $allServices = $serviceRepo->findAll();
        $allReservations = $reservationRepo->findAll();

        // Calculate statistics
        $stats = [
            'users' => [
                'total' => count($allUsers),
                'active' => count(array_filter($allUsers, fn($u) => $u->isActive())),
                'sellers' => count(array_filter($allUsers, fn($u) => in_array('ROLE_SELLER', $u->getRoles()))),
                'admins' => count(array_filter($allUsers, fn($u) => in_array('ROLE_ADMIN', $u->getRoles()))),
            ],
            'announcements' => [
                'total' => count($allAnnouncements),
                'active' => count(array_filter($allAnnouncements, fn($a) => in_array($a->getStatus(), ['active', 'available']))),
                'sponsored' => count(array_filter($allAnnouncements, fn($a) => $a->isSponsored())),
            ],
            'services' => [
                'total' => count($allServices),
                'active' => count(array_filter($allServices, fn($s) => $s->isActive())),
            ],
            'reservations' => [
                'total' => count($allReservations),
                'pending' => count(array_filter($allReservations, fn($r) => $r->getStatus() === 'pending')),
                'confirmed' => count(array_filter($allReservations, fn($r) => $r->getStatus() === 'confirmed')),
                'cancelled' => count(array_filter($allReservations, fn($r) => $r->getStatus() === 'cancelled')),
            ],
        ];

        return $this->render('admin/reports.html.twig', [
            'stats' => $stats,
        ]);
    }
}

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

        $stats = [
            'users' => $userRepo->count([]),
            'announcements' => $announcementRepo->count([]),
            'services' => $serviceRepo->count([]),
            'reservations' => $reservationRepo->count([]),
            'active_announcements' => $announcementRepo->count(['status' => 'active']),
            'pending_reservations' => $reservationRepo->count(['status' => 'pending']),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }
}

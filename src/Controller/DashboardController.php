<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_dashboard_admin');
        }
        if ($this->isGranted('ROLE_SELLER')) {
            return $this->redirectToRoute('app_dashboard_seller');
        }
        if ($this->isGranted('ROLE_PROVIDER')) {
            return $this->redirectToRoute('app_dashboard_provider');
        }
        return $this->render('dashboard/client.html.twig');
    }

    #[Route('/dashboard/admin', name: 'app_dashboard_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(): Response
    {
        return $this->render('dashboard/admin.html.twig');
    }

    #[Route('/dashboard/seller', name: 'app_dashboard_seller')]
    #[IsGranted('ROLE_SELLER')]
    public function seller(): Response
    {
        return $this->render('dashboard/seller.html.twig');
    }

    #[Route('/dashboard/provider', name: 'app_dashboard_provider')]
    #[IsGranted('ROLE_PROVIDER')]
    public function provider(): Response
    {
        return $this->render('dashboard/provider.html.twig');
    }
}

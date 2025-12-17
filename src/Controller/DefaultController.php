<?php

namespace App\Controller;

use App\Repository\AnnouncementRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(): Response
    {
        return $this->render('default/landing.html.twig');
    }

    #[Route('/marketplace', name: 'app_marketplace')]
    public function marketplace(
        Request $request,
        AnnouncementRepository $announcementRepo,
        ServiceRepository $serviceRepo
    ): Response
    {
        $type = $request->query->get('type', 'all');
        $category = $request->query->get('category');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $search = $request->query->get('search');

        $announcementsQuery = $announcementRepo->createQueryBuilder('a')
            ->where('a.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'available']);

        if ($type !== 'services' && $category) {
            $announcementsQuery->join('a.categories', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $category);
        }

        if ($type !== 'services' && $minPrice) {
            $announcementsQuery->andWhere('a.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($type !== 'services' && $maxPrice) {
            $announcementsQuery->andWhere('a.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        if ($search) {
            $announcementsQuery->andWhere('a.title LIKE :search OR a.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $announcements = $type !== 'services' ? $announcementsQuery->getQuery()->getResult() : [];

        $servicesQuery = $serviceRepo->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true);

        if ($type !== 'listings' && $search) {
            $servicesQuery->andWhere('s.title LIKE :search OR s.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $services = $type !== 'listings' ? $servicesQuery->getQuery()->getResult() : [];

        return $this->render('default/marketplace.html.twig', [
            'announcements' => $announcements,
            'services' => $services,
            'type' => $type,
            'category' => $category,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'search' => $search,
        ]);
    }
}

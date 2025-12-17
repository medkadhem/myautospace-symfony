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
        AnnouncementRepository $announcementRepo
    ): Response
    {
        $category = $request->query->get('category');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $search = $request->query->get('search');

        $announcementsQuery = $announcementRepo->createQueryBuilder('a')
            ->where('a.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'available']);

        if ($category) {
            $announcementsQuery->join('a.categories', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $category);
        }

        if ($minPrice) {
            $announcementsQuery->andWhere('a.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $announcementsQuery->andWhere('a.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        if ($search) {
            $announcementsQuery->andWhere('a.title LIKE :search OR a.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $announcements = $announcementsQuery->getQuery()->getResult();

        return $this->render('default/marketplace.html.twig', [
            'announcements' => $announcements,
            'category' => $category,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'search' => $search,
        ]);
    }

    #[Route('/services', name: 'app_services')]
    public function services(
        Request $request,
        ServiceRepository $serviceRepo
    ): Response
    {
        $category = $request->query->get('category');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $search = $request->query->get('search');

        $servicesQuery = $serviceRepo->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true);

        if ($category) {
            $servicesQuery->andWhere('s.category = :categoryId')
                ->setParameter('categoryId', $category);
        }

        if ($minPrice) {
            $servicesQuery->andWhere('s.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $servicesQuery->andWhere('s.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        if ($search) {
            $servicesQuery->andWhere('s.title LIKE :search OR s.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $services = $servicesQuery->getQuery()->getResult();

        return $this->render('default/services.html.twig', [
            'services' => $services,
            'category' => $category,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'search' => $search,
        ]);
    }
}

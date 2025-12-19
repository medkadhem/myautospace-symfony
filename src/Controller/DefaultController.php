<?php

namespace App\Controller;

use App\Repository\AnnouncementRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(
        AnnouncementRepository $announcementRepo,
        ServiceRepository $serviceRepo,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Get featured/latest announcements
        $featuredAnnouncements = $announcementRepo->createQueryBuilder('a')
            ->where('a.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'available'])
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();

        // Get featured services
        $featuredServices = $serviceRepo->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        // Get statistics
        $totalAnnouncements = $announcementRepo->count([]);
        $activeAnnouncements = $announcementRepo->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'available'])
            ->getQuery()
            ->getSingleScalarResult();
        $totalServices = $serviceRepo->count(['isActive' => true]);
        
        // Get total users count
        $totalUsers = $entityManager->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\User', 'u')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Get providers count
        $totalProviders = $entityManager->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\User', 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_PROVIDER%')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Get completed reservations count
        $completedReservations = $entityManager->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from('App\Entity\Reservation', 'r')
            ->where('r.status = :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('default/landing.html.twig', [
            'featuredAnnouncements' => $featuredAnnouncements,
            'featuredServices' => $featuredServices,
            'totalAnnouncements' => $totalAnnouncements,
            'activeAnnouncements' => $activeAnnouncements,
            'totalServices' => $totalServices,
            'totalUsers' => $totalUsers,
            'totalProviders' => $totalProviders,
            'completedReservations' => $completedReservations,
        ]);
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

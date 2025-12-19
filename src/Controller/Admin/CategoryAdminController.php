<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryForm;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/categories')]
class CategoryAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $category = new Category();
        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category created successfully!');
            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Category updated successfully!');
            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            // Check if category has subcategories
            if ($category->getSubCategories()->count() > 0) {
                $this->addFlash('error', 'Cannot delete this category because it has ' . $category->getSubCategories()->count() . ' subcategories. Please delete or reassign the subcategories first.');
                return $this->redirectToRoute('app_admin_category_index');
            }

            // Check if category has services
            if ($category->getServices()->count() > 0) {
                $this->addFlash('error', 'Cannot delete this category because it has ' . $category->getServices()->count() . ' services associated with it. Please reassign or delete the services first.');
                return $this->redirectToRoute('app_admin_category_index');
            }

            // Check if category has announcements
            if ($category->getAnnouncements()->count() > 0) {
                $this->addFlash('error', 'Cannot delete this category because it has ' . $category->getAnnouncements()->count() . ' announcements associated with it. Please reassign or delete the announcements first.');
                return $this->redirectToRoute('app_admin_category_index');
            }

            try {
                $entityManager->remove($category);
                $entityManager->flush();
                $this->addFlash('success', 'Category deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while deleting the category: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_admin_category_index');
    }
}

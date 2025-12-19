<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryForm;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category')]
final class CategoryController extends AbstractController
{
    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryForm::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            // Check if category has subcategories
            if ($category->getSubCategories()->count() > 0) {
                $this->addFlash('error', 'Cannot delete this category because it has ' . $category->getSubCategories()->count() . ' subcategories. Please delete or reassign the subcategories first.');
                return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
            }

            // Check if category has services
            if ($category->getServices()->count() > 0) {
                $this->addFlash('error', 'Cannot delete this category because it has ' . $category->getServices()->count() . ' services associated with it. Please reassign or delete the services first.');
                return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
            }

            // Check if category has announcements
            if ($category->getAnnouncements()->count() > 0) {
                $this->addFlash('error', 'Cannot delete this category because it has ' . $category->getAnnouncements()->count() . ' announcements associated with it. Please reassign or delete the announcements first.');
                return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
            }

            try {
                $entityManager->remove($category);
                $entityManager->flush();
                $this->addFlash('success', 'Category deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while deleting the category. Please try again.');
            }
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
   #[Route('/category', name: 'app_category')]
public function index(UserRepository $userRepository, CategoryRepository $categoryRepository): Response
{
    
    $categories = $categoryRepository->findAll();

    return $this->render('category/index.html.twig', [
        'categories' => $categories,
    ]);
}

    #[Route('/admin/category/new', name: 'app_category_new')]
    public function addCategory(EntityManagerInterface $entityManager, Request $request): Response
    {   
        $category = new Category();
        $form = $this ->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() ){
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Votre catégorie à bien été créée');
        }

        return $this->render('category/newCategory.html.twig', [
            'formCategory' => $form->createView(),
        ]);
    }


    #[Route('/admin/category/{id}/edit', name: 'app_category_edit', requirements: ['id' => '\d+'])]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $entityManager): Response 
    {
    
       $form = $this->createForm(CategoryFormType::class, $category);
       $form->handleRequest($request);    
       if ($form->isSubmitted() && $form->isValid()) {        
        $entityManager->flush();
        $this->addFlash('success', 'Votre catégorie à bien été modifiée.');
        return $this->redirectToRoute('app_categories_list'); 
    }

    return $this->render('category/editCategory.html.twig', [
        'formCategory' => $form->createView(),
        'category' => $category
    ]);

    }

    #[Route('/admin/categories', name: 'app_categories_list')]
    public function listCategories(EntityManagerInterface $entityManager): Response
    {
    
    $categories = $entityManager->getRepository(Category::class)->findAll();

   
    return $this->render('category/list.html.twig', [
        'categories' => $categories,
    ]);

    }

    #[Route('/admin/category/{id}/delete', name: 'app_category_delete', requirements: ['id' => '\d+'])]
    public function deleteCategory(Category $category, EntityManagerInterface $entityManager): Response 
    {
    
      $entityManager->remove($category);
      $entityManager->flush();

      $this->addFlash('danger', 'Votre catégorie à bien été supprimée.');

    return $this->redirectToRoute('app_categories_list');

    }


}

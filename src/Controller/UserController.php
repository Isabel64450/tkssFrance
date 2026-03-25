<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
 
     public function profile(Security $security): Response
    {
    $user = $security->getUser();

    return $this->render('user/profile.html.twig', [
        'user' => $user,
    ]);
   }

    #[Route('/user/{id}/make-editor', name: 'app_user_make_editor', requirements: ['id' => '\d+'])]
    public function makeEditor(User $user, EntityManagerInterface $entityManager): Response
    {
        $roles = $user->getRoles();
        if (!in_array('ROLE_EDITOR', $roles)) {
            $roles[] = 'ROLE_EDITOR';
            $user->setRoles($roles);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Votre user à bien été modifiée à editor.');

        return $this->redirectToRoute('app_user_list');
    }

    
    #[Route('/admin/user/{id}/delete/editor/role', name: 'app_user_delete_editor_role')]
    public function deleteRoleEditor(EntityManagerInterface $entityManager, User $user): Response
    {
    $roles = $user->getRoles();    
    $roles = array_diff($roles, ['ROLE_EDITOR']);    
    $roles = array_diff($roles, ['ROLE_USER']);
    $user->setRoles($roles);
    $entityManager->flush();
    $this->addFlash('danger', "Le rôle éditeur a bien été retiré à l'utilisateur");
    return $this->redirectToRoute('app_user_list');
    }



    #[Route('/users', name: 'app_user_list')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
    
    $users = $entityManager->getRepository(User::class)->findAll();
   
    return $this->render('user/list.html.twig', [
        'users' => $users,
    ]);
    }

    #[Route('/admin/user/{id}/remove/', name: 'app_user_remove',requirements: ['id' => '\d+'])]
    public function deleteUser(User $user ,EntityManagerInterface $entityManager): Response
    {        
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('danger', "L'utilisateur à bien été supprimé.");
        
        return $this->redirectToRoute('app_user_list');
    }



}
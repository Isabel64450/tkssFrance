<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user/{id}/assign-role', name: 'app_user_assign_role', methods: ['GET','POST'])]
    public function assignRole(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $gender = $user->getGender();

        $rolesFemale = ['ROLE_MISKY', 'ROLE_KALINCHA', 'ROLE_MAMALA'];
        $rolesMale = ['ROLE_CHASCAS', 'ROLE_ROMPES', 'ROLE_PACHAS', 'ROLE_MACHUS'];
        $availableRoles = $gender === 'female' ? $rolesFemale : $rolesMale;

        // POST → traitement du formulaire
        if ($request->isMethod('POST')) {
            $role = $request->request->get('role');

            if (!$role) {
                $this->addFlash('error', 'Aucun rôle fourni');
                return $this->redirectToRoute('app_user_assign_role', ['id' => $user->getId()]);
            }

            if (!in_array($role, $availableRoles)) {
                $this->addFlash('error', 'Rôle invalide pour ce genre');
                return $this->redirectToRoute('app_user_assign_role', ['id' => $user->getId()]);
            }

            $user->setRoles([$role]);
            $em->flush();

            $this->addFlash('success', 'Rôle mis à jour');
            return $this->redirectToRoute('app_user_list');
        }

        // GET → affichage du formulaire
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'availableRoles' => $availableRoles,
        ]);
    }


    #[Route('/profile', name: 'app_profile')]
 
     public function profile(Security $security): Response
    {
     $user = $security->getUser();     
    $age = null;

    
    if ($user instanceof \App\Entity\User && $user->getDateOfBirth()) {
        $today = new \DateTimeImmutable('today');
        $birthDate = $user->getDateOfBirth(); 
        $age = $birthDate->diff($today)->y; 
    }

    return $this->render('user/profile.html.twig', [
        'user' => $user,
        'age' => $age, 
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
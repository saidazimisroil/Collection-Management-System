<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserStatusEnum;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();
        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/add/{id<\d+>}', name: 'app_admin_add')]
    public function addAdmin(int $id): Response
    {
        $user = $this->em->getRepository(User::class)->find($id);
        $user->setRoles(['ROLE_ADMIN']);
        
        $this->em->persist($user);
        $this->em->flush();

        return $this->redirectToRoute('app_admin');
    }
    
    #[Route('/admin/remove/{id<\d+>}', name: 'app_admin_remove')]
    public function removeAdmin(int $id): Response
    {
        $user = $this->em->getRepository(User::class)->find($id);
        $user->setRoles([]);
        
        $this->em->persist($user);
        $this->em->flush();

        return $this->redirectToRoute('app_admin');
    }
    
    #[Route('/users/delete/{id<\d+>}', name: 'app_users_delete_one')]
    public function deleteOne(int $id, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('app_admin');
        }
    
        try {
            $em->remove($user);
            $em->flush();
        } catch (\Exception $e) {
            // Log the exception or handle it accordingly
            $this->addFlash('error', 'Could not delete user.');
            return $this->redirectToRoute('app_admin');
        }
    
        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/users/block/{id<\d+>}', name: 'app_users_block_one')]
    public function blockOne(int $id, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_admin');
        }
    
        try {
            $user->setStatus('blocked');
            $em->flush();
        } catch (\Exception $e) {
            // Log the exception or handle it accordingly
            $this->addFlash('error', 'Could not block user.');
            return $this->redirectToRoute('app_admin');
        }
    
        $this->addFlash('success', 'User blocked successfully.');
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/users/unblock/{id<\d+>}', name: 'app_users_unblock_one')]
    public function unblockOne(int $id, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_admin');
        }
    
        try {
            $user->setStatus('active');
            $em->flush();
        } catch (\Exception $e) {
            // Log the exception or handle it accordingly
            $this->addFlash('error', 'Could not unblock user.');
            return $this->redirectToRoute('app_admin');
        }
    
        $this->addFlash('success', 'User unblocked successfully.');
        return $this->redirectToRoute('app_admin');
    }
}

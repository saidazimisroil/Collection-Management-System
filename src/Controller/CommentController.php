<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Item;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class CommentController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}
    
    #[Route('/comment/{id<\d+>}', name: 'app_comment')]
    public function index(int $id, Request $request): Response
    {
        // Trim trailing whitespace and newline characters from the content
        $content = $request->query->get('content');
        $trimmedContent = rtrim($content);
        if (empty($trimmedContent)) {
            return $this->redirectToRoute('app_item_show', [
                'id' => $id,
                'error' => "You can't post an empty comment!"
            ]);
        }


        $currentUser = $this->security->getUser();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $currentUser->getUserIdentifier()]);
    
        $item = $this->em->getRepository(Item::class)->find($id);
    
    
        $comment = new Comment();
        $comment->setContent($trimmedContent);
        $comment->setUser($user);
        $comment->setItem($item);
    
        $this->em->persist($comment);
        $this->em->flush();
    
        return $this->redirectToRoute('app_item_show', ['id' => $id]);
    }    
}

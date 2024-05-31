<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Like;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/like')]
class LikeController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    #[Route('/{id<\d+>}', name: 'app_like')]
    public function index(int $id): Response
    {
        $item = $this->em->getRepository(Item::class)->find($id);
        $currentUser = $this->security->getUser();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $currentUser->getUserIdentifier()]);

        // Check if the like already exists
        $likeRepository = $this->em->getRepository(Like::class);
        $existingLike = $likeRepository->createQueryBuilder('l')
            ->innerJoin('l.users', 'u')
            ->where('l.Item = :item')
            ->andWhere('u.id = :user')
            ->setParameter('item', $item)
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingLike) {
            // If the like already exists, remove the user from the like
            $existingLike->removeUser($currentUser);

            if ($existingLike->getUsers()->isEmpty()) {
                $this->em->remove($existingLike);
            } else {
                $this->em->persist($existingLike);
            }

            $this->em->flush();

            $this->addFlash('success', 'Like removed successfully.');
        } else {
            if($item->getLikes()){
                $like = $item->getLikes();
            } else {
                $like = new Like();
                $like->setItem($item);
            }            
            
            $like->addUser($user);

            $this->em->persist($like);
            $this->em->flush();

            $this->addFlash('success', 'Item liked successfully.');
        }

        $collection = $item->getItemCollection();
        return $this->redirectToRoute('app_item_show', ['id' => $id]);
    }
}

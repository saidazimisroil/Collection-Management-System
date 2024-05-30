<?php

namespace App\Controller;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    #[Route('/search', name: 'app_search', methods:['GET'])]
    public function index(Request $request): Response
    {
        $prompt = $request->query->get('prompt');

        if (!$prompt) {
            return $this->render('search/index.html.twig', [
                'results_items' => [],
                'prompt' => '',
                'tags' => $this->em->getRepository(Tag::class)->getExistingTagNames()
            ]);
        }

        // Perform search by name in Item and content in Comment
        $itemQuery = $this->em->createQuery(
            'SELECT i 
            FROM App\Entity\Item i 
            WHERE i.name LIKE :prompt'
        )->setParameter('prompt', '%' . $prompt . '%');

        $commentQuery = $this->em->createQuery(
            'SELECT c 
            FROM App\Entity\Comment c 
            WHERE c.content LIKE :prompt'
        )->setParameter('prompt', '%' . $prompt . '%');
        
        $tagQuery = $this->em->createQuery(
            'SELECT t 
            FROM App\Entity\Tag t 
            WHERE t.name LIKE :prompt'
        )->setParameter('prompt', '%' . $prompt . '%');

        $items = $itemQuery->getResult();
        $comments = $commentQuery->getResult();
        $tags = $tagQuery->getResult();

        // Collect unique items from comments
        $commentItems = [];
        foreach ($comments as $comment) {
            $commentItems[] = $comment->getItem();
        }

        // Collect unique items from tags
        $tagItems = [];
        foreach ($tags as $tag) {
            $tagItems = array_unique(array_merge($tagItems, $tag->getItems()->toArray()), SORT_REGULAR);
        }

        // Merge and remove duplicate items
        $result_items = array_unique(array_merge($items, $commentItems, $tagItems), SORT_REGULAR);

        return $this->render('search/index.html.twig', [
            'results_items' => $result_items,
            'prompt' => $prompt,
            'tags' => $this->em->getRepository(Tag::class)->getExistingTagNames()
        ]);
    }
}

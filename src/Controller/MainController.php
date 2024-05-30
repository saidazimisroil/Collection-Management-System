<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\ItemCollection;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    #[Route('/main', name: 'app_main')]
    public function index(): Response
    {
        $latestItems = $this->em->getRepository(Item::class)->findBy([],  ['id' => 'DESC'], 8);
        
        // Fetch all collections
        $collections = $this->em->getRepository(ItemCollection::class)->findAll();
        
        // Sort collections by the number of items in descending order
        usort($collections, function($a, $b) {
            return count($b->getItem()) <=> count($a->getItem());
        });
        
        // Get the top 5 largest collections
        $topLargestCollections = array_slice($collections, 0, 5);

        return $this->render('main/index.html.twig', [
            'items' => $latestItems,
            'collections' => $topLargestCollections,
            'tags' => $this->em->getRepository(Tag::class)->getExistingTagNames(),
            'prompt' => ''
        ]);
    }
}

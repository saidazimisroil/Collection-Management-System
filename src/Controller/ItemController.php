<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\ItemCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ItemController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    #[Route('/item', name: 'app_item')]
    public function index(): Response
    {
        return $this->render('item/index.html.twig', [
            'controller_name' => 'ItemController',
        ]);
    }

    #[Route('/item/new/{id<\d+>}', name: 'app_item_new', methods:['GET'])]
    public function new(int $id): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);

        return $this->render('item/new_item.html.twig', [
            'collection' => $collection,
        ]);
    }
    
    #[Route('/item/new/{id<\d+>}', name: 'app_item_save', methods:['POST'])]
    public function save(int $id, Request $request): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);
    
        if (!$collection) {
            throw $this->createNotFoundException('The collection does not exist');
        }
    
        $itemName = $request->request->get('name');
    
        $item = new Item();
        $item->setName($itemName);
        $item->setItemCollection($collection);
        $item->setTags("Tags");
    
        $this->em->persist($item);
        $this->em->flush();
    
        return $this->redirectToRoute('app_collection', ['id' => $id]);
    }    
}

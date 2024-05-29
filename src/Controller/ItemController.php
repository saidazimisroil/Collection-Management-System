<?php

namespace App\Controller;

use App\Entity\BoolField;
use App\Entity\DateField;
use App\Entity\Integer;
use App\Entity\Item;
use App\Entity\ItemCollection;
use App\Entity\StringField;
use App\Entity\TextField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/item')]
class ItemController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    #[Route('/show/{id<\d+>}', name: 'app_item_show', methods:['GET'])]
    public function show(int $id): Response
    {
        $item = $this->em->getRepository(Item::class)->find($id);

        return $this->render('item/show_item.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/new/{id<\d+>}', name: 'app_item_new', methods:['GET'])]
    public function new(int $id): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);

        return $this->render('item/new_item.html.twig', [
            'collection' => $collection,
            'error' => '',
        ]);
    }
    
    #[Route('/new/{id<\d+>}', name: 'app_item_save', methods:['POST'])]
    public function save(int $id, Request $request): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);
    
        if (!$collection) {
            throw $this->createNotFoundException('The collection does not exist');
        }
    
        $itemName = $request->request->get('name');
        if(!$this->isItemNameUnique($itemName, $collection)){
            return $this->render('item/new_item.html.twig', [
                'collection' => $collection,
                'error' => "You already have created an item with the name '$itemName' in this collection",
            ]);
        }

        $item = new Item();
        $item->setName($itemName);
        $item->setItemCollection($collection);
        $item->setTags("Tags");

        $intNames = $collection->getIntegers();
        foreach ($intNames as $index =>$intName) {
            $name = strtolower($intName);
            if ($name) {
                $intValue = $request->request->get('int' . ++$index);
                
                $int = new Integer();
                $int->setName($name);
                $int->setValue($intValue);
                
                $item->addInteger($int);

                $this->em->persist($int);
            }
        }

        $stringNames = $collection->getStrings();
        foreach ($stringNames as $index =>$stringName) {
            $name = strtolower($stringName);
            if ($name) {
                $stringValue = $request->request->get('string' . ++$index);
                
                $string = new StringField();
                $string->setName($name);
                $string->setValue($stringValue);
                
                $item->addStringField($string);

                $this->em->persist($string);
            }
        }

        $textNames = $collection->getTexts();
        foreach ($textNames as $index =>$textName) {
            $name = strtolower($textName);
            if ($name) {
                $textValue = $request->request->get('text' . ++$index);
                
                $text = new TextField();
                $text->setName($name);
                $text->setValue(rtrim($textValue));
                
                $item->addTextField($text);

                $this->em->persist($text);
            }
        }

        $boolNames = $collection->getBools();
        foreach ($boolNames as $index =>$boolName) {
            $name = strtolower($boolName);
            if ($name) {
                $boolValue = $request->request->has('bool' . ++$index) ? true : false;
                
                $bool = new BoolField();
                $bool->setName($name);
                $bool->setValue($boolValue);
                
                $item->addBoolField($bool);

                $this->em->persist($bool);
            }
        }

        $dateNames = $collection->getDates();
        foreach ($dateNames as $index =>$dateName) {
            $name = strtolower($dateName);
            if ($name) {
                $dateValue = $request->request->get('date' . ($index + 1));
                
                $date = new DateField();
                $date->setName($name);
                $date->setValue(new \DateTimeImmutable($dateValue));
                
                $item->addDateField($date);

                $this->em->persist($date);
            }
        }
    
        $this->em->persist($item);
        $this->em->flush();
    
        return $this->redirectToRoute('app_collection', ['id' => $id]);
    }
    private function isItemNameUnique($name, $collection) : bool
    {
        $items = $collection->getItem();
        if (!$items) {
            return true;
        }
        
        foreach ($items as $item) {
            if (strtolower($item->getName()) === strtolower($name)) {
                return false;
            }
        }
        return true;
    }

    #[Route('/edit/{id<\d+>}', name: 'app_item_edit', methods:['GET'])]
    public function edit(int $id): Response
    {
        $item = $this->em->getRepository(Item::class)->find($id);

        $collection = $item->getItemCollection();
        $currentUser = $this->security->getUser();
        if($currentUser !== $collection->getUser()){
            return $this->redirectToRoute('app_collections_my');
        }
        
        return $this->render('item/update_item.html.twig', [
            'item' => $item,
            'collection' => $collection,
        ]);
    }

    #[Route('/edit/{id<\d+>}', name: 'app_item_update', methods:['POST'])]
    public function saveChanges(int $id, Request $request): Response
    {
        $item = $this->em->getRepository(Item::class)->find($id);

        $collection = $item->getItemCollection();
        $currentUser = $this->security->getUser();
        if($currentUser !== $collection->getUser()){
            return $this->redirectToRoute('app_collections_my');
        }

        $item->setName($request->request->get('name'));

        $this->em->persist($item);
        $this->em->flush();

        return $this->redirectToRoute('app_collection', ['id' => $collection->getId()]);
    }

    #[Route('/delete/{id<\d+>}', name: 'app_item_delete', methods:['GET'])]
    public function delete(int $id): Response
    {
        $item = $this->em->getRepository(Item::class)->find($id);

        $collection = $item->getItemCollection();
        $currentUser = $this->security->getUser();
        if($currentUser !== $collection->getUser()){
            return $this->redirectToRoute('app_collections_my');
        }

        $this->em->remove($item);
        $this->em->flush();

        return $this->redirectToRoute('app_collection', ['id' => $collection->getId()]);
    }
}

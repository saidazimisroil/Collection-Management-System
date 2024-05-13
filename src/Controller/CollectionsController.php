<?php

namespace App\Controller;

use App\Entity\CategoriesEnum;
use App\Entity\ItemCollection;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collections')]
class CollectionsController extends AbstractController
{
    private $createRouteName = 'app_create_collection';

    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'app_collections_index')]
    public function index(): Response
    {
        // all collections
        $collections = $this->em->getRepository(ItemCollection::class)->findAll();

        return $this->render('collections/index.html.twig', [
            'collections' => $collections,
            'createRouteName' => $this->createRouteName,
        ]);
    }

    #[Route('/me', name: 'app_collections_my')]
    public function getMyCollections(): Response
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login'); // Redirect to login if not authenticated
        }

        // Get the current user data
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $currentUser->getUserIdentifier()]);

        // Assuming getItemCollectionId is a method that returns the collections
        $collections = $this->em->getRepository(ItemCollection::class)->findBy(['user' => $user->getId()]);

        return $this->render('collections/my_collections.html.twig', [
            'userEmail' => $currentUser->eraseCredentials(),
            'collections' => $collections,
            'createRouteName' => $this->createRouteName,
        ]);
    }

    #[Route('/create', name: 'app_create_collection', methods:['GET'])]
    public function create(): Response
    {
        return $this->render('collections/create_collection.html.twig');
    }
    
    #[Route('/create', name: 'app_store_collection', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to create a collection.');
            return $this->redirectToRoute('app_login');
        }

        $collection = new ItemCollection();
        $collection->setName($request->request->get('name'));
        $collection->setDescription($request->request->get('description'));
        $collection->setCategory($request->request->get('category'));
        $collection->setUser($user);

        $this->em->persist($collection);
        $this->em->flush();

        $this->addFlash('success', 'Collection created successfully!');
        return $this->redirectToRoute('app_collections_my');
    }
    
    #[Route('/{id<\d+>}', name: 'app_collection')]
    public function getCollection(int $id): Response
    {
        // chosen collection
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);
        $items = $collection->getAllItems();

        return $this->render('collections/collection.html.twig', [
            'collection' => $collection,
            'createRouteName' => $this->createRouteName,
            'items' => $items,
        ]);
    }

    #[Route('/edit/{id<\d+>}', methods:['GET'], name: 'app_collection_update_form')]
    public function getEditForm(int $id): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);
        if (!$collection) {
            throw $this->createNotFoundException('The collection does not exist');
        }
        
        $currentCategory = $collection->getCategory(); // Assuming getCategory() method returns a CategoriesEnum object

        // Get all enum cases
        $categories = CategoriesEnum::cases();

        // Filter out the current category and get the remaining categories
        $sortedCategories = array_filter($categories, function ($category) use ($currentCategory) {
            return $category->value !== $currentCategory;
        });

        // Convert enum cases to values suitable for rendering
        $categoryValues = array_map(function ($category) {
            return $category->value;
        }, $sortedCategories);

        // Prepend the current category's value for display
        array_unshift($categoryValues, $currentCategory);

        return $this->render('collections/update_collection.html.twig', [
            'collection' => $collection,
            'categories' => $categoryValues,
        ]);
    }

    #[Route('/edit/{id<\d+>}', methods:['POST'], name: 'app_collection_update')]
    public function update(int $id, Request $request): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);

        $collection->setName($request->request->get('name'));
        $collection->setDescription($request->request->get('description'));
        $collection->setCategory($request->request->get('category'));

        $this->em->persist($collection);
        $this->em->flush();

        $this->addFlash('success', 'Collection created successfully!');
        return $this->redirectToRoute('app_collection', ['id' => $id]);
    }

    #[Route('/delete/{id<\d+>}', name: 'app_collection_delete', methods:['GET'])]
    public function deleteCollection(int $id): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);

        $this->em->remove($collection);
        $this->em->flush();

        return $this->redirectToRoute('app_collections_my');
    }
}

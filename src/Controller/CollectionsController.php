<?php

namespace App\Controller;

use App\Entity\CategoriesEnum;
use App\Entity\ItemCollection;
use App\Entity\Tag;
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
            'tags' => $this->em->getRepository(Tag::class)->getExistingTagNames()
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
            'tags' => $this->em->getRepository(Tag::class)->getExistingTagNames()
        ]);
    }

    #[Route('/me/create', name: 'app_create_collection', methods:['GET'])]
    public function create(): Response
    {
        return $this->render('collections/create_collection.html.twig', [
            'error' => '',
        ]);
    }
    
    #[Route('/me/create', name: 'app_store_collection', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $user = $this->security->getUser();

        $name = $request->request->get('name');

        if(!$this->isCollectionNameUnique($name)) {
            return $this->render('collections/create_collection.html.twig', [
                'error' => 'You already have a collection with name: ' . $name,
            ]);
        }

        $collection = new ItemCollection();
        $collection->setName($name);
        $collection->setDescription(rtrim($request->request->get('description')));
        $collection->setCategory($request->request->get('category'));
        $collection->setUser($user);

        // Custom fields
        // Integers
        $int1 = $request->request->get('integerField1') ? $request->request->get('integerField1') : null;
        $int2 = $request->request->get('integerField2') ? $request->request->get('integerField2') : null;
        $int3 = $request->request->get('integerField3') ? $request->request->get('integerField3') : null;
        $collection->setIntegers([$int1, $int2, $int3]);

        // Strings
        $string1 = $request->request->get('stringField1') ? $request->request->get('stringField1') : null;
        $string2 = $request->request->get('stringField2') ? $request->request->get('stringField2') : null;
        $string3 = $request->request->get('stringField3') ? $request->request->get('stringField3') : null;
        $collection->setStrings([$string1, $string2, $string3]);

        // Texts
        $text1 = $request->request->get('textField1') ? $request->request->get('textField1') : null;
        $text2 = $request->request->get('textField2') ? $request->request->get('textField2') : null;
        $text3 = $request->request->get('textField3') ? $request->request->get('textField3') : null;
        $collection->setTexts([$text1, $text2, $text3]);

        // Booleans
        $bool1 = $request->request->get('boolField1') ? $request->request->get('boolField1') : null;
        $bool2 = $request->request->get('boolField2') ? $request->request->get('boolField2') : null;
        $bool3 = $request->request->get('boolField3') ? $request->request->get('boolField3') : null;
        $collection->setBools([$bool1, $bool2, $bool3]);

        // Dates
        $date1 = $request->request->get('dateField1') ? $request->request->get('dateField1') : null;
        $date2 = $request->request->get('dateField2') ? $request->request->get('dateField2') : null;
        $date3 = $request->request->get('dateField3') ? $request->request->get('dateField3') : null;
        $collection->setDates([$date1, $date2, $date3]);

        $this->em->persist($collection);
        $this->em->flush();

        $this->addFlash('success', 'Collection created successfully!');
        return $this->redirectToRoute('app_collections_my');
    }
    private function isCollectionNameUnique($name) : bool
    {
        $currentUser = $this->security->getUser();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $currentUser->getUserIdentifier()]);

        $collections = $this->em->getRepository(ItemCollection::class)->findBy(['user' => $user->getId()]);
        if (!$collections) {
            return true;
        }
        
        foreach ($collections as $collection) {
            if ($collection->getName() === $name) {
                return false;
            }
        }
        return true;
    }
    
    #[Route('/{id<\d+>}', name: 'app_collection')]
    public function getCollection(int $id): Response
    {
        // chosen collection
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);

        return $this->render('collections/collection.html.twig', [
            'collection' => $collection,
            'tags' => $this->em->getRepository(Tag::class)->getExistingTagNames()
        ]);
    }

    #[Route('/edit/{id<\d+>}', methods:['GET'], name: 'app_collection_update_form')]
    public function getEditForm(int $id): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);
        if (!$collection) {
            throw $this->createNotFoundException('The collection does not exist');
        }

        $currentUser = $this->security->getUser();
        if($currentUser !== $collection->getUser()){
            return $this->redirectToRoute('app_collections_my');
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

        $customFields = $this->getCustomFields($collection);

        return $this->render('collections/update_collection.html.twig', [
            'collection' => $collection,
            'categories' => $categoryValues,
            'customFields' => $customFields
        ]);
    }
    private function getCustomFields($collection) : array
    {
        $ints = $collection->getIntegers();
        $strs = $collection->getStrings();
        $txts = $collection->getTexts();
        $bools = $collection->getBools();
        $dates = $collection->getDates();

        return array(
            'integer' => $ints, 
            'string' => $strs, 
            'text' => $txts, 
            'boolean' => $bools, 
            'date' => $dates
        );
    }

    #[Route('/edit/{id<\d+>}', methods:['POST'], name: 'app_collection_update')]
    public function update(int $id, Request $request): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);

        $collection->setName($request->request->get('name'));
        $collection->setDescription($request->request->get('description'));
        $collection->setCategory($request->request->get('category'));
        $collection->setIntegers([$request->request->get('integerField1'), $request->request->get('integerField2'), $request->request->get('integerField3')]);
        $collection->setStrings([$request->request->get('stringField1'), $request->request->get('stringField2'), $request->request->get('stringField3')]);
        $collection->setTexts([$request->request->get('textField1'), $request->request->get('textField2'), $request->request->get('textField3')]);
        $collection->setBools([$request->request->get('booleanField1'), $request->request->get('booleanField2'), $request->request->get('booleanField3')]);
        $collection->setDates([$request->request->get('dateField1'), $request->request->get('dateField2'), $request->request->get('dateField3')]);

        $this->em->persist($collection);
        $this->em->flush();

        $this->addFlash('success', 'Collection created successfully!');
        return $this->redirectToRoute('app_collection', ['id' => $id]);
    }

    #[Route('/delete/{id<\d+>}', name: 'app_collection_delete', methods:['GET'])]
    public function deleteCollection(int $id): Response
    {
        $collection = $this->em->getRepository(ItemCollection::class)->find($id);

        $currentUser = $this->security->getUser();
        if($currentUser !== $collection->getUser()){
            return $this->redirectToRoute('app_collections_my');
        }

        $this->em->remove($collection);
        $this->em->flush();

        return $this->redirectToRoute('app_collections_my');
    }
}

<?php

namespace App\Controller;

use App\Entity\Item;
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

        return $this->render('main/index.html.twig', [
            'items' => $latestItems,
        ]);
    }
}

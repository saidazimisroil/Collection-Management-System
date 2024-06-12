<?php
namespace App\Controller;

use App\Form\TicketType;
use App\Service\JiraService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{
    private $jiraService;

    public function __construct(JiraService $jiraService)
    {
        $this->jiraService = $jiraService;
    }

    /**
     * @Route("/create-ticket", name="create_ticket")
     */
    #[Route("/create-ticket", name:"app_ticket_create")]
    public function createTicket(Request $request): Response
    {
        $link = $request->query->get('link', ''); // Get the 'link' parameter from the query string

        $form = $this->createForm(TicketType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $reporter = $this->getUser()->getUserIdentifier();

            $response = $this->jiraService->createTicket(
                $data['summary'],
                $data['priority'],
                $reporter,
                $data['collection'],
                $link // Pass the link to the JiraService
            );

            // Redirect or display the response as needed
            return $this->redirectToRoute('app_ticket_success');
        }

        return $this->render('ticket/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/ticket-success", name:"app_ticket_success")]
    public function successTicket(): Response
    {
        return $this->render('ticket/success.html.twig');
    }
}

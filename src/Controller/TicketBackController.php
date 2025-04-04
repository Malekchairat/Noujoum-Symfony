<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType; // Import the correct TicketType class.
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TicketBackController extends AbstractController
{
    #[Route('/ticket-back', name: 'ticket_back')]
    public function index(Request $request, TicketRepository $ticketRepository): Response
    {
        // Retrieve the sorting and search parameters from the query string.
        $sort = $request->query->get('sort', 'evenement'); // Default sort by event
        $search = $request->query->get('search', '');

        // Build a query with joins on the event and user relations.
        $qb = $ticketRepository->createQueryBuilder('t')
            ->leftJoin('t.evenement', 'e') // Join with the evenement table
            ->leftJoin('t.utilisateur', 'u'); // Join with the utilisateur table

        // If there's a search term, filter by event title or user name.
        if (!empty($search)) {
            $qb->andWhere('e.titre LIKE :search OR u.username LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Order by the chosen sorting parameter.
        if ($sort === 'utilisateur') {
            $qb->orderBy('u.username', 'ASC');
        } else {
            $qb->orderBy('e.titre', 'ASC');
        }

        $tickets = $qb->getQuery()->getResult();

        return $this->render('ticket/ticketback.html.twig', [
            'tickets' => $tickets,
            'sort' => $sort,
            'search' => $search,
        ]);
    }

    #[Route('/ticket-back/edit/{id}', name: 'ticket_edit')]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Ticket modifié avec succès.');
            return $this->redirectToRoute('ticket_back');
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form'   => $form->createView(),
        ]);
    }

    // The delete route accepts POST and DELETE methods.
    #[Route('/ticket-back/delete/{id}', name: 'ticket_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {
            $em->remove($ticket);
            $em->flush();
            $this->addFlash('success', 'Ticket supprimé avec succès.');
        }
        return $this->redirectToRoute('ticket_back');
    }
}

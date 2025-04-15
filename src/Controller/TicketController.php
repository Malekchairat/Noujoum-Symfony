<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Evenement;
use App\Entity\User;
use App\Form\TicketType;
use App\Service\QrCodeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/ticket')]
class TicketController extends AbstractController
{
    private QrCodeGenerator $qrCodeGenerator;

    public function __construct(QrCodeGenerator $qrCodeGenerator)
    {
        $this->qrCodeGenerator = $qrCodeGenerator;
    }

    #[Route('/new/{id}', name: 'ticket_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        // Retrieve the event
        $event = $entityManager->getRepository(Evenement::class)->find($id);
        if (!$event) {
            return new JsonResponse(['status' => 'error', 'errors' => 'Événement non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Use the user with id=1 (temporary)
        $user = $entityManager->getRepository(User::class)->find(1);
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'errors' => 'User with id 1 not found.'], Response::HTTP_NOT_FOUND);
        }

        // Create a new Ticket and bind to form
        $ticket = new Ticket();
        $ticket->setEvenement($event);
        $ticket->setUtilisateur($user);

        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantite = $ticket->getQuantite();
            $prix = $event->getPrix();
            $ticket->setTotal($quantite * $prix);

            // First, persist the ticket without the QR code so that an ID is generated
            try {
                $entityManager->persist($ticket);
                $entityManager->flush();
            } catch (\Exception $e) {
                return new JsonResponse([
                    'status' => 'error',
                    'errors' => 'Database error: ' . $e->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Now that the ticket has an ID, generate the QR code content
            $ticketId = $ticket->getId();
            // Assuming your Evenement entity has a getTitre() method
            $eventTitle = $event->getTitre();
            // UPDATED: New User entity has no getUsername(), combine getNom() and getPrenom() instead.
            $username = $user->getNom() . ' ' . $user->getPrenom();

            // Create the content to be encoded in the QR code
            $qrContent = sprintf("Ticket ID: %d\nEvent: %s\nUser: %s", $ticketId, $eventTitle, $username);

            // Define the directory where the QR code will be saved
            $qrDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/qrcodes';
            if (!is_dir($qrDirectory)) {
                mkdir($qrDirectory, 0755, true);
            }

            // Create a unique file name for the QR code image
            $qrFilename = 'ticket_' . uniqid() . '.png';
            $qrFilePath = $qrDirectory . '/' . $qrFilename;

            // Generate and save the QR code using the QrCodeGenerator service
            $this->qrCodeGenerator->generateAndSave($qrContent, $qrFilePath);

            // Set the relative path in the Ticket entity and update the record
            $ticket->setQrCode('/uploads/qrcodes/' . $qrFilename);
            $entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'Réservation réussie !']);
        }

        return new JsonResponse(['status' => 'error', 'errors' => 'Form non soumis ou invalide.'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/mes', name: 'mes_tickets', methods: ['GET'])]
    public function mesTickets(EntityManagerInterface $entityManager): Response
    {
        // For now, use the user with id = 1
        $user = $entityManager->getRepository(User::class)->find(1);
        if (!$user) {
            throw $this->createNotFoundException('User with id 1 not found.');
        }

        // UPDATED: Since the new User entity does not have a "tickets" property,
        // we query for tickets based on the "utilisateur" field.
        $tickets = $entityManager->getRepository(Ticket::class)->findBy(['utilisateur' => $user]);

        return $this->render('ticket/MesTickets.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/pdf/{id}', name: 'ticket_pdf', methods: ['GET'])]
    public function ticketPdf(EntityManagerInterface $entityManager, int $id): Response
    {
        $ticket = $entityManager->getRepository(Ticket::class)->find($id);
        if (!$ticket) {
            throw $this->createNotFoundException('Ticket non trouvé.');
        }

        // Render a Twig template for the PDF content
        $html = $this->renderView('ticket/ticket_pdf.html.twig', [
            'ticket' => $ticket,
        ]);

        // Set Dompdf options if needed
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output the generated PDF (force download)
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ticket_' . $ticket->getId() . '.pdf"'
        ]);
    }
}
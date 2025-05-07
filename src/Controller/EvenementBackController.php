<<<<<<< HEAD
<?php
=======
<?php 
>>>>>>> origin/GestionCommandes
namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
<<<<<<< HEAD
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GoogleCalendarService;
=======
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
>>>>>>> origin/GestionCommandes

#[Route('/evenement')]
class EvenementBackController extends AbstractController
{
<<<<<<< HEAD

    private GoogleCalendarService $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    #[Route('/EvenementBack', name: 'evenement_EvenementBack')]
    public function index(EntityManagerInterface $em): Response
    {
        $now = new \DateTime();
        $evenements = $em->getRepository(Evenement::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.tickets', 't')->addSelect('t')
            ->where('e.dateFin >= :now')->setParameter('now', $now)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()->getResult();

        foreach ($evenements as $ev) {
            $ev->daysUntilStart = $now->diff($ev->getDateDebut())->days;
        }

        return $this->render('evenement/evenementback.html.twig', [
            'evenements' => $evenements,
            'maxTickets' => 500,
        ]);
    }

    #[Route('/new', name: 'evenement_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // 1) Validation dateDebut/dateFin + image upload…
            $dateDebut = $evenement->getDateDebut();
            $dateFin   = $evenement->getDateFin();
            if ($dateDebut && $dateFin && $dateDebut > $dateFin) {
                $form->get('dateDebut')->addError(new FormError('La date de début ne peut pas être après la date de fin.'));
            } else {
                // 2) Handle image upload (if any)
                if ($img = $form->get('image')->getData()) {
                    $newFilename = uniqid().'.'.$img->guessExtension();
                    $img->move($this->getParameter('uploads_directory'), $newFilename);
                    $evenement->setImage($newFilename);
                }
    
                // 3) Persist the Evenement entity
                $em->persist($evenement);
                $em->flush();
    
                // 4) Synchronization with Google Calendar
                $googleId = $this->calendarService->createEvent($evenement); // Create event on Google Calendar
                $evenement->setGoogleEventId($googleId); // Set the Google event ID in the Evenement entity
                $em->flush(); // Update the database with the Google event ID
    
                // 5) Display success message
                $this->addFlash('success', 'Événement créé et synchronisé avec Google Calendar.');
    
                // 6) Redirect to another route after successful creation
                return $this->redirectToRoute('evenement_EvenementBack');
            }
        }
    
        return $this->render('evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'evenement_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
=======
    #[Route('/EvenementBack', name: 'evenement_EvenementBack')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $evenements = $entityManager->getRepository(Evenement::class)->findAll();

        return $this->render('evenement/evenementback.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/new', name: 'evenement_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $evenement = new Evenement();
    $form = $this->createForm(EvenementType::class, $evenement);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $dateDebut = $evenement->getDateDebut();
        $dateFin = $evenement->getDateFin();
        if ($dateDebut && $dateFin && $dateDebut > $dateFin) {
            // Add a form error instead of a flash message:
            $form->get('dateDebut')->addError(new FormError('La date de début ne peut pas être après la date de fin.'));
            return $this->render('evenement/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // Process file upload for image if needed
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
            $evenement->setImage($newFilename);
        }

        $entityManager->persist($evenement);
        $entityManager->flush();

        $this->addFlash('success', 'Evenement créé avec succès.');
        return $this->redirectToRoute('evenement_EvenementBack');
    }

    return $this->render('evenement/new.html.twig', [
        'form' => $form->createView(),
    ]);
}


    
#[Route('/{id}/edit', name: 'evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
>>>>>>> origin/GestionCommandes
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
<<<<<<< HEAD
            // 1) Validation + image upload…
            $dateDebut = $evenement->getDateDebut();
            $dateFin   = $evenement->getDateFin();
            if ($dateDebut && $dateFin && $dateDebut > $dateFin) {
                $form->get('dateDebut')->addError(new FormError('La date de début ne peut pas être après la date de fin.'));
            } else {
                if ($img = $form->get('image')->getData()) {
                    $newFilename = uniqid().'.'.$img->guessExtension();
                    $img->move($this->getParameter('uploads_directory'), $newFilename);
                    if ($old = $evenement->getImage()) {
                        @unlink($this->getParameter('uploads_directory').'/'.$old);
                    }
                    $evenement->setImage($newFilename);
                }

                $em->flush();

                // 2) Mise à jour Google Calendar
                if ($evenement->getGoogleEventId()) {
                    $this->calendarService->updateEvent($evenement->getGoogleEventId(), $evenement);
                } else {
                    $googleId = $this->calendarService->createEvent($evenement);
                    $evenement->setGoogleEventId($googleId);
                    $em->flush();
                }

                $this->addFlash('success', 'Événement mis à jour et synchronisé.');
                return $this->redirectToRoute('evenement_EvenementBack');
            }
=======
            // Validate start and end dates
            $dateDebut = $evenement->getDateDebut();
            $dateFin = $evenement->getDateFin();
            if ($dateDebut && $dateFin && $dateDebut > $dateFin) {
                $form->get('dateDebut')->addError(new FormError('La date de début ne peut pas être après la date de fin.'));
                return $this->render('evenement/edit.html.twig', [
                    'evenement' => $evenement,
                    'form' => $form->createView(),
                ]);
            }

            // Process file upload for image if needed
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);

                // Remove old image file if it exists
                if ($evenement->getImage()) {
                    $oldImage = $this->getParameter('uploads_directory') . '/' . $evenement->getImage();
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }

                $evenement->setImage($newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', '🎉 Événement mis à jour avec succès!');
            return $this->redirectToRoute('evenement_EvenementBack');
>>>>>>> origin/GestionCommandes
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
<<<<<<< HEAD
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            // Suppression dans Google Calendar
            if ($id = $evenement->getGoogleEventId()) {
                $this->calendarService->deleteEvent($id);
            }

            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé localement et sur Google Calendar.');
        } else {
            $this->addFlash('error', '❌ Token CSRF invalide !');
        }

        return $this->redirectToRoute('evenement_EvenementBack');
    }
    
    
    #[Route('/stat', name: 'evenement_stat')]
    public function stat(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Evenement::class);
        $now = new \DateTime();

        // 1) Fetch only active events (not expired), ordered by total tickets sold
        $results = $repository->createQueryBuilder('e')
            ->leftJoin('e.tickets', 't')
            ->addSelect('SUM(t.quantite) AS totalTickets')
            ->where('e.dateFin >= :now')
            ->setParameter('now', $now)
            ->groupBy('e.id')
            ->orderBy('totalTickets', 'DESC')
            ->getQuery()
            ->getResult();

        // 2) Split into top 3 and the rest
        $top3   = array_slice($results, 0, 3);
        $others = array_slice($results, 3);

        // 3) For the speed chart, take the 6 nearest upcoming (by dateDebut) that are still active
        $upcoming = $repository->createQueryBuilder('e')
            ->leftJoin('e.tickets','t')
            ->addSelect('SUM(t.quantite) AS totalTickets')
            ->where('e.dateDebut >= :now')
            ->andWhere('e.dateFin   >= :now')
            ->setParameter('now', $now)
            ->groupBy('e.id')
            ->orderBy('e.dateDebut','ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();

        // 4) Build speed data (tickets sold per day) for the chart
        $speedData = [];
        foreach ($upcoming as $row) {
            /** @var Evenement $event */
            $event       = $row[0];
            $sold        = (int)$row['totalTickets'];
            $daysElapsed = max(1, $now->diff($event->getDateDebut())->days);
            $speed       = round($sold / $daysElapsed, 2);

            $speedData[] = [
                'title' => $event->getTitre(),
                'speed' => $speed,
            ];
        }

        return $this->render('evenement/evenementstat.html.twig', [
            'top3'      => $top3,
            'others'    => $others,
            'speedData' => $speedData,
        ]);
    }
    
    

    #[Route('/Expired', name: 'evenement_expired')]
    public function expired(EntityManagerInterface $entityManager): Response
    {
        $now = new \DateTime();
        $expired = $entityManager
            ->getRepository(Evenement::class)
            ->createQueryBuilder('e')
            ->where('e.dateFin < :now')
            ->setParameter('now',$now)
            ->orderBy('e.dateFin','DESC')
            ->getQuery()
            ->getResult();
        return $this->render('evenement/oldevenement.html.twig',['evenements'=>$expired]);
    }

    #[Route('/{id}/duplicate', name: 'evenement_duplicate', methods: ['GET','POST'])]
    public function duplicate(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $clone = new Evenement();
        $clone->setTitre($evenement->getTitre())
              ->setDescription($evenement->getDescription())
              ->setLieu($evenement->getLieu())
              ->setPrix($evenement->getPrix())
              ->setTypeE($evenement->getTypeE())
              ->setArtiste($evenement->getArtiste());

        $form = $this->createForm(EvenementType::class,$clone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateDebut = $clone->getDateDebut();
            $dateFin   = $clone->getDateFin();
            if ($dateDebut && $dateFin && $dateDebut > $dateFin) {
                $form->get('dateDebut')->addError(new FormError('La date de début ne peut pas être après la date de fin.'));
            } else {
                $imageFile = $form->get('image')->getData();
                if ($imageFile) {
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move($this->getParameter('uploads_directory'),$newFilename);
                    $clone->setImage($newFilename);
                }
                $entityManager->persist($clone);
                $entityManager->remove($evenement);
                $entityManager->flush();
                $this->addFlash('success','Événement dupliqué et supprimé avec succès.');
                return $this->redirectToRoute('evenement_expired');
            }
        }

        return $this->render('evenement/new.html.twig',['form'=>$form->createView()]);
    }

    #[Route('/{id}', name: 'evenement_details', methods: ['GET'])]
    public function details(Evenement $evenement): Response
    {
        $maxTickets = 500;
        $sold = 0;
        foreach ($evenement->getTickets() as $ticket) {
            $sold += $ticket->getQuantite();
        }
        $percentage = $maxTickets > 0 ? round(($sold / $maxTickets) * 100, 2) : 0;
        $today = new \DateTime();
        $interval = $today->diff($evenement->getDateDebut());
        $daysUntilStart = max(0, (int)$interval->format('%r%a'));

        return $this->render('evenement/evenementdetails.html.twig', [
            'evenement'      => $evenement,
            'sold'           => $sold,
            'percentage'     => $percentage,
            'daysUntilStart' => $daysUntilStart,
            'maxTickets'     => $maxTickets,
        ]);
    }
=======
            'form' => $form->createView(),
        ]);
    }


    

    #[Route('/{id}/delete', name: 'evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', '🗑️ Event deleted successfully!');
        } else {
            $this->addFlash('error', '❌ Invalid CSRF token!');
        }
        return $this->redirectToRoute('evenement_EvenementBack');
    }

    #[Route('/stat', name: 'evenement_stat')]
public function stat(EntityManagerInterface $entityManager): Response
{
    // Build the query to calculate the total ticket quantity for each event
    $qb = $entityManager->createQueryBuilder();
    $qb->select('e', 'SUM(t.quantite) AS totalTickets')
        ->from(Evenement::class, 'e')
        ->leftJoin('e.tickets', 't') // assuming "tickets" is the property in Evenement mapping the Ticket relation
        ->groupBy('e.id')
        ->orderBy('totalTickets', 'DESC')
        ->setMaxResults(3);
        
    $results = $qb->getQuery()->getResult();

    return $this->render('evenement/evenementstat.html.twig', [
        'results' => $results,
    ]);
}

>>>>>>> origin/GestionCommandes
}
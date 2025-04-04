<?php 
namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evenement')]
class EvenementBackController extends AbstractController
{
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
{
    $form = $this->createForm(EvenementType::class, $evenement);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();
            $imageFile->move($this->getParameter('uploads_directory'), $newFilename);

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
    }

    return $this->render('evenement/edit.html.twig', [
        'evenement' => $evenement,
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

}
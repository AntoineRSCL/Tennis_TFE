<?php

namespace App\Controller;

use App\Entity\Court;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\CourtRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/myclub/reservation', name: 'reservation_index')]
    public function list(Request $request, CourtRepository $terrainRepository, ReservationRepository $reservationRepository): Response 
    {
        $date = $request->query->get('date', (new \DateTime())->format('Y-m-d'));

        // Récupérer tous les terrains
        $terrains = $terrainRepository->findAll();

        // Récupérer toutes les réservations pour la date sélectionnée
        $startDate = new \DateTime($date);
        $endDate = (clone $startDate)->setTime(23, 59, 59);
        $reservations = $reservationRepository->createQueryBuilder('r')
            ->where('r.startTime BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getResult();

        return $this->render('reservation/index.html.twig', [
            'courts' => $terrains,
            'reservations' => $reservations,
            'date' => $date,
        ]);
    }

    #[Route('/myclub/reservation/new', name: 'reservation_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les paramètres de la requête
        $startTime = $request->query->get('startTime');
        $endTime = $request->query->get('endTime');
        $courtId = $request->query->get('courtId');

        // Trouver le court par ID
        $court = $entityManager->getRepository(Court::class)->find($courtId);
        if (!$court) {
            throw $this->createNotFoundException('Court not found');
        }

        // Créer une nouvelle réservation
        $reservation = new Reservation();
        $reservation->setStartTime(new \DateTime($startTime));
        $reservation->setEndTime(new \DateTime($endTime));
        $reservation->setCourt($court);
        $reservation->setPlayer1($this->getUser()); // Utilisateur connecté

        // Créer et gérer le formulaire pour le joueur 2
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est soumis et valide, persister la réservation
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('reservation_index'); // Rediriger vers la liste des réservations
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'court' => $court, // Passer le court à la vue si nécessaire
            'startTime' => $startTime, // Passer le startTime à la vue
            'endTime' => $endTime, // Passer le endTime à la vue
            'reservation' => $reservation, // Passer la variable reservation au template
        ]);
    }
}

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
    /**
     * Fonction pour voir les reservations de terrain
     *
     * @param Request $request
     * @param CourtRepository $terrainRepository
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    #[Route('/myclub/reservation', name: 'reservation_index')]
    public function list(Request $request, CourtRepository $terrainRepository, ReservationRepository $reservationRepository): Response 
    {
        $date = $request->query->get('date', (new \DateTime())->format('Y-m-d'));

        // Définir le fuseau horaire
        $timezone = new \DateTimeZone('Europe/Brussels');
        $currentDate = new \DateTime('now', $timezone);

        // Vérifier si la date choisie est antérieure à aujourd'hui
        if (new \DateTime($date, $timezone) < $currentDate->setTime(0, 0, 0)) {
            $this->addFlash('danger', 'Vous ne pouvez pas réserver pour une date antérieure à aujourd\'hui.');
            return $this->redirectToRoute('reservation_index');
        }

        // Récupérer tous les terrains
        $terrains = $terrainRepository->findAll();

        // Récupérer toutes les réservations pour la date sélectionnée
        $startDate = new \DateTime($date, $timezone);
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

    /**
     * Fonction pour creer une reservation
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ReservationRepository $reservationRepository
     * @return Response
     */
    #[Route('/myclub/reservation/new', name: 'reservation_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $timezone = new \DateTimeZone('Europe/Brussels'); // Définir le fuseau horaire

        // Récupérer les réservations où l'utilisateur est player1 ou player2
        $userReservations = $reservationRepository->createQueryBuilder('r')
            ->where('r.player1 = :user OR r.player2 = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        // Si l'utilisateur n'a pas le rôle "ROLE_USER" et qu'il a 5 réservations ou plus, on bloque
        if ($this->isGranted('ROLE_USER') && count($userReservations) >= 5) {
            $this->addFlash('danger', 'Vous avez atteint le nombre maximum de 5 réservations.');
            return $this->redirectToRoute('reservation_index');
        }

        // Reste du code pour créer une nouvelle réservation
        $startTime = $request->query->get('startTime');
        $endTime = $request->query->get('endTime');
        $courtId = $request->query->get('courtId');

        $court = $entityManager->getRepository(Court::class)->find($courtId);
        if (!$court) {
            throw $this->createNotFoundException('Court not found');
        }

        // Vérifier si l'heure de début est dans le passé
        $currentDateTime = new \DateTime('now', $timezone);
        $reservationStartTime = new \DateTime($startTime, $timezone);

        if ($reservationStartTime < $currentDateTime) {
            $this->addFlash('danger', 'Vous ne pouvez pas réserver une heure qui est déjà passée.');
            return $this->redirectToRoute('reservation_index');
        }

        $reservation = new Reservation();
        $reservation->setStartTime($reservationStartTime);
        $reservation->setEndTime(new \DateTime($endTime, $timezone));
        $reservation->setCourt($court);
        $reservation->setPlayer1($user);

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();
            
            // Ajouter un message flash de succès
            $this->addFlash('success', 'Votre réservation a été créée avec succès.');
            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'court' => $court,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'reservation' => $reservation,
        ]);
    }

    /**Fonction pour supprimer une reservation de terrain */
    #[Route('/myclub/reservation/{id}/delete', name: 'reservation_delete')]
    public function deleteReservation(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est soit player1 soit player2, ou un admin
        if ($reservation->getPlayer1() === $user || $reservation->getPlayer2() === $user || $this->isGranted('ROLE_ADMIN')) {
            
            // Supprimer la réservation
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été annulée avec succès.');
        } else {
            // Si l'utilisateur n'a pas le droit de supprimer, on ajoute un message d'erreur
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette réservation.');
        }

        return $this->redirectToRoute('reservation_index');
    }

}

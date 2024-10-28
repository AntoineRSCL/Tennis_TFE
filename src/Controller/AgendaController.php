<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaReservation;
use App\Form\AgendaReservationType;
use App\Repository\AgendaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted; // Assurez-vous que cette ligne est ajoutée

class AgendaController extends AbstractController
{
    /**
     * Fonction pour voir les evenements
     *
     * @param AgendaRepository $agendaRepo
     * @return Response
     */
    #[Route('/agenda', name: 'agenda_index')]
    public function index(AgendaRepository $agendaRepo): Response
    {
        $agenda = $agendaRepo->findAllUpcomingEvents();

        return $this->render('agenda/index.html.twig', [
            'events' => $agenda,
        ]);
    }

    /**
     * Fonction pour voir un eveneemnt en particulier
     */
    #[Route('/agenda/{slug}', name: 'agenda_show')]
    public function show(Agenda $agenda): Response
    {
        $user = $this->getUser();
        $userReservationsCount = 0;

        if ($user) {
            foreach ($agenda->getAgendaReservations() as $reservation) {
                if ($reservation->getUser() === $user) {
                    $userReservationsCount++;
                }
            }
        }

        return $this->render('agenda/show.html.twig', [
            'agenda' => $agenda,
            'userReservationsCount' => $userReservationsCount,
        ]);
    }

    /**
     * Fonction pour reserver des places
     */
    #[Route('/agenda/{slug}/reserve', name: 'agenda_reserve')]
    public function reserve(Request $request, EntityManagerInterface $em, Agenda $agenda): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour réserver.');
        }

        $form = $this->createForm(AgendaReservationType::class);
        $form->handleRequest($request);

        $currentReservations = $agenda->getAgendaReservations()->filter(function($reservation) use ($user) {
            return $reservation->getUser() === $user;
        });

        // Compter le nombre de réservations actuelles de l'utilisateur
        $currentReservationsCount = count($currentReservations);

        // Compter le nombre total de réservations pour l'agenda
        $totalReservationsCount = count($agenda->getAgendaReservations());
        
        // Calculer le nombre de places restantes
        $remainingPlaces = $agenda->getLimitNumber() !== null ? $agenda->getLimitNumber() - $totalReservationsCount : PHP_INT_MAX;

        if ($form->isSubmitted() && $form->isValid()) {
            $nbPlaces = $form->get('nb_places')->getData(); // Récupérer le nombre de places depuis le formulaire

            // Calculer le total de places réservées
            $totalReservedPlaces = $currentReservationsCount + $nbPlaces;

            // Vérifier si le nombre total de réservations dépasse la limite
            if ($remainingPlaces < $nbPlaces) {
                $this->addFlash('danger', 'Il ne reste que ' . $remainingPlaces . ' place' . ($remainingPlaces > 1 ? 's' : '') . ' disponibles.');
                return $this->redirectToRoute('agenda_reserve', ['slug' => $agenda->getSlug()]);
            }

            // Modifier ou ajouter les réservations
            if ($currentReservationsCount > 0) {
                // Si l'utilisateur a déjà réservé, mettez à jour ses réservations
                // Suppression des réservations existantes
                foreach ($currentReservations as $reservation) {
                    $em->remove($reservation);
                }
            }

            // Créer les nouvelles réservations
            for ($i = 0; $i < $nbPlaces; $i++) {
                $reservation = new AgendaReservation();
                $reservation->setAgenda($agenda);
                $reservation->setUser($user);

                $em->persist($reservation);
            }

            $em->flush();

            $this->addFlash('success', 'Votre réservation de ' . $nbPlaces . ' place' . ($nbPlaces > 1 ? 's' : '') . ' a été effectuée avec succès.');

            return $this->redirectToRoute('agenda_show', ['slug' => $agenda->getSlug()]);
        }

        return $this->render('agenda/reserve.html.twig', [
            'agenda' => $agenda,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fonction pour annuler une reservation
     */
    #[Route('/agenda/{slug}/cancel', name: 'agenda_cancel')]
    public function cancel(Request $request, EntityManagerInterface $em, Agenda $agenda): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour annuler une réservation.');
        }

        // Récupérer les réservations existantes de l'utilisateur pour cet événement
        $currentReservations = $agenda->getAgendaReservations()->filter(function($reservation) use ($user) {
            return $reservation->getUser() === $user;
        });

        // Compter le nombre de réservations actuelles de l'utilisateur
        $currentReservationsCount = count($currentReservations);

        if ($currentReservationsCount > 0) {
            // Si l'utilisateur a des réservations, on annule toutes ses réservations
            foreach ($currentReservations as $reservation) {
                $em->remove($reservation);
            }

            $em->flush();

            $this->addFlash('success', 'Vos réservations ont été annulées avec succès.');
        } else {
            $this->addFlash('error', 'Aucune réservation à annuler.');
        }

        return $this->redirectToRoute('agenda_show', ['slug' => $agenda->getSlug()]);
    }
}

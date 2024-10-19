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

class AgendaController extends AbstractController
{
    #[Route('/agenda', name: 'agenda_index')]
    public function index(AgendaRepository $agendaRepo): Response
    {
        $agenda = $agendaRepo->findAllUpcomingEvents();

        return $this->render('agenda/index.html.twig', [
            'events' => $agenda,
        ]);
    }

    #[Route('/agenda/{slug}', name: 'agenda_show')]
    public function show(Agenda $agenda): Response
    {
        return $this->render('agenda/show.html.twig', [
        ]);
    }

    #[Route('/agenda/{slug}/reserve', name: 'agenda_reserve')]
    public function reserve(Request $request, EntityManagerInterface $em, Agenda $agenda): Response
    {
        $form = $this->createForm(AgendaReservationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nbPlaces = $form->get('nb_places')->getData();  // Récupérer le nombre de places depuis le formulaire
            $user = $this->getUser();

            // Calculer le nombre de places déjà réservées
            $currentReservations = count($agenda->getAgendaReservations());

            // Vérifier si le nombre de places demandées dépasse le nombre de places disponibles
            if ($agenda->getLimitNumber() !== null && ($currentReservations + $nbPlaces) > $agenda->getLimitNumber()) {
                $this->addFlash('error', 'Il ne reste que ' . ($agenda->getLimitNumber() - $currentReservations) . ' places disponibles.');
                return $this->redirectToRoute('agenda_reserve', ['slug' => $agenda->getSlug()]);
            }

            // Créer plusieurs réservations selon le nombre de places
            for ($i = 0; $i < $nbPlaces; $i++) {
                $reservation = new AgendaReservation();
                $reservation->setAgenda($agenda);
                $reservation->setUser($user);

                $em->persist($reservation);
            }

            $em->flush();

            $this->addFlash('success', 'Votre réservation de ' . $nbPlaces . ' places a été effectuée avec succès.');

            return $this->redirectToRoute('agenda_show', ['slug' => $agenda->getSlug()]);
        }

        return $this->render('agenda/reserve.html.twig', [
            'agenda' => $agenda,
            'form' => $form->createView(),
        ]);
    }


}

<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Repository\AgendaRepository;
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


}

<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'news_index')]
    public function index(NewsRepository $newsRepo): Response
    {
        // Récupérer toutes les actualités
        $newsList = $newsRepo->findAll();

        return $this->render('news/index.html.twig', [
            'newsList' => $newsList, // On passe la liste des actualités au template
        ]);
    }

    #[Route('/news/{slug}', name: 'news_show')]
    public function show(News $news): Response
    {
        // Grâce au ParamConverter, Symfony trouve l'entité News associée au slug
        return $this->render('news/show.html.twig', [
            'news' => $news, // On passe l'actualité spécifique au template
        ]);
    }
}

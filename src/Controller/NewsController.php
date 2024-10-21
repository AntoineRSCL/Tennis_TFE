<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsController extends AbstractController
{
    #[Route('/news/{page<\d+>?1}', name: 'news_index')]
    public function index(NewsRepository $newsRepo, PaginationService $pagination, int $page = 1): Response
    {
        $pagination->setEntityClass(News::class)
                   ->setLimit(9) // Limite à 9 résultats par page
                   ->setPage($page);

        return $this->render('news/index.html.twig', [
            'newsList' => $pagination->getData(),  // Les actualités paginées
            'pagination' => $pagination,           // L'objet de pagination pour la navigation
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

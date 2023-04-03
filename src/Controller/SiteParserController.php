<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteParserController extends AbstractController
{
    #[Route('/', name: 'site_parser')]
    public function index(PageRepository $pageRepository): Response
    {
        $pages = $pageRepository->findAllSortedByImagesCount();

        return $this->render('site_parser/parser.html.twig', [
            'pages' => $pages,
        ]);
    }
}

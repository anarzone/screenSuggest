<?php

namespace App\Domain\Content\Controller;

use App\Domain\Content\Service\ScrapingService;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Deprecated("ScrapingService is not yet implemented")]
class ScrapingController extends AbstractController
{
    public function __construct(private readonly ScrapingService $scrapingService)
    {
    }

    #[Route('/content/scrape', name: 'scrape', methods: ['GET'])]
    public function scrape()
    {
        dd($this->scrapingService->scrape());
        return $this->scrapingService->scrape();
    }
}
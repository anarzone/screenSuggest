<?php

namespace App\Domain\Content\Controller;

use App\Domain\Common\Enum\ContentType;
use App\Domain\Content\Service\ContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContentController extends AbstractController
{
    public function __construct(
        readonly private ContentService $contentService,
    )
    {
    }

    #[Route(
        '/content/{type}/{full}',
        name: 'content_all',
        methods: ['GET']
    )]
    public function index(ContentType $type = ContentType::DEFAULT, bool $full = false): Response
    {
        return $this->json(
            [
                'data' => $this->contentService->content($type, $full),
            ]
        );
    }
}
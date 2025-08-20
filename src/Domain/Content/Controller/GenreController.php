<?php

namespace App\Domain\Content\Controller;

use App\Domain\Content\Service\GenreService;
use App\Domain\Content\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GenreController extends AbstractController
{
    public function __construct(
        private readonly GenreService $genreService,
        private readonly PaginationService $paginationService,
    ) {
    }

    #[Route(
        path: '/genres',
        name: 'app_genre_index',
        methods: ['GET']
    )]
    public function index(Request $request): JsonResponse
    {
        $pagination = $this->paginationService->getPaginationParameters($request);

        [$data, $total] = $this->genreService->getPaginatedOrderedByName(
            $pagination['limit'],
            $pagination['offset']
        );

        return $this->json([
            'message' => 'Genres retrieved successfully',
            'data' => $data,
            'pagination' => $this->paginationService->createPaginationData(
                $pagination['page'],
                $pagination['limit'],
                $total
            ),
        ]);
    }
}

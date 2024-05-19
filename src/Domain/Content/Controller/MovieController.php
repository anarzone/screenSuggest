<?php

namespace App\Domain\Content\Controller;

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Hydrator\MovieHydrator;
use App\Domain\Content\Service\MovieService;
use App\Entity\Movie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MovieController extends AbstractController
{
    public function __construct(
        readonly private ValidatorInterface $validator,
        readonly private MovieService $movieService,
        readonly private MovieHydrator $movieHydrator
    )
    {
    }

    #[Route(
        '/movies',
        name: 'movies_all',
        methods: ['GET']
    )]
    public function index(): JsonResponse
    {
        return $this->json(
            [
                'data' => $this->movieService->all(),
            ]
        );
    }

    #[Route('/movies/{movie}', name: 'movies_show', methods: ['GET'])]
    public function show(Movie $movie): JsonResponse
    {
        return $this->json(
            [
                'data' => $this->movieService->single($movie)
            ]
        );
    }

    #[Route('/movies', name: 'movies_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        return $this->save(
            movieDto: $this->movieHydrator->hydrate(request: $request),
            message: "Movie created successfully!"
        );
    }

    #[Route('/movies/{movie}', name: 'movies_update', methods: ['PATCH'])]
    public function update(Movie $movie, Request $request): JsonResponse
    {
        return $this->save(
            movieDto: $this->movieHydrator->hydrate(movie: $movie, request: $request),
            message: "Movie updated successfully!"
        );
    }

    private function save(ContentDtoInterface $movieDto, string $message): JsonResponse
    {
        $errors = $this->validator->validate($movieDto);

        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $movieDto = $this->movieService->store($movieDto);

        return $this->json([
            'message' => $message,
            'data' => $movieDto,
        ], Response::HTTP_CREATED);
    }
}
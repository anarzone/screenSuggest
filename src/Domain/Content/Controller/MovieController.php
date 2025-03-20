<?php

namespace App\Domain\Content\Controller;

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Dto\Movie\MovieDto;
use App\Domain\Content\Hydrator\MovieHydrator;
use App\Domain\Content\Service\CSVConverterService;
use App\Domain\Content\Service\MovieService;
use App\Repository\MovieRepository;
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
        readonly private MovieHydrator $movieHydrator,
        readonly private MovieService $movieService,
        readonly private MovieRepository $movieRepository,
        readonly private CSVConverterService $csvConverterService
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
                'message' => 'All movies',
                'data' => $this->movieService->all(),
            ]
        );
    }

    #[Route('/movies/{id}', name: 'movies_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $movie = $this->movieRepository->findOneBy(['id' => $id]);

        if ($movie === null) {
            return $this->json(['message' => 'Movie not found!', 'data' => []], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            [
                'message' => 'Movie found!',
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

    #[Route('/movies/{id}', name: 'movies_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $movie = $this->movieRepository->findOneBy(['id' => $id]);

        if ($movie === null) {
            return $this->json(['message' => 'Movie not found!', 'data' => []], Response::HTTP_NOT_FOUND);
        }

        return $this->save(
            movieDto: $this->movieHydrator->hydrate(movieData: $movie, request: $request),
            message: "Movie updated successfully!"
        );
    }

    #[Route('/movies/{id}', name: 'movies_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $movie = $this->movieRepository->findOneBy(['id' => $id]);

        if ($movie === null) {
            return $this->json(['message' => 'Movie not found!', 'data' => []], Response::HTTP_NOT_FOUND);
        }

        $this->movieService->delete($movie);

        return $this->json(['message' => 'Movie deleted successfully!', 'data' => []]);
    }

    private function save(MovieDto $movieDto, string $message): JsonResponse
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

    #[Route('/csv', name: 'movies_csv', methods: ['GET'])]
    public function testUrl(): void
    {
        $this->csvConverterService->convert();
    }
}

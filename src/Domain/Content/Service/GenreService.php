<?php

namespace App\Domain\Content\Service;

use App\Entity\Genre;
use App\Repository\GenresRepository;

final readonly class GenreService
{
    public function __construct(
        private GenresRepository $genresRepository,
    ) {
    }

    /**
     * Returns a paginated, stable-ordered list of genres and the total count.
     *
     * @return array{0: array<int, array{id:int,name:string,description:?string}>, 1: int}
     */
    public function getPaginatedOrderedByName(int $limit, int $offset): array
    {
        $genres = $this->genresRepository->findBy(
            criteria: [],
            orderBy: ['name' => 'ASC'],
            limit: $limit,
            offset: $offset,
        );

        $total = $this->genresRepository->count();

        $data = array_map(static function (Genre $genre): array {
            return [
                'id' => $genre->getId(),
                'name' => $genre->getName(),
                'description' => $genre->getDescription(),
            ];
        }, $genres);

        return [$data, $total];
    }
}

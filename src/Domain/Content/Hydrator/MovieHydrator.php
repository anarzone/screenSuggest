<?php

namespace App\Domain\Content\Hydrator;

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Dto\Movie\MovieDto;
use App\Entity\Director;
use App\Entity\Movie;
use Symfony\Component\HttpFoundation\Request;

final readonly class MovieHydrator
{
    public function __construct(private DirectorHydrator $directorHydrator)
    {
    }

    public function hydrate(?Movie $movie = null, ?Request $request = null): ContentDtoInterface
    {
        if($movie !== null && $request !== null) {
            $requestData = json_decode($request->getContent());
            return $this->populateFromMovieAndObject($movie, $requestData);
        }

        if($movie === null && $request !== null) {
            $requestData = json_decode($request->getContent());
            return $this->populateFromObject($requestData);
        }

        if ($movie !== null && $request === null){
            return $this->populateFromObject($movie);
        }
    }

    public function hydrateCollection(array $movies): array
    {
        $populatedData = [];

        foreach ($movies as $movie) {
            $populatedData[] = $this->hydrate($movie);
        }

        return $populatedData;
    }

    private function populateFromMovieAndObject(Movie $movie, object $requestData): ContentDtoInterface
    {
        $dto = new MovieDto();

        $dto->id = $movie->getId();
        $dto->title = $this->requestValue($requestData, 'title') ?? $movie->getTitle();
        $dto->description = $this->requestValue($requestData, 'description') ?? $movie->getDescription();
        $dto->release_date = new \DateTime($this->requestValue($requestData, 'release_date')) ?? $movie->getReleaseDate();
        $dto->duration = $this->requestValue($requestData, 'duration') ?? $movie->getDuration()->value();
        $dto->director = $this->directorHydrator->hydrate($this->requestValue($requestData, 'director') ?? $movie->getDirector());
        $dto->external_id = $this->requestValue($requestData, 'external_id') ?? $movie->getExternalId();

        return $dto;
    }

    private function populateFromObject(object $movieData): ContentDtoInterface
    {
        $dto = new MovieDto();

        $dto->id = $this->requestValue($movieData, 'id');
        $dto->title = $this->requestValue($movieData, 'title');
        $dto->description = $this->requestValue($movieData, 'description');

        if($release_date = $this->requestValue($movieData, 'release_date')) {
            $dto->release_date = is_string($release_date) ? new \DateTime($release_date) : $release_date;
        }

        $duration = $this->requestValue($movieData, 'duration');

        $dto->duration = is_int($duration) ? $this->requestValue($movieData, 'duration') : $duration->value();
        $dto->director = $this->directorHydrator->hydrate($this->requestValue($movieData, 'director'));
        $dto->external_id = $this->requestValue($movieData, 'external_id');

        return $dto;
    }

    private function requestValue($movieData, $key)
    {
        if ($movieData instanceof Movie){
            return $movieData->{"get".$this->filterString($key)}();
        }

        return property_exists($movieData, $key) ? $movieData->$key : null;
    }

    private function filterString($string): string
    {
        return ucwords(str_replace('_', '', $string));
    }
}
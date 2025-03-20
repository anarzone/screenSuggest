<?php

namespace App\Domain\Content\Hydrator;

use App\Domain\Content\Dto\Movie\MovieDto;
use App\Domain\Content\ValueObject\Content\Duration;
use App\Entity\Movie;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;

final readonly class MovieHydrator
{
    public function __construct(
        private DirectorHydrator $directorHydrator
    )
    {
    }

    public function hydrate(object|array|null $movieData = null, ?Request $request = null): MovieDto
    {
        if($movieData instanceof Movie && $request !== null) {
            $requestData = json_decode($request->getContent());

            return $this->populateFromMovieAndObject($movieData, $requestData);
        }

        if($movieData === null && $request !== null) {
            $requestData = json_decode($request->getContent());
            return $this->populateFromObjectOrArray($requestData);
        }

        if (is_array($movieData)){
            return $this->populateFromObjectOrArray($movieData);
        }

        if ($movieData !== null && $request === null){
            return $this->populateFromObjectOrArray($movieData);
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

    private function populateFromMovieAndObject(Movie $movie, object $requestData): MovieDto
    {
        $movieDto = new MovieDto();

        $movieDto->id = $movie->getId();
        $movieDto->title = $this->requestValue($requestData, 'title') ?? $movie->getTitle();
        $movieDto->originalTitle = $this->requestValue($requestData, 'originalTitle') ?? $movie->getOriginalTitle();
        $movieDto->description = $this->requestValue($requestData, 'description') ?? $movie->getDescription();
        $movieDto->releaseDate = new \DateTime($this->requestValue($requestData, 'releaseDate')) ?? $movie->getReleaseDate();
        $movieDto->duration = $this->requestValue($requestData, 'duration') ?? $movie->getDuration()->value();
        $movieDto->directors = $this->getPopulatedDirectors($requestData);
        $movieDto->genres = $this->requestValue($requestData, 'genres') ?? $movie->getGenres();
        $movieDto->actors = $this->requestValue($requestData, 'actors') ?? $movie->getActors();
        $movieDto->imdbRating = $this->requestValue($requestData, 'imdbRating') ?? $movie->getImdbRating();
        $movieDto->tmdbId = $this->requestValue($requestData, 'external_id') ?? $movie->getTmdbId();
        $movieDto->imdbId = $this->requestValue($requestData, 'imdbId') ?? $movie->getImdbId();
        $movieDto->imdbVotes = $this->requestValue($requestData, 'imdbVotes') ?? $movie->getImdbVotes();
        $movieDto->productionCountries = $this->requestValue($requestData, 'productionCountries') ?? $movie->getProductionCountries();
        $movieDto->productionCompanies = $this->requestValue($requestData, 'productionCompanies') ?? $movie->getProductionCompanies();
        $movieDto->spokenLanguages = $this->requestValue($requestData, 'spokenLanguages') ?? $movie->getSpokenLanguages();
        $movieDto->posterPath = $this->requestValue($requestData, 'posterPath') ?? $movie->getPosterPath();

        return $movieDto;
    }

    private function populateFromObjectOrArray(object|array $movieData): MovieDto
    {
        $movieDto = new MovieDto();

        $movieDto->id = $this->requestValue($movieData, 'id');
        $movieDto->title = $this->requestValue($movieData, 'title');
        $movieDto->originalTitle = $this->requestValue($movieData, 'originalTitle');
        $movieDto->description = $this->requestValue($movieData, 'description');

        if($release_date = $this->requestValue($movieData, 'releaseDate')) {
            $movieDto->releaseDate = is_string($release_date) ? new \DateTime($release_date) : $release_date;
        }

        $duration = $this->requestValue($movieData, 'duration');
        $movieDto->duration = $duration instanceof Duration ? $duration->value() : $duration; // Make it more readable
        $movieDto->directors = $this->getPopulatedDirectors($movieData);
        $movieDto->imdbRating = $this->requestValue($movieData, 'imdbRating');
        $movieDto->tmdbId = $this->requestValue($movieData, 'tmdbId');
        $movieDto->imdbId = $this->requestValue($movieData, 'imdbId');
        $movieDto->imdbVotes = $this->requestValue($movieData, 'imdbVotes');
        $movieDto->productionCountries = $this->requestValue($movieData, 'productionCountries');
        $movieDto->productionCompanies = $this->requestValue($movieData, 'productionCompanies');
        $movieDto->spokenLanguages = $this->requestValue($movieData, 'spokenLanguages');
        $movieDto->posterPath = $this->requestValue($movieData, 'posterPath');
        $movieDto->genres = $this->explodeVal($this->requestValue($movieData, 'genres'));
        $movieDto->actors = $this->explodeVal($this->requestValue($movieData, 'actors'));

        return $movieDto;
    }

    private function requestValue(object|array $movieData, $key)
    {
        if (is_array($movieData)){
            return $movieData[$key] ?? null;
        }

        if ($movieData instanceof Movie){
            return $movieData->{"get".$this->filterString($key)}();
        }

        return property_exists($movieData, $key) ? $movieData->$key : null;
    }

    private function filterString($string): string
    {
        return ucwords(str_replace('_', '', $string));
    }

    private function explodeVal(string|object $value = null): ?array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof Collection) {
            foreach ($value as $item) {
                $values[] = $item->getName();
            }

            return $values;
        }

        if (!str_contains($value, ',')) {
            return [$value];
        }

        return explode(',', $value);
    }

    private function getPopulatedDirectors(object|array $requestData): array|null|Collection
    {
        $directors = $this->requestValue($requestData, 'directors');
        return $this->directorHydrator->hydrateCollection($this->explodeVal($directors));
    }
}

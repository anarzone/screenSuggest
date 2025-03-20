<?php

namespace App\Domain\Content\Service;

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Dto\Director\DirectorDto;
use App\Domain\Content\Dto\Movie\MovieDto;
use App\Domain\Content\Hydrator\MovieHydrator;
use App\Domain\Content\ValueObject\Content\Duration;
use App\Domain\Content\ValueObject\Content\Title;
use App\Entity\Actor;
use App\Entity\Director;
use App\Entity\Genre;
use App\Entity\Movie;
use App\Repository\ActorRepository;
use App\Repository\DirectorRepository;
use App\Repository\GenresRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MovieService
{
    public function __construct(
        private ActorRepository $actorRepository,
        private EntityManagerInterface $entityManager,
        private MovieRepository        $movieRepository,
        private DirectorRepository     $directorRepository,
        private MovieHydrator          $movieHydrator,
        private GenresRepository      $genreRepository
    )
    {
    }

    public function all(): array
    {
        return $this->movieHydrator->hydrateCollection($this->movieRepository->findAll());
    }

    public function single(Movie $movie): ContentDtoInterface
    {
        return $this->movieHydrator->hydrate(movieData: $movie);
    }

    public function store(MovieDto $movieInputDto): MovieDto
    {
        $movie = $movieInputDto->id ? $this->movieRepository->findOneBy(['id'=>$movieInputDto->id]) : new Movie();

        $movie->setTitle(Title::fromString($movieInputDto->title));
        $movie->setOriginalTitle($movieInputDto->originalTitle);
        $movie->setDescription($movieInputDto->description);
        $movie->setReleaseDate($movieInputDto->releaseDate);
        $movie->setDuration(Duration::fromInt($movieInputDto->duration));
        $this->addDirectors($movie, $movieInputDto->directors);
        $movie->setImdbRating($movieInputDto->imdbRating);
        $movie->setTmdbId($movieInputDto->tmdbId);
        $movie->setImdbId($movieInputDto->imdbId);
        $movie->setImdbVotes($movieInputDto->imdbVotes);
        $movie->setProductionCountries($movieInputDto->productionCountries);
        $movie->setProductionCompanies($movieInputDto->productionCompanies);
        $movie->setSpokenLanguages($movieInputDto->spokenLanguages);
        $movie->setPosterPath($movieInputDto->posterPath);
        $this->addGenres($movie, $movieInputDto->genres);
        $this->addActors($movie, $movieInputDto->actors);

        $this->entityManager->persist($movie);
        $this->entityManager->flush();

        $movieInputDto->id = $movie->getId();

        return $this->movieHydrator->hydrate(movieData: $movie);
    }

    public function delete(Movie $movie): bool
    {
        $this->entityManager->remove($movie);
        $this->entityManager->flush();

        return true;
    }

    private function getDirector(DirectorDto $directorDto): Director
    {
        $director = $this->directorRepository->findOneBy(['name' => $directorDto->name]);

        if ($director === null) {
            $director = new Director();
            $director->setName($directorDto->name);
            $director->setBirthDate($directorDto->birthdate);
            $director->setNationality($directorDto->nationality);

            $this->entityManager->persist($director);
            $this->entityManager->flush();
        }

        return $director;
    }

    private function addGenres(Movie $movie, array $genres): void
    {
        foreach ($genres as $name) {
            $genre = $this->genreRepository->findOneBy(['name' => $name]);

            if ($genre === null) {
                $genre = new Genre();
                $genre->setName($name);
                $this->entityManager->persist($genre);
            }

            $movie->addGenre($genre);
        }
    }

    private function addActors(Movie $movie, array $actors): void
    {
        foreach ($actors as $name) {
            $actor = $this->actorRepository->findOneBy(['name' => $name]);

            if ($actor === null) {
                $actor = new Actor();
                $actor->setName($name);
                $this->entityManager->persist($actor);
            }

            $movie->addActor($actor);
        }
    }

    private function addDirectors(Movie $movie, array $directors): void
    {
        foreach ($directors as $directorDto) {
            $director = $this->getDirector($directorDto);
            $movie->addDirector($director);
        }
    }
}

<?php

namespace App\Domain\Content\Service;

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Dto\Director\DirectorDto;
use App\Domain\Content\Dto\Movie\MovieDto;
use App\Domain\Content\Hydrator\MovieHydrator;
use App\Domain\Content\ValueObject\Content\Duration;
use App\Domain\Content\ValueObject\Content\Title;
use App\Entity\Director;
use App\Entity\Movie;
use App\Repository\DirectorRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MovieService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MovieRepository        $movieRepository,
        private DirectorRepository     $directorRepository,
        private MovieHydrator          $movieHydrator,
    )
    {
    }

    public function all(): array
    {
        return $this->movieHydrator->hydrateCollection($this->movieRepository->findAll());
    }

    public function single(Movie $movie): ContentDtoInterface
    {
        return $this->movieHydrator->hydrate($movie);
    }

    public function store(ContentDtoInterface $movieInputDto): ContentDtoInterface
    {
        $movie = $movieInputDto->id ? $this->movieRepository->findOneBy(['id'=>$movieInputDto->id]) : new Movie();

        $movie->setTitle(Title::fromString($movieInputDto->title));
        $movie->setDescription($movieInputDto->description);
        $movie->setReleaseDate($movieInputDto->release_date);
        $movie->setDuration(Duration::fromInt($movieInputDto->duration));
        $movie->setDirector($this->director($movieInputDto->director));

        $this->entityManager->persist($movie);
        $this->entityManager->flush();

        $movieInputDto->id = $movie->getId();

        return $this->movieHydrator->hydrate($movie);
    }

    private function director(DirectorDto $directorDto): Director
    {
        $director = $this->directorRepository->findOneBy(['name' => $directorDto->name]);

        if ($director === null) {
            $director = new Director();
        }

        $director->setName($directorDto->name);
        $director->setBirthDate($directorDto->birthdate);
        $director->setNationality($directorDto->nationality);

        $this->entityManager->persist($director);
        $this->entityManager->flush();

        return $director;
    }
}
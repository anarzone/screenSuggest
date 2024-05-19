<?php

namespace App\Entity;

use App\Repository\MovieGenresRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieGenresRepository::class)]
class MovieGenre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $movie_id = null;

    #[ORM\Column]
    private ?int $genre_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovieId(): ?int
    {
        return $this->movie_id;
    }

    public function setMovieId(int $movie_id): static
    {
        $this->movie_id = $movie_id;

        return $this;
    }

    public function getGenreId(): ?int
    {
        return $this->genre_id;
    }

    public function setGenreId(int $genre_id): static
    {
        $this->genre_id = $genre_id;

        return $this;
    }
}

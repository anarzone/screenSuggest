<?php

namespace App\Entity;

use App\Repository\TvShowGenreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TvShowGenreRepository::class)]
class TvShowGenre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $tv_show_id = null;

    #[ORM\Column]
    private ?int $genre_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTvShowId(): ?int
    {
        return $this->tv_show_id;
    }

    public function setTvShowGenreId(int $tv_show_genre_id): static
    {
        $this->tv_show_id = $tv_show_genre_id;

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

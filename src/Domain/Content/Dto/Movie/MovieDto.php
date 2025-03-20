<?php

namespace App\Domain\Content\Dto\Movie;

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Dto\Director\DirectorDto;
use Symfony\Component\Validator\Constraints as Assert;
use App\Domain\Content\Validator\Movie as MovieValidator;

class MovieDto implements ContentDtoInterface
{
    #[Assert\NotBlank(groups: ['update'])]
    public ?string $id = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[MovieValidator\UniqueTitle]
    public string $title;
    #[Assert\Length(max: 255)]
    public ?string $originalTitle = null;
    public ?string $description = null;

    #[Assert\NotNull]
    public ?\DateTimeInterface $releaseDate = null;

    #[Assert\NotNull]
    #[Assert\Range(min: 1, max: 6000)]
    public ?int $duration = null;
    public ?array $directors = null;
    public ?string $imdbRating = null;
    public ?string $tmdbId = null;
    public ?string $imdbId = null;

    #[Assert\Length(max: 255)]
    public ?string $imdbVotes = null;
    public ?string $productionCountries = null;
    public ?string $productionCompanies = null;
    public ?string $spokenLanguages = null;

    #[Assert\Length(max: 255)]
    public ?string $posterPath = null;

    /**
     * @var array<int, int> IDs of associated genres
     */
    #[Assert\All([
        new Assert\Type("integer"),
    ])]
    public array $genres = [];

    /**
     * @var array<int, int> IDs of associated actors
     */
    #[Assert\All([
        new Assert\Type("integer"),
    ])]
    public array $actors = [];
}

<?php

namespace App\Domain\Content\Dto\Movie;

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Dto\Director\DirectorDto;
use Symfony\Component\Validator\Constraints as Assert;
use App\Domain\Content\Validator\Movie as MovieValidator;

class MovieDto implements ContentDtoInterface
{
    #[Assert\NotBlank(groups: ['update'])]
    public ?string $id=null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[MovieValidator\UniqueTitle]
    public string $title;
    public ?string $description;
    #[Assert\NotNull]
    public \DateTimeInterface $release_date;

    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 6000)]
    public int $duration;
    public ?DirectorDto $director;

    public ?float $rating;
    public ?float $imdb_rating;
    public ?float $rotten_tomatoes_rating;
    public ?int $external_id;
}
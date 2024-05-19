<?php

namespace App\Domain\Content\Dto\TvShow;

use App\Domain\Common\Enum\ContentType;
use Symfony\Component\Validator\Constraints as Assert;

class TvShowInputDto
{
    #[Assert\NotNull]
    public string $title;
    public ?string $description;
    #[Assert\NotNull]
    public \DateTime $release_date;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [ContentType::class, 'values'])]
    public string $type;

    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 6000)]
    public int $duration;
    public ?string $director;
    public ?string $creator;
    public ?float $rating;
    public ?float $imdb_rating;
    public ?float $rotten_tomatoes_rating;
    public ?int $external_content_id;
}
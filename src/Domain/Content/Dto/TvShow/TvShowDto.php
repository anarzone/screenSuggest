<?php

namespace App\Domain\Content\Dto\TvShow;

use App\Domain\Content\Dto\ContentDtoInterface;

class TvShowDto implements ContentDtoInterface
{
    public ?string $id;
    public string $title;
    public ?string $description;
    public \DateTimeInterface $release_date;
    public string $type;
    public int $duration;
    public ?string $director;
    public ?string $creator;
    public ?float $rating;
    public ?float $imdb_rating;
    public ?float $rotten_tomatoes_rating;
    public ?int $external_content_id;
}
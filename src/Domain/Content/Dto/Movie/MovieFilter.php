<?php

namespace App\Domain\Content\Dto\Movie;

use Symfony\Component\HttpFoundation\Request;

final class MovieFilter
{
    public function __construct(
        public ?string $genre,
        public ?int    $year,
        public ?float  $imdbRatingMin,
        public ?float  $imdbRatingMax
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->query->get('genre'),
            $request->query->getInt('year') ?: null,
            $request->query->get('ratingMin') ?: null,
            $request->query->get('ratingMax') ?: null
        );
    }
}

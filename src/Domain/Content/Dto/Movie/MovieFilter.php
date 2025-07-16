<?php

namespace App\Domain\Content\Dto\Movie;

use Symfony\Component\HttpFoundation\Request;

final class MovieFilter
{
    public function __construct(
        public ?string $query,
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
            $request->query->get('q') ?: null,
            $request->query->get('genre') ?: null,
            $request->query->getInt('year') ?: null,
            $request->query->get('ratingMin') ?: null,
            $request->query->get('ratingMax') ?: null
        );
    }

    public function hasSearch(): bool
    {
        return !empty($this->query) && strlen(trim($this->query)) >= 3;
    }

    public function hasFilters(): bool
    {
        return !empty($this->genre) ||
            !empty($this->year) ||
            !empty($this->imdbRatingMin) ||
            !empty($this->imdbRatingMax);
    }
}

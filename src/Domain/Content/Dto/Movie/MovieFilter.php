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
        public ?float  $imdbRatingMax,
        public ?string $sortBy = null,
        public ?string $sortOrder = null,
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
            $request->query->get('ratingMax') ?: null,
            $request->query->get('sortBy') ?: null,
            $request->query->get('sortOrder') ?: null
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

    public function hasSort(): bool
    {
        return !empty($this->sortBy);
    }

    public function getSortBy(): ?string
    {
        $allowedSortFields = [
            'title', 'releaseDate', 'imdbRating', 'duration', 'createdAt'
        ];

        return in_array($this->sortBy, $allowedSortFields) ? $this->sortBy : 'releaseDate';
    }

    public function getSortOrder(): string
    {
        return strtolower($this->sortOrder) === 'asc' ? 'ASC' : 'DESC';
    }
}

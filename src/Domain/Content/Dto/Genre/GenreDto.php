<?php

namespace App\Domain\Content\Dto\Genre;

use App\Domain\Content\Dto\ContentDtoInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GenreDto implements ContentDtoInterface
{
    #[Assert\NotBlank(groups: ['update'])]
    public ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $name;

    #[Assert\Length(max: 65535)]
    public ?string $description = null;

    /**
     * @var array<int> IDs of associated TV shows
     */
    #[Assert\All([
        new Assert\Type("integer"),
    ])]
    public array $tvShows = [];

    /**
     * @var array<int> IDs of associated movies
     */
    #[Assert\All([
        new Assert\Type("integer"),
    ])]
    public array $movies = [];
}

<?php

namespace App\Domain\Content\Dto\Actor;

use App\Domain\Content\Dto\ContentDtoInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ActorDto implements ContentDtoInterface
{
    #[Assert\NotBlank(groups: ['update'])]
    public ?int $id = null;

    public ?bool $adult = null;

    #[Assert\Length(max: 65535)]
    public ?string $alsoKnownAs = null;

    #[Assert\Length(max: 65535)]
    public ?string $biography = null;

    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $birthday = null;

    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $deathday = null;

    #[Assert\Choice(choices: ['male', 'female', 'other'], message: 'Choose a valid gender.')]
    public ?string $gender = null;

    #[Assert\Url]
    public ?string $website = null;

    public ?int $tmdbId = null;

    public ?int $imdbId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    #[Assert\Length(max: 255)]
    public ?string $placeOfBirth = null;

    #[Assert\Length(max: 255)]
    public ?string $profilePath = null;

    /**
     * @var array<int> IDs of associated movies
     */
    #[Assert\All([
        new Assert\Type("integer"),
    ])]
    public array $movies = [];
}

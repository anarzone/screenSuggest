<?php

namespace App\Domain\Content\Dto\Director;

use Symfony\Component\Validator\Constraints as Assert;
use App\Domain\Content\Validator\Director as DirectorValidator;

class DirectorDto
{
    public ?int $id = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[DirectorValidator\UniqueName]
    public string $name;
    public ?\DateTimeInterface $birthdate = null;
    public ?string $nationality = null;

    /**
     * @var array<int> IDs of associated movies
     */
    #[Assert\All([
        new Assert\Type("integer"),
    ])]
    public array $movies = [];
}

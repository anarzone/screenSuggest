<?php

namespace App\Entity;

use App\Repository\CreatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreatorRepository::class)]
#[ORM\Table(name: 'creators')]
class Creator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(length: 70, nullable: true)]
    private ?string $nationality = null;

    /**
     * @var Collection<int, TvShow>
     */
    #[ORM\OneToMany(targetEntity: TvShow::class, mappedBy: 'creator')]
    private Collection $tvShows;

    public function __construct()
    {
        $this->tvShows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * @return Collection<int, TvShow>
     */
    public function getTvShows(): Collection
    {
        return $this->tvShows;
    }

    public function addTvShow(TvShow $tvShow): static
    {
        if (!$this->tvShows->contains($tvShow)) {
            $this->tvShows->add($tvShow);
            $tvShow->setCreator($this);
        }

        return $this;
    }

    public function removeTvShow(TvShow $tvShow): static
    {
        if ($this->tvShows->removeElement($tvShow)) {
            // set the owning side to null (unless already changed)
            if ($tvShow->getCreator() === $this) {
                $tvShow->setCreator(null);
            }
        }

        return $this;
    }
}

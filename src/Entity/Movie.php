<?php

namespace App\Entity;

use App\Domain\Content\ValueObject\Content\Duration;
use App\Domain\Content\ValueObject\Content\Title;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ORM\Table(name: 'movies')]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'title', length: 255)]
    private Title $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(type: 'duration', nullable: true)]
    private ?Duration $duration = null;

    #[ORM\ManyToMany(targetEntity: Director::class, inversedBy: 'movies')]
    private ?Collection $directors;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'movie')]
    private Collection $reviews;

    #[ORM\Column(nullable: true)]
    private ?string $tmdbId = null;

    /**
     * @var Collection<int, Actor>
     */
    #[ORM\ManyToMany(targetEntity: Actor::class, inversedBy: 'movies')]
    private Collection $actors;

    /**
     * @var Collection<int, Genre>
     */
    #[ORM\ManyToMany(targetEntity: Genre::class, inversedBy: 'movies')]

    private Collection $genres;

    #[ORM\Column(nullable: true)]
    private ?string $imdbId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imdbRating = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imdbVotes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $productionCountries = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $productionCompanies = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $spokenLanguages = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterPath = null;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->actors = new ArrayCollection();
        $this->genres = new ArrayCollection();
        $this->directors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function setTitle(Title $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getDuration(): ?Duration
    {
        return $this->duration;
    }

    public function setDuration(?Duration $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDirectors(): ?Collection
    {
        return $this->directors;
    }

    public function addDirector(Director $director): static
    {
        if (!$this->directors->contains($director)) {
            $this->directors->add($director);
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setMovie($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getMovie() === $this) {
                $review->setMovie(null);
            }
        }

        return $this;
    }

    public function getTmdbId(): ?string
    {
        return $this->tmdbId;
    }

    public function setTmdbId(?string $tmdbId): static
    {
        $this->tmdbId = $tmdbId;

        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
        }

        return $this;
    }

    public function removeActor(Actor $actor): static
    {
        $this->actors->removeElement($actor);

        return $this;
    }

    /**
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): static
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): static
    {
        $this->genres->removeElement($genre);

        return $this;
    }

    public function getImdbId(): ?string
    {
        return $this->imdbId;
    }

    public function setImdbId(?string $imdbId): static
    {
        $this->imdbId = $imdbId;

        return $this;
    }

    public function getImdbRating(): ?string
    {
        return $this->imdbRating;
    }

    public function setImdbRating(?string $imdbRating): static
    {
        $this->imdbRating = $imdbRating;

        return $this;
    }

    public function getImdbVotes(): ?string
    {
        return $this->imdbVotes;
    }

    public function setImdbVotes(?string $imdbVotes): static
    {
        $this->imdbVotes = $imdbVotes;

        return $this;
    }

    public function getOriginalTitle(): ?string
    {
        return $this->originalTitle;
    }

    public function setOriginalTitle(?string $originalTitle): static
    {
        $this->originalTitle = $originalTitle;

        return $this;
    }

    public function getProductionCountries(): ?string
    {
        return $this->productionCountries;
    }

    public function setProductionCountries(?string $productionCountries): static
    {
        $this->productionCountries = $productionCountries;

        return $this;
    }

    public function getProductionCompanies(): ?string
    {
        return $this->productionCompanies;
    }

    public function setProductionCompanies(?string $productionCompanies): static
    {
        $this->productionCompanies = $productionCompanies;

        return $this;
    }

    public function getSpokenLanguages(): ?string
    {
        return $this->spokenLanguages;
    }

    public function setSpokenLanguages(?string $spokenLanguages): static
    {
        $this->spokenLanguages = $spokenLanguages;

        return $this;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function setPosterPath(?string $posterPath): static
    {
        $this->posterPath = $posterPath;

        return $this;
    }
}

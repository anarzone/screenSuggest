<?php

namespace App\Entity;

use App\Repository\UserContentRecommendationsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserContentRecommendationsRepository::class)]
#[ORM\Table(name: 'user_content_recommendations')]
class UserContentRecommendation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\ManyToOne]
    private ?Content $content_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $recommended_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getContentId(): ?Content
    {
        return $this->content_id;
    }

    public function setContentId(?Content $content_id): static
    {
        $this->content_id = $content_id;

        return $this;
    }

    public function getRecommendedAt(): ?\DateTimeInterface
    {
        return $this->recommended_at;
    }

    public function setRecommendedAt(?\DateTimeInterface $recommended_at): static
    {
        $this->recommended_at = $recommended_at;

        return $this;
    }
}

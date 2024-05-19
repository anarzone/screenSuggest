<?php

namespace App\Domain\Content\ValueObject\Content;

class Rating
{
    private ?int $rating = null;

    public function __construct(?float $rating)
    {
        if ($rating < 0 || $rating > 10) {
            throw new \InvalidArgumentException('Rating must be a positive integer');
        }

        $this->rating = $rating;
    }

    public function getValue(): float
    {
        return $this->rating;
    }

    public static function fromInt(?float $value): self
    {
        return new self($value);
    }

    public function equals(?Rating $duration): bool
    {
        if ($duration === null) {
            return false;
        }

        return $this->rating === $duration->getValue();
    }

    public function __toString(): string
    {
        return (string) $this->rating;
    }
}
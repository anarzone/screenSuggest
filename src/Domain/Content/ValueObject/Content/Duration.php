<?php

namespace App\Domain\Content\ValueObject\Content;

use App\Domain\Common\ValueObject\IntegerValueInterface;

class Duration implements IntegerValueInterface
{
    private int $duration;

    public function __construct(int $durationInMinutes)
    {
        if ($durationInMinutes < 0 || $durationInMinutes > 6000) {
            throw new \InvalidArgumentException('Duration must be a positive integer');
        }

        $this->duration = $durationInMinutes;
    }

    public function value(): int
    {
        return $this->duration;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function equals(?Duration $duration): bool
    {
        if ($duration === null) {
            return false;
        }

        return $this->duration === $duration->value();
    }

    public function __toString(): string
    {
        return (string) $this->duration;
    }
}
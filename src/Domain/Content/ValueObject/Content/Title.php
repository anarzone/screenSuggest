<?php

namespace App\Domain\Content\ValueObject\Content;

use App\Domain\Common\ValueObject\StringValueInterface;

final readonly class Title implements StringValueInterface
{
    public function __construct(private string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(?Title $title): bool
    {
        if ($title === null) {
            return false;
        }

        return $this->getValue() === $title->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
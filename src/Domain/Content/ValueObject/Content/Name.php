<?php

namespace App\Domain\Content\ValueObject\Content;

use App\Domain\Common\ValueObject\StringValueInterface;

final readonly class Name implements StringValueInterface
{
    public function __construct(private string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): StringValueInterface
    {
        return new self($value);
    }

    public function equals(?Name $title): bool
    {
        if ($title === null) {
            return false;
        }

        return $this->getValue() === $title->getValue();
    }
}
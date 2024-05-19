<?php

namespace App\Domain\Content\ValueObject\Content;

use App\Domain\Common\Enum\ContentType;
use App\Domain\Common\ValueObject\StringValueInterface;

final readonly class Type implements StringValueInterface
{
    private string $value;

    public function __construct(string $value)
    {
        if(ContentType::tryFrom($value) === null) {
            throw new \InvalidArgumentException('Invalid content type');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(?Type $type): bool
    {
        if ($type === null) {
            return false;
        }

        return $this->getValue() === $type->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
<?php

namespace App\Domain\Common\ValueObject;

interface StringValueInterface
{
    public static function fromString(string $value): self;
    public function getValue(): string;
}
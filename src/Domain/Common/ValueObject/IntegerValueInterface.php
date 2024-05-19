<?php

namespace App\Domain\Common\ValueObject;

interface IntegerValueInterface
{
    public static function fromInt(int $value): self;
}
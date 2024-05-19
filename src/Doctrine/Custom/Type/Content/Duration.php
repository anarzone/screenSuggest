<?php

namespace App\Doctrine\Custom\Type\Content;

use App\Doctrine\Custom\Type\IntegerType;

class Duration extends IntegerType
{
    const TYPE_NAME = 'duration';
    public function getClassName(): string
    {
        return \App\Domain\Content\ValueObject\Content\Duration::class;
    }
}
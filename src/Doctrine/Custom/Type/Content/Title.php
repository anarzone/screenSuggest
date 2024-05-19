<?php

namespace App\Doctrine\Custom\Type\Content;

use App\Doctrine\Custom\Type\StringType;

class Title extends StringType
{
    const TYPE_NAME = 'title';

    public function getClassName(): string
    {
        return \App\Domain\Content\ValueObject\Content\Title::class;
    }
}
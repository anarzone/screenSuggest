<?php

namespace App\Doctrine\Custom\Type\Content;

use App\Doctrine\Custom\Type\StringType;

class Type extends StringType
{
    const TYPE_NAME = 'content_type';

    public function getClassName(): string
    {
        return \App\Domain\Content\ValueObject\Content\Type::class;
    }
}
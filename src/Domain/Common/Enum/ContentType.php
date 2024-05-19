<?php

namespace App\Domain\Common\Enum;

enum ContentType: string
{
    case MOVIE = 'movie';
    case TV_SHOW = 'tv_show';

    case DEFAULT = '';

    public static function values(): array{
        return array_map(fn($case) => $case->value, self::cases());
    }
}

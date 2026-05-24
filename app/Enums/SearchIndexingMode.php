<?php

namespace App\Enums;

enum SearchIndexingMode: string
{
    case Index = 'index';
    case Noindex = 'noindex';

    public function robotsMeta(): string
    {
        return match ($this) {
            self::Index => 'index,follow',
            self::Noindex => 'noindex,nofollow',
        };
    }

    public function robotsHeader(): ?string
    {
        return match ($this) {
            self::Index => null,
            self::Noindex => 'noindex, nofollow, noarchive',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Index => 'Maximaal indexeren',
            self::Noindex => 'Niets indexeren',
        };
    }
}

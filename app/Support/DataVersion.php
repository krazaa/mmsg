<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class DataVersion
{
    private const CACHE_KEY = 'application.data_version';

    public static function current(): int
    {
        return (int) Cache::rememberForever(
            self::CACHE_KEY,
            fn (): int => (int) floor(microtime(true) * 1000),
        );
    }

    public static function touch(): void
    {
        self::current();
        Cache::increment(self::CACHE_KEY);
    }
}

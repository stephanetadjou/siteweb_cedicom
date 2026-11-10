<?php

namespace App\Support;

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Cache;

/** Cache des contenus du site public, vidé à chaque modification dans le back office. */
class SiteCache
{
    public static function home(string $locale, callable $build): array
    {
        return Cache::rememberForever("site.home.$locale", $build);
    }

    public static function flush(): void
    {
        foreach (SetLocale::LOCALES as $locale) {
            Cache::forget("site.home.$locale");
        }
        SiteSettings::forget();
    }
}

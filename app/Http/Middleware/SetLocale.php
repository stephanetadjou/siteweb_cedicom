<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Langue du site gardée en session (français par défaut). */
class SetLocale
{
    public const LOCALES = ['fr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'fr');

        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : 'fr');

        return $next($request);
    }
}

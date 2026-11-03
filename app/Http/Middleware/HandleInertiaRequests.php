<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            ...parent::share($request),
            'locale' => $locale,
            'locales' => SetLocale::LOCALES,
            'translations' => fn () => $this->translations($locale),
            // Uniquement pour le back office : nom et e-mail du compte connecté.
            'auth' => fn () => ['user' => $request->user()?->only('name', 'email')],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }

    /** Dictionnaire JSON de la langue courante (lang/fr.json, lang/en.json), partagé avec React. */
    private function translations(string $locale): array
    {
        $path = lang_path($locale.'.json');

        return is_file($path) ? json_decode(file_get_contents($path), true) : [];
    }
}

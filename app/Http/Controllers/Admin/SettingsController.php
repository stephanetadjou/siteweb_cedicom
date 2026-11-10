<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Paramètres du site : coordonnées, réseaux sociaux, chiffres clés, accroche de l'accueil. */
class SettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings', [
            'fields' => collect(SiteSettings::FIELDS)->map(fn (array $f, string $name) => ['name' => $name] + $f)->values(),
            'values' => SiteSettings::forForm(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (SiteSettings::FIELDS as $name => $field) {
            $rules += match ($field['type']) {
                'email' => [$name => ['required', 'email', 'max:150']],
                'url' => [$name => ['nullable', 'url:https', 'max:255']],
                'number' => [$name => ['required', 'integer', 'min:0', 'max:10000000']],
                'text_tr' => ["$name.fr" => ['required', 'string', 'max:255'], "$name.en" => ['nullable', 'string', 'max:255']],
                'textarea_tr' => ["$name.fr" => ['required', 'string', 'max:2000'], "$name.en" => ['nullable', 'string', 'max:2000']],
                default => [$name => [$name === 'phone' ? 'required' : 'nullable', 'string', 'max:100']],
            };
        }

        $data = $request->validate($rules, ContentController::messages());

        SiteSettings::save(collect(SiteSettings::FIELDS)->mapWithKeys(fn (array $f, string $name) => [
            $name => $f['type'] === 'number' ? (int) $data[$name] : ($data[$name] ?? null),
        ])->all());

        return back()->with('success', 'Paramètres enregistrés.');
    }
}

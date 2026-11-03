<?php

namespace App\Models\Concerns;

/**
 * Champs traduits stockés en JSON : {"fr": "...", "en": "..."}.
 * Les champs listés dans $translatable doivent être castés en array.
 */
trait HasTranslations
{
    public function translate(string $field, ?string $locale = null): mixed
    {
        $values = $this->{$field} ?? [];
        $locale ??= app()->getLocale();

        return $values[$locale] ?? $values[config('app.fallback_locale')] ?? $values['fr'] ?? null;
    }

    protected function initializeHasTranslations(): void
    {
        foreach ($this->translatable as $field) {
            $this->mergeCasts([$field => 'array']);
        }
    }
}

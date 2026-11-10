<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Paramètres du site (coordonnées, réseaux sociaux, chiffres clés, accroche de l'accueil).
 * Valeur vide en base = valeur par défaut : textes de lang/{fr,en}.json, sinon DEFAULTS.
 */
class SiteSettings
{
    private const CACHE_KEY = 'site.settings';

    /** Champs modifiables : type, libellé, clé de traduction de repli (champs FR/EN). */
    public const FIELDS = [
        'phone' => ['type' => 'text', 'label' => 'Téléphone affiché', 'default' => '(+241) 01 77 57 48 / 49'],
        'phone_link' => ['type' => 'text', 'label' => 'Téléphone pour l’appel (format international, sans espace)', 'default' => '+24101775748'],
        'email' => ['type' => 'email', 'label' => 'E-mail de contact', 'default' => 'info@cedicomgroup.com'],
        'address' => ['type' => 'text_tr', 'label' => 'Adresse', 'lang' => 'contact.city'],
        'facebook' => ['type' => 'url', 'label' => 'Facebook', 'default' => ''],
        'instagram' => ['type' => 'url', 'label' => 'Instagram', 'default' => ''],
        'linkedin' => ['type' => 'url', 'label' => 'LinkedIn', 'default' => ''],
        'whatsapp' => ['type' => 'text', 'label' => 'WhatsApp (numéro international, ex. 24177000000)', 'default' => ''],
        'stat_warehouses' => ['type' => 'number', 'label' => 'Surface d’entrepôts (m²)', 'default' => 30000],
        'stat_trucks' => ['type' => 'number', 'label' => 'Camions de livraison', 'default' => 35],
        'stat_years' => ['type' => 'number', 'label' => 'Années d’expertise', 'default' => 30],
        'hero_title' => ['type' => 'text_tr', 'label' => 'Grand titre de l’accueil', 'lang' => 'hero.title'],
        'hero_lead' => ['type' => 'textarea_tr', 'label' => 'Paragraphe d’introduction de l’accueil', 'lang' => 'hero.lead'],
    ];

    /** Valeurs enregistrées (sans repli), en cache jusqu'à la prochaine modification. */
    public static function stored(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => Setting::pluck('value', 'key')->all());
    }

    /** Valeurs pour le site public, dans la langue demandée, avec repli. */
    public static function forLocale(string $locale): array
    {
        $stored = self::stored();

        return collect(self::FIELDS)->map(function (array $field, string $key) use ($stored, $locale) {
            $value = $stored[$key] ?? null;

            if (str_ends_with($field['type'], '_tr')) {
                return filled($value[$locale] ?? null) ? $value[$locale] : __($field['lang'], [], $locale);
            }

            return filled($value) ? $value : $field['default'];
        })->all();
    }

    /** Valeurs pour le formulaire du back office (les champs vides montrent le texte par défaut). */
    public static function forForm(): array
    {
        $stored = self::stored();

        return collect(self::FIELDS)->map(function (array $field, string $key) use ($stored) {
            $value = $stored[$key] ?? null;
            if (str_ends_with($field['type'], '_tr')) {
                return [
                    'fr' => filled($value['fr'] ?? null) ? $value['fr'] : __($field['lang'], [], 'fr'),
                    'en' => filled($value['en'] ?? null) ? $value['en'] : __($field['lang'], [], 'en'),
                ];
            }

            return filled($value) ? $value : $field['default'];
        })->all();
    }

    public static function save(array $values): void
    {
        foreach (array_keys(self::FIELDS) as $key) {
            if (array_key_exists($key, $values)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $values[$key]]);
            }
        }
        SiteCache::flush();
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

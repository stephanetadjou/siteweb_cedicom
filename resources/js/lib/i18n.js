import { usePage } from '@inertiajs/react';

/**
 * Traductions partagées par Laravel (lang/fr.json, lang/en.json).
 * t('brands.count', { count: 30 }) remplace :count.
 */
export function useT() {
    const { translations = {} } = usePage().props;

    return (key, replace = {}) =>
        Object.entries(replace).reduce(
            (text, [name, value]) => text.replaceAll(`:${name}`, value),
            translations[key] ?? key,
        );
}

export function useLocale() {
    const { locale, locales } = usePage().props;
    return { locale, locales };
}

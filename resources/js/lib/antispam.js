import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

let scriptPromise = null;

/** Charge le script Google reCAPTCHA v3 une seule fois, seulement sur les pages avec formulaire. */
function loadRecaptcha(siteKey) {
    scriptPromise ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(siteKey)}`;
        script.async = true;
        script.onload = () => window.grecaptcha.ready(resolve);
        script.onerror = reject;
        document.head.appendChild(script);
    });
    return scriptPromise;
}

/**
 * Champs anti-spam à joindre à un envoi : jeton horodaté signé (_t) et,
 * si reCAPTCHA est configuré, jeton reCAPTCHA v3 pour l'action donnée.
 * Usage : const antispam = useAntiSpam('contact'); … const extra = await antispam();
 */
export function useAntiSpam(action) {
    const { token, recaptchaKey } = usePage().props.antispam ?? {};

    useEffect(() => {
        if (recaptchaKey) loadRecaptcha(recaptchaKey).catch(() => {});
    }, [recaptchaKey]);

    const collect = async () => {
        const fields = { _t: token };
        if (recaptchaKey) {
            try {
                await loadRecaptcha(recaptchaKey);
                fields.recaptcha_token = await window.grecaptcha.execute(recaptchaKey, { action });
            } catch {
                // Script bloqué : l'envoi part sans jeton reCAPTCHA et sera refusé côté serveur.
            }
        }
        return fields;
    };
    collect.enabled = Boolean(recaptchaKey); // pour afficher la mention reCAPTCHA
    return collect;
}

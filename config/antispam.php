<?php

/*
| Protection anti-spam des formulaires (contact, partenariat, connexion admin).
| Voir DEPLOIEMENT_GITHUB.md, section « Protection anti-spam ».
*/

return [

    // Google reCAPTCHA v3 (invisible). Sans les deux clés, la vérification est désactivée (local, tests).
    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),
        'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    ],

    // Délai minimum entre l'affichage du formulaire et l'envoi (un robot remplit instantanément),
    // et durée de validité du jeton signé glissé dans la page.
    'min_seconds' => (int) env('ANTISPAM_MIN_SECONDS', 3),
    'max_age_seconds' => 60 * 60 * 24,

    // Envois acceptés par adresse IP et par formulaire.
    'form_attempts' => 3,
    'form_decay_minutes' => 10,

    // Plafond global d'alertes e-mail par heure (au-delà, les demandes restent dans le back office).
    'alerts_per_hour' => (int) env('ANTISPAM_ALERTS_PER_HOUR', 30),

    // Vérifie que le domaine de l'adresse e-mail existe (désactivé dans les tests).
    'email_dns' => (bool) env('ANTISPAM_EMAIL_DNS', true),

];

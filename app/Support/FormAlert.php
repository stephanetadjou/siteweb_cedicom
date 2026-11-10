<?php

namespace App\Support;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * Alerte e-mail d'un formulaire, envoyée uniquement à MAIL_NOTIFY_TO (jamais au visiteur,
 * dont l'adresse n'est mise qu'en Reply-To). La demande est déjà enregistrée : une panne SMTP
 * ou le plafond horaire atteint ne font jamais échouer le formulaire.
 */
class FormAlert
{
    public static function send(Mailable $mail): void
    {
        $allowed = RateLimiter::attempt('form-alerts', config('antispam.alerts_per_hour'), fn () => true, 3600);
        if (! $allowed) {
            Log::warning('Plafond horaire des alertes de formulaire atteint : e-mail non envoyé', ['mail' => $mail::class]);

            return;
        }

        try {
            Mail::to(config('mail.notify_to'))->locale('fr')->send($mail);
        } catch (Throwable $e) {
            Log::error('Échec de l\'envoi de l\'e-mail du formulaire', ['exception' => $e]);
        }
    }
}

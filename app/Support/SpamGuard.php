<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Détection des envois automatiques : champ piège, jeton horodaté signé
 * (temps de remplissage minimum) et score Google reCAPTCHA v3.
 */
class SpamGuard
{
    /** Jeton signé glissé dans la page : date d'affichage du formulaire. */
    public function token(?int $timestamp = null): string
    {
        return Crypt::encryptString((string) ($timestamp ?? time()));
    }

    public function recaptchaEnabled(): bool
    {
        return filled(config('antispam.recaptcha.site_key')) && filled(config('antispam.recaptcha.secret_key'));
    }

    /** Raison du rejet d'un formulaire public, ou null s'il semble envoyé par une personne. */
    public function formRejection(Request $request, string $action): ?string
    {
        if (filled($request->input('website'))) {
            return 'champ piège rempli';
        }

        try {
            $elapsed = time() - (int) Crypt::decryptString((string) $request->input('_t'));
        } catch (DecryptException) {
            return 'jeton absent ou invalide';
        }

        if ($elapsed < config('antispam.min_seconds')) {
            return "envoyé trop vite ({$elapsed} s)";
        }
        if ($elapsed > config('antispam.max_age_seconds')) {
            return 'jeton expiré';
        }

        return $this->recaptchaRejection($request, $action);
    }

    /** Raison du rejet par reCAPTCHA, ou null (aussi quand reCAPTCHA n'est pas configuré). */
    public function recaptchaRejection(Request $request, string $action): ?string
    {
        if (! $this->recaptchaEnabled()) {
            return null;
        }

        $token = (string) $request->input('recaptcha_token');
        if ($token === '') {
            return 'jeton reCAPTCHA absent';
        }

        try {
            $result = Http::asForm()->timeout(5)->post(config('antispam.recaptcha.verify_url'), [
                'secret' => config('antispam.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ])->json();
        } catch (Throwable $e) {
            // Google injoignable : on laisse passer (le piège et les limites de débit restent actifs).
            Log::warning('reCAPTCHA injoignable, vérification ignorée', ['exception' => $e->getMessage()]);

            return null;
        }

        if (! ($result['success'] ?? false)) {
            return 'reCAPTCHA refusé';
        }
        if (($result['action'] ?? null) !== $action) {
            return 'action reCAPTCHA inattendue';
        }
        if (($result['score'] ?? 0) < config('antispam.recaptcha.min_score')) {
            return 'score reCAPTCHA trop bas ('.($result['score'] ?? 0).')';
        }

        return null;
    }
}

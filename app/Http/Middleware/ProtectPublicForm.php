<?php

namespace App\Http\Middleware;

use App\Support\SpamGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejet silencieux des envois automatiques : le robot reçoit la même réponse
 * qu'en cas de succès, mais rien n'est enregistré ni envoyé.
 * Usage : ->middleware('antispam:contact').
 */
class ProtectPublicForm
{
    private const SUCCESS_MESSAGES = [
        'contact' => 'form.contact_success',
        'partnership' => 'form.partner_success',
    ];

    public function __construct(private SpamGuard $guard) {}

    public function handle(Request $request, Closure $next, string $action): Response
    {
        if ($reason = $this->guard->formRejection($request, $action)) {
            Log::warning('Formulaire rejeté (anti-spam)', ['form' => $action, 'raison' => $reason, 'ip' => $request->ip()]);

            return back()->with('success', __(self::SUCCESS_MESSAGES[$action]));
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartnershipRequest;
use App\Mail\NewPartnershipRequest;
use App\Models\PartnershipRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PartnershipController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Site/Partner', [
            'sectors' => PartnershipRequest::SECTORS,
        ]);
    }

    public function store(StorePartnershipRequest $request): RedirectResponse
    {
        $demande = PartnershipRequest::create($request->safe()->except('website') + [
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        // La demande est enregistrée : une panne SMTP ne doit pas faire échouer le formulaire.
        try {
            Mail::to(config('mail.notify_to'))->locale('fr')->send(new NewPartnershipRequest($demande));
        } catch (Throwable $e) {
            Log::error('Échec de l\'envoi de l\'e-mail du formulaire', ['exception' => $e]);
        }

        return back()->with('success', __('form.partner_success'));
    }
}

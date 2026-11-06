<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Site/Contact', [
            'subjects' => ContactMessage::SUBJECTS,
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $contact = ContactMessage::create($request->safe()->except('website') + [
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        // La demande est enregistrée : une panne SMTP ne doit pas faire échouer le formulaire.
        try {
            Mail::to(config('mail.notify_to'))->locale('fr')->send(new NewContactMessage($contact));
        } catch (Throwable $e) {
            Log::error('Échec de l\'envoi de l\'e-mail du formulaire', ['exception' => $e]);
        }

        return back()->with('success', __('form.contact_success'));
    }
}

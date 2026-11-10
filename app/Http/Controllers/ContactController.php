<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use App\Support\FormAlert;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

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
        $contact = ContactMessage::create($request->validated() + [
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        FormAlert::send(new NewContactMessage($contact));

        return back()->with('success', __('form.contact_success'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
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
        ContactMessage::create($request->safe()->except('website') + [
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', __('form.contact_success'));
    }
}

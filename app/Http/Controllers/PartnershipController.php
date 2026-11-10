<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartnershipRequest;
use App\Mail\NewPartnershipRequest;
use App\Models\PartnershipRequest;
use App\Support\FormAlert;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

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
        $demande = PartnershipRequest::create($request->validated() + [
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        FormAlert::send(new NewPartnershipRequest($demande));

        return back()->with('success', __('form.partner_success'));
    }
}

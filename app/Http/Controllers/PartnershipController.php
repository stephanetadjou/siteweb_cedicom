<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartnershipRequest;
use App\Models\PartnershipRequest;
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
        PartnershipRequest::create($request->safe()->except('website') + [
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', __('form.partner_success'));
    }
}

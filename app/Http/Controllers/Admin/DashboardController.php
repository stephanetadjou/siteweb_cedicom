<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\PartnershipRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'partnerships' => ['new' => PartnershipRequest::whereNull('processed_at')->count(), 'total' => PartnershipRequest::count()],
                'messages' => ['new' => ContactMessage::whereNull('read_at')->count(), 'total' => ContactMessage::count()],
            ],
            'latestPartnerships' => PartnershipRequest::latest()->orderByDesc('id')->limit(5)->get()
                ->map(fn (PartnershipRequest $r) => [
                    'id' => $r->id,
                    'title' => $r->company,
                    'subtitle' => $r->country.' · '.$r->email,
                    'date' => $r->created_at->format('d/m/Y H:i'),
                    'done' => (bool) $r->processed_at,
                ]),
            'latestMessages' => ContactMessage::latest()->orderByDesc('id')->limit(5)->get()
                ->map(fn (ContactMessage $m) => [
                    'id' => $m->id,
                    'title' => $m->name,
                    'subtitle' => __('form.subjects.'.$m->subject, [], 'fr').' · '.$m->email,
                    'date' => $m->created_at->format('d/m/Y H:i'),
                    'done' => (bool) $m->read_at,
                ]),
        ]);
    }
}

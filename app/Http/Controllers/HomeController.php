<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\PartnerBrand;
use App\Models\PartnershipRequest;
use App\Models\Pole;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Site/Home', [
            'poles' => Pole::orderBy('sort')->get()->map->toLocalizedArray(),
            'brands' => PartnerBrand::orderBy('sort')->get()
                ->map(fn (PartnerBrand $b) => ['id' => $b->id, 'name' => $b->name, 'category' => $b->category, 'logo' => asset($b->logo)]),
            'brandCategories' => PartnerBrand::CATEGORIES,
            'divisions' => Division::with('enseignes')->orderBy('sort')->get()->map->toLocalizedArray(),
            'sectors' => PartnershipRequest::SECTORS,
        ]);
    }
}

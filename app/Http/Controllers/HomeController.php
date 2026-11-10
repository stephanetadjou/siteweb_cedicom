<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\HeroSlide;
use App\Models\PartnerBrand;
use App\Models\PartnershipRequest;
use App\Models\Pole;
use App\Support\SiteCache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        // Contenus gérés dans le back office, en cache jusqu'à la prochaine modification.
        $content = SiteCache::home(app()->getLocale(), fn () => [
            'heroSlides' => HeroSlide::where('visible', true)->orderBy('sort')->orderBy('id')->get()->map->toLocalizedArray()->all(),
            'poles' => Pole::orderBy('sort')->orderBy('id')->get()->map->toLocalizedArray()->all(),
            'brands' => PartnerBrand::where('visible', true)->orderByDesc('featured')->orderBy('sort')->orderBy('id')->get()
                ->map(fn (PartnerBrand $b) => [
                    'id' => $b->id, 'name' => $b->name, 'category' => $b->category, 'logo' => asset($b->logo), 'featured' => $b->featured,
                ])->all(),
            'divisions' => Division::with('enseignes')->orderBy('sort')->orderBy('id')->get()->map->toLocalizedArray()->all(),
        ]);

        return Inertia::render('Site/Home', $content + [
            'brandCategories' => PartnerBrand::CATEGORIES,
            'sectors' => PartnershipRequest::SECTORS,
        ]);
    }
}

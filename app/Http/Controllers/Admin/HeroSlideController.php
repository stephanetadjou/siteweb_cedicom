<?php

namespace App\Http\Controllers\Admin;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Model;

class HeroSlideController extends ContentController
{
    protected function model(): string
    {
        return HeroSlide::class;
    }

    protected function slug(): string
    {
        return 'diaporama';
    }

    protected function labels(): array
    {
        return [
            'title' => 'Diaporama de l’accueil',
            'singular' => 'diapositive',
            'help' => 'Photos qui défilent en haut de l’accueil, dans l’ordre de la liste. Conseil : photo paysage d’au moins 1600 px de large ; elle est réduite automatiquement à 1920 px.',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'image', 'label' => 'Photo', 'type' => 'image', 'required' => true, 'width' => 1920],
            ['name' => 'alt', 'label' => 'Description de la photo (accessibilité)', 'type' => 'text_tr', 'required' => true],
            ['name' => 'veil', 'label' => 'Voile clair derrière le texte', 'type' => 'select', 'required' => true, 'default' => 'light', 'options' => [
                ['value' => 'light', 'label' => 'Léger (photo bien visible)'],
                ['value' => 'strong', 'label' => 'Fort (photo estompée)'],
            ]],
            ['name' => 'position', 'label' => 'Cadrage de la photo', 'type' => 'text', 'default' => '50% 50%', 'help' => 'Position horizontale puis verticale : 50% 50% = centre, 70% 60% = un peu à droite et en bas.'],
            ['name' => 'visible', 'label' => 'Afficher sur le site', 'type' => 'checkbox'],
        ];
    }

    protected function row(Model $item): array
    {
        return [
            'title' => $item->translate('alt', 'fr'),
            'subtitle' => $item->veil === 'strong' ? 'Voile fort' : 'Voile léger',
            'image' => asset($item->image),
        ];
    }

    protected function prepare(Model $item, array $data): array
    {
        $data['position'] = $data['position'] ?: '50% 50%';

        return $item->exists ? $data : $data + ['sort' => (HeroSlide::max('sort') ?? -1) + 1];
    }
}

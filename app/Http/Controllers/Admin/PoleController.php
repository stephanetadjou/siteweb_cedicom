<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pole;
use Illuminate\Database\Eloquent\Model;

class PoleController extends ContentController
{
    protected function model(): string
    {
        return Pole::class;
    }

    protected function slug(): string
    {
        return 'poles';
    }

    protected function labels(): array
    {
        return [
            'title' => "Pôles d'activité",
            'singular' => 'pôle',
            'help' => "Cartes « Nos pôles d'activité » de l'accueil, dans l'ordre de la liste.",
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Titre', 'type' => 'text_tr', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea_tr', 'required' => true],
            ['name' => 'tags', 'label' => 'Ligne de marques en bas de carte', 'type' => 'text_tr', 'help' => 'Ex. : PAUL · KFC · Pizza Hut · +8 enseignes'],
            ['name' => 'image', 'label' => 'Photo (format bandeau, 2,4 × plus large que haute)', 'type' => 'image', 'required' => true, 'width' => 1440],
            ['name' => 'image_position', 'label' => 'Cadrage de la photo', 'type' => 'text', 'default' => 'center', 'help' => 'Ex. : center, center 30%, 70% 60%.'],
            ['name' => 'anchor', 'label' => 'Lien de la carte', 'type' => 'select', 'required' => true, 'options' => [
                ['value' => 'marques', 'label' => 'Section Marques partenaires'],
                ['value' => 'divisions', 'label' => 'Section Divisions'],
                ['value' => 'logistique', 'label' => 'Section Logistique'],
                ['value' => 'partenariat', 'label' => 'Section Devenir partenaire'],
            ]],
        ];
    }

    protected function row(Model $item): array
    {
        return ['title' => $item->translate('title', 'fr'), 'subtitle' => $item->translate('tags', 'fr'), 'image' => asset($item->image)];
    }

    protected function prepare(Model $item, array $data): array
    {
        $data['image_position'] = $data['image_position'] ?: 'center';

        return $item->exists ? $data : $data + ['logos' => [], 'round_logos' => false, 'sort' => (Pole::max('sort') ?? -1) + 1];
    }
}

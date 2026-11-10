<?php

namespace App\Http\Controllers\Admin;

use App\Models\Division;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DivisionController extends ContentController
{
    protected function model(): string
    {
        return Division::class;
    }

    protected function slug(): string
    {
        return 'divisions';
    }

    protected function labels(): array
    {
        return [
            'title' => 'Divisions',
            'singular' => 'division',
            'help' => 'Section « Divisions » de l’accueil. Les logos de chaque division se gèrent dans la rubrique Enseignes.',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Titre', 'type' => 'text_tr', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea_tr', 'required' => true],
            ['name' => 'image', 'label' => 'Photo principale', 'type' => 'image', 'required' => true, 'width' => 1600],
            ['name' => 'image_alt', 'label' => 'Description de la photo (accessibilité)', 'type' => 'text_tr', 'required' => true],
            ['name' => 'categories', 'label' => "Catégories d'activité", 'type' => 'lines_tr', 'help' => 'Une catégorie par ligne.'],
        ];
    }

    protected function row(Model $item): array
    {
        return [
            'title' => $item->translate('title', 'fr'),
            'subtitle' => $item->enseignes()->count().' enseigne(s)',
            'image' => asset($item->image),
        ];
    }

    protected function prepare(Model $item, array $data): array
    {
        if ($item->exists) {
            return $data;
        }

        $base = Str::slug($data['title']['fr']) ?: 'division';
        $slug = $base;
        for ($i = 2; Division::where('slug', $slug)->exists(); $i++) {
            $slug = "$base-$i";
        }

        return $data + ['slug' => $slug, 'sort' => (Division::max('sort') ?? -1) + 1];
    }
}

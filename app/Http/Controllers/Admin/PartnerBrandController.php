<?php

namespace App\Http\Controllers\Admin;

use App\Models\PartnerBrand;
use Illuminate\Database\Eloquent\Model;

class PartnerBrandController extends ContentController
{
    protected function model(): string
    {
        return PartnerBrand::class;
    }

    protected function slug(): string
    {
        return 'marques';
    }

    protected function labels(): array
    {
        return [
            'title' => 'Marques partenaires',
            'singular' => 'marque',
            'help' => 'Mur des marques de l’accueil. Les marques « mises en avant » apparaissent en premier, avec un badge. Une marque masquée reste ici mais n’apparaît plus sur le site.',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Nom', 'type' => 'text', 'required' => true],
            ['name' => 'category', 'label' => 'Catégorie', 'type' => 'select', 'required' => true, 'options' => collect(PartnerBrand::CATEGORIES)
                ->map(fn (string $c) => ['value' => $c, 'label' => __('brands.category.'.$c, [], 'fr')])->all()],
            ['name' => 'logo', 'label' => 'Logo', 'type' => 'image', 'required' => true, 'width' => 600],
            ['name' => 'featured', 'label' => 'Mettre en avant (partenaire majeur)', 'type' => 'checkbox', 'default' => false],
            ['name' => 'visible', 'label' => 'Afficher sur le site', 'type' => 'checkbox'],
        ];
    }

    protected function row(Model $item): array
    {
        return ['title' => $item->name, 'subtitle' => __('brands.category.'.$item->category, [], 'fr'), 'image' => asset($item->logo), 'contain' => true];
    }

    protected function prepare(Model $item, array $data): array
    {
        return $item->exists ? $data : $data + ['sort' => (PartnerBrand::max('sort') ?? -1) + 1];
    }
}

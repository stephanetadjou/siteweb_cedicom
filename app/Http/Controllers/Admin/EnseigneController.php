<?php

namespace App\Http\Controllers\Admin;

use App\Models\Division;
use App\Models\Enseigne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EnseigneController extends ContentController
{
    protected function model(): string
    {
        return Enseigne::class;
    }

    protected function slug(): string
    {
        return 'enseignes';
    }

    protected function labels(): array
    {
        return ['title' => 'Enseignes', 'singular' => 'enseigne', 'help' => 'Logos affichés sous chaque division : franchises internationales et concepts propres. L’ordre s’applique à l’intérieur de chaque groupe.'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Nom', 'type' => 'text', 'required' => true],
            ['name' => 'division_id', 'label' => 'Division', 'type' => 'select', 'required' => true, 'options' => Division::orderBy('sort')->get()
                ->map(fn (Division $d) => ['value' => $d->id, 'label' => $d->translate('title', 'fr')])->all()],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => true, 'default' => 'franchise', 'options' => [
                ['value' => 'franchise', 'label' => 'Franchise internationale'],
                ['value' => 'concept', 'label' => 'Concept propre'],
            ]],
            ['name' => 'logo', 'label' => 'Logo', 'type' => 'image', 'required' => true, 'width' => 600],
        ];
    }

    protected function query(): Builder
    {
        return Enseigne::query()->with('division')
            ->orderBy(Division::select('sort')->whereColumn('divisions.id', 'enseignes.division_id'))
            ->orderBy('type')->orderBy('sort')->orderBy('id');
    }

    protected function row(Model $item): array
    {
        return [
            'title' => $item->name,
            'subtitle' => $item->division?->translate('title', 'fr').' · '.($item->type === 'franchise' ? 'Franchise' : 'Concept propre'),
            'image' => asset($item->logo),
            'contain' => true,
        ];
    }

    protected function prepare(Model $item, array $data): array
    {
        return $item->exists ? $data : $data + [
            'sort' => (Enseigne::where('division_id', $data['division_id'])->where('type', $data['type'])->max('sort') ?? -1) + 1,
        ];
    }
}

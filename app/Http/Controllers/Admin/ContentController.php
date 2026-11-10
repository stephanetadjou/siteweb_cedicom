<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ImageUpload;
use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion générique d'un contenu du site (liste, ajout, modification, ordre, suppression).
 * Chaque sous-classe décrit ses champs ; l'écran React Admin/Content/Form les affiche.
 *
 * Types de champ : text, textarea, number, select, checkbox, image (option width = largeur max),
 * text_tr / textarea_tr (FR + EN) et lines_tr (liste FR + EN, une valeur par ligne).
 */
abstract class ContentController extends Controller
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

    /** Segment d'URL sous /admin/contenus (ex. « marques ») */
    abstract protected function slug(): string;

    /** title (pluriel), singular, help (facultatif) */
    abstract protected function labels(): array;

    abstract protected function fields(): array;

    /** Ligne de la liste : title, subtitle, image, contain (logo) */
    abstract protected function row(Model $item): array;

    /** Ordre d'affichage, identique à celui du site */
    protected function query(): Builder
    {
        return ($this->model())::query()->orderBy('sort')->orderBy('id');
    }

    /** Complète les données avant enregistrement (ex. slug, valeurs par défaut) */
    protected function prepare(Model $item, array $data): array
    {
        return $data;
    }

    public function index(): Response
    {
        $fields = collect($this->fields());

        return Inertia::render('Admin/Content/Index', [
            'labels' => $this->labels(),
            'baseUrl' => $this->baseUrl(),
            'items' => $this->query()->get()->map(fn (Model $item) => $this->row($item) + [
                'id' => $item->getKey(),
                'sort' => $item->sort,
                'visible' => $fields->contains('name', 'visible') ? (bool) $item->visible : null,
                'featured' => $fields->contains('name', 'featured') ? (bool) $item->featured : null,
            ]),
        ]);
    }

    public function create(): Response
    {
        return $this->form(new ($this->model()));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, new ($this->model()));

        return redirect($this->baseUrl())->with('success', 'Élément ajouté.');
    }

    public function edit(int $id): Response
    {
        return $this->form(($this->model())::findOrFail($id));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->save($request, ($this->model())::findOrFail($id));

        return redirect($this->baseUrl())->with('success', 'Modifications enregistrées.');
    }

    /** Monte ou descend un élément d'un cran (échange d'ordre avec son voisin dans la liste). */
    public function move(int $id, string $direction): RedirectResponse
    {
        $items = $this->query()->get()->values();
        $index = $items->search(fn (Model $item) => $item->getKey() === $id);
        abort_if($index === false, 404);

        $neighbor = $items->get($direction === 'monter' ? $index - 1 : $index + 1);
        if ($neighbor) {
            // Renumérote toute la liste pour éviter les ordres en double, puis échange les deux.
            $ordered = $items->all();
            [$ordered[$index], $ordered[$items->search($neighbor)]] = [$neighbor, $items[$index]];
            foreach (array_values($ordered) as $position => $item) {
                $item->forceFill(['sort' => $position])->save();
            }
            SiteCache::flush();
        }

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = ($this->model())::findOrFail($id);
        $images = collect($this->fields())->where('type', 'image')->map(fn ($f) => $item->{$f['name']});
        $item->delete();
        $images->each(fn (?string $path) => ImageUpload::delete($path));
        SiteCache::flush();

        return redirect($this->baseUrl())->with('success', 'Élément supprimé.');
    }

    private function baseUrl(): string
    {
        return '/admin/contenus/'.$this->slug();
    }

    private function form(Model $item): Response
    {
        return Inertia::render('Admin/Content/Form', [
            'labels' => $this->labels(),
            'baseUrl' => $this->baseUrl(),
            'item' => $item->exists ? ['id' => $item->getKey(), 'title' => $this->row($item)['title']] : null,
            'fields' => $this->fields(),
            'values' => collect($this->fields())->mapWithKeys(fn (array $f) => [$f['name'] => $this->formValue($item, $f)]),
        ]);
    }

    private function formValue(Model $item, array $field): mixed
    {
        $value = $item->{$field['name']};

        return match ($field['type']) {
            'text_tr', 'textarea_tr' => ['fr' => $value['fr'] ?? '', 'en' => $value['en'] ?? ''],
            'lines_tr' => ['fr' => implode("\n", $value['fr'] ?? []), 'en' => implode("\n", $value['en'] ?? [])],
            'image' => $value ? asset($value) : null,
            'checkbox' => $item->exists ? (bool) $value : ($field['default'] ?? true),
            'number' => $value ?? 0,
            default => $value ?? ($field['default'] ?? ''),
        };
    }

    private function save(Request $request, Model $item): void
    {
        $fields = collect($this->fields());
        $data = $request->validate($this->rules($fields, $item), $this->messages(), $this->attributes($fields));

        $values = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            $values[$name] = match ($field['type']) {
                'text_tr', 'textarea_tr' => ['fr' => $data[$name]['fr'] ?? null, 'en' => ($data[$name]['en'] ?? null) ?: null],
                'lines_tr' => ['fr' => $this->lines($data[$name]['fr'] ?? ''), 'en' => $this->lines($data[$name]['en'] ?? '')],
                'checkbox' => $request->boolean($name),
                'number' => (int) ($data[$name] ?? 0),
                'image' => $request->hasFile($name)
                    ? ImageUpload::store($request->file($name), $this->slug(), $field['width'] ?? 1920)
                    : $item->{$name},
                default => $data[$name] ?? null,
            };
        }

        $previousImages = $fields->where('type', 'image')->mapWithKeys(fn ($f) => [$f['name'] => $item->{$f['name']}]);
        $item->fill($this->prepare($item, $values))->save();

        // Supprime les anciennes images remplacées (seulement celles envoyées par le back office).
        $previousImages->each(fn (?string $old, string $name) => $old !== $item->{$name} ? ImageUpload::delete($old) : null);
        SiteCache::flush();
    }

    private function rules(Collection $fields, Model $item): array
    {
        $rules = [];
        foreach ($fields as $f) {
            $required = ($f['required'] ?? false) ? 'required' : 'nullable';
            $name = $f['name'];
            match ($f['type']) {
                'text' => $rules[$name] = [$required, 'string', 'max:255'],
                'textarea' => $rules[$name] = [$required, 'string', 'max:5000'],
                'number' => $rules[$name] = ['nullable', 'integer', 'min:0', 'max:9999'],
                'checkbox' => $rules[$name] = ['nullable', 'boolean'],
                'select' => $rules[$name] = [$required, Rule::in(collect($f['options'])->pluck('value')->map(fn ($v) => (string) $v)->all())],
                'image' => $rules[$name] = [$required === 'required' && ! $item->{$name} ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=8000,max_height=8000'],
                'text_tr' => $rules += [$name => ['array'], "$name.fr" => [$required, 'string', 'max:255'], "$name.en" => ['nullable', 'string', 'max:255']],
                'textarea_tr' => $rules += [$name => ['array'], "$name.fr" => [$required, 'string', 'max:5000'], "$name.en" => ['nullable', 'string', 'max:5000']],
                'lines_tr' => $rules += [$name => ['array'], "$name.fr" => ['nullable', 'string', 'max:3000'], "$name.en" => ['nullable', 'string', 'max:3000']],
            };
        }

        return $rules;
    }

    public static function messages(): array
    {
        return [
            'required' => 'Ce champ est obligatoire.',
            'image' => 'Le fichier doit être une image.',
            'mimes' => 'Formats acceptés : JPG, PNG ou WebP.',
            'max.file' => "L'image ne doit pas dépasser 5 Mo.",
            'dimensions' => 'Image trop grande (8000 px au maximum).',
            'max.string' => 'Texte trop long (:max caractères au maximum).',
            'max.numeric' => 'Valeur trop grande.',
            'min.numeric' => 'La valeur doit être positive.',
            'integer' => 'Nombre entier attendu.',
            'in' => 'Valeur non valide.',
            'email' => 'Adresse e-mail non valide.',
            'url' => 'Adresse web non valide (commencez par https://).',
        ];
    }

    private function attributes(Collection $fields): array
    {
        return $fields->flatMap(fn ($f) => [
            $f['name'] => $f['label'],
            $f['name'].'.fr' => $f['label'].' (FR)',
            $f['name'].'.en' => $f['label'].' (EN)',
        ])->all();
    }

    private function lines(?string $text): array
    {
        return collect(preg_split('/\R/', (string) $text))->map(fn ($l) => trim($l))->filter()->values()->all();
    }
}

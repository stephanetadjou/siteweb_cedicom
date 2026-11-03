<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Demandes reçues par le site (partenariats, messages) : liste filtrable, détail,
 * statut nouveau / traité et export CSV. Les sous-classes décrivent leur modèle.
 */
abstract class InboxController extends Controller
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

    /** Segment d'URL sous /admin (ex. « partenariats ») */
    abstract protected function slug(): string;

    /** title, singular, done (libellé du statut traité), empty */
    abstract protected function labels(): array;

    /** Colonne datée qui marque la demande comme traitée */
    abstract protected function statusColumn(): string;

    /** @return list<string> colonnes recherchées */
    abstract protected function searchable(): array;

    /** Ligne de la liste : title, subtitle, meta */
    abstract protected function row(Model $item): array;

    /** @return array<string, ?string> libellé => valeur, pour le détail et l'export CSV */
    abstract protected function details(Model $item): array;

    public function index(Request $request): Response
    {
        $items = $this->filtered($request)->paginate(20)->withQueryString();

        return Inertia::render('Admin/Inbox/Index', [
            'labels' => $this->labels(),
            'baseUrl' => '/admin/'.$this->slug(),
            'filters' => ['q' => (string) $request->query('q', ''), 'status' => $this->status($request)],
            'items' => $items->through(fn (Model $item) => $this->row($item) + [
                'id' => $item->getKey(),
                'date' => $item->created_at->format('d/m/Y H:i'),
                'done' => (bool) $item->{$this->statusColumn()},
            ]),
        ]);
    }

    public function show(int $id): Response
    {
        $item = ($this->model())::findOrFail($id);

        return Inertia::render('Admin/Inbox/Show', [
            'labels' => $this->labels(),
            'baseUrl' => '/admin/'.$this->slug(),
            'item' => [
                'id' => $item->getKey(),
                'title' => $this->row($item)['title'],
                'date' => $item->created_at->format('d/m/Y à H:i'),
                'done' => (bool) $item->{$this->statusColumn()},
                'doneAt' => $item->{$this->statusColumn()}?->format('d/m/Y à H:i'),
                'email' => $item->email,
                'details' => collect($this->details($item))
                    ->map(fn (?string $value, string $label) => ['label' => $label, 'value' => $value])
                    ->values(),
            ],
        ]);
    }

    /** Bascule nouveau ↔ traité */
    public function toggle(int $id): RedirectResponse
    {
        $item = ($this->model())::findOrFail($id);
        $column = $this->statusColumn();
        $item->forceFill([$column => $item->{$column} ? null : now()])->save();

        return back()->with('success', $item->{$column} ? 'Marqué comme traité.' : 'Remis en « nouveau ».');
    }

    /** Export CSV (séparateur « ; » et BOM UTF-8 pour Excel), avec les filtres de la liste. */
    public function export(Request $request): StreamedResponse
    {
        $items = $this->filtered($request)->get();
        $headers = array_keys($this->details(new ($this->model())));

        return response()->streamDownload(function () use ($items, $headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['N°', 'Reçu le', 'Statut', ...$headers], ';');
            foreach ($items as $item) {
                fputcsv($out, [
                    $item->getKey(),
                    $item->created_at->format('d/m/Y H:i'),
                    $item->{$this->statusColumn()} ? $this->labels()['done'] : 'Nouveau',
                    ...array_values($this->details($item)),
                ], ';');
            }
            fclose($out);
        }, $this->slug().'-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function status(Request $request): string
    {
        $status = $request->query('status');

        return in_array($status, ['new', 'done'], true) ? $status : 'all';
    }

    private function filtered(Request $request): Builder
    {
        $q = trim((string) $request->query('q', ''));
        $status = $this->status($request);
        $column = $this->statusColumn();

        return ($this->model())::query()
            ->when($q !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($q) {
                foreach ($this->searchable() as $searchable) {
                    $query->orWhere($searchable, 'like', '%'.addcslashes($q, '%_\\').'%');
                }
            }))
            ->when($status === 'new', fn (Builder $query) => $query->whereNull($column))
            ->when($status === 'done', fn (Builder $query) => $query->whereNotNull($column))
            ->latest()
            ->orderByDesc('id');
    }
}

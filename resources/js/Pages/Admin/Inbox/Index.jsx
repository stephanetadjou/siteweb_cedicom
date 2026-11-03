import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout, { StatusBadge, btnOutline, btnPrimary, card } from '@/Components/Admin/AdminLayout';
import Icon from '@/Components/Site/Icon';

const STATUSES = [
    ['all', 'Toutes'],
    ['new', 'Nouvelles'],
    ['done', 'Traitées'],
];

export default function InboxIndex({ labels, baseUrl, filters, items }) {
    const [q, setQ] = useState(filters.q);
    const query = (extra) => ({ q: q || undefined, status: filters.status === 'all' ? undefined : filters.status, ...extra });
    const visit = (extra) => router.get(baseUrl, query(extra), { preserveState: true, replace: true });
    const exportUrl = `${baseUrl}/export?${new URLSearchParams(Object.entries(query({})).filter(([, v]) => v)).toString()}`;

    return (
        <AdminLayout
            title={labels.title}
            actions={
                <a href={exportUrl} className={btnOutline}>
                    <Icon name="download" className="size-5" /> <span className="hidden sm:inline">Export CSV</span>
                </a>
            }
        >
            <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        visit({ page: undefined });
                    }}
                    className="flex w-full max-w-md gap-2"
                    role="search"
                >
                    <label className="relative flex-1">
                        <span className="sr-only">Rechercher</span>
                        <Icon name="search" className="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-muted" />
                        <input
                            type="search"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="Nom, e-mail, pays, message…"
                            className="w-full rounded-lg border border-line bg-white py-2.5 pr-3 pl-10 text-[16px] focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25"
                        />
                    </label>
                    <button type="submit" className={btnPrimary}>Rechercher</button>
                </form>

                <div className="flex gap-1 rounded-lg border border-line bg-white p-1" role="group" aria-label="Filtrer par statut">
                    {STATUSES.map(([value, label]) => (
                        <button
                            key={value}
                            type="button"
                            onClick={() => visit({ status: value === 'all' ? undefined : value, page: undefined })}
                            aria-pressed={filters.status === value}
                            className={`rounded-md px-3 py-1.5 text-sm font-bold transition ${filters.status === value ? 'bg-brand text-white' : 'text-navy hover:bg-soft'}`}
                        >
                            {label}
                        </button>
                    ))}
                </div>
            </div>

            <div className={`${card} mt-5 overflow-hidden`}>
                {items.data.length === 0 ? (
                    <p className="px-5 py-10 text-center text-muted">{filters.q || filters.status !== 'all' ? 'Aucun résultat pour ces filtres.' : labels.empty}</p>
                ) : (
                    <ul className="divide-y divide-line">
                        {items.data.map((item) => (
                            <li key={item.id} className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center">
                                <Link href={`${baseUrl}/${item.id}`} className="min-w-0 flex-1 group">
                                    <span className="flex flex-wrap items-center gap-2">
                                        <span className="font-semibold text-navy group-hover:text-brand group-hover:underline">{item.title}</span>
                                        <StatusBadge done={item.done} doneLabel={labels.done} />
                                    </span>
                                    <span className="mt-0.5 block truncate text-sm text-muted">{item.subtitle}</span>
                                    <span className="block truncate text-sm text-muted">{item.meta}</span>
                                </Link>
                                <span className="flex items-center gap-3 sm:shrink-0">
                                    <span className="text-sm text-muted">{item.date}</span>
                                    <button
                                        type="button"
                                        onClick={() => router.patch(`${baseUrl}/${item.id}/statut`, {}, { preserveScroll: true })}
                                        className="rounded-lg border border-line px-3 py-1.5 text-sm font-bold text-navy hover:border-brand hover:text-brand"
                                    >
                                        {item.done ? 'Remettre en nouveau' : 'Marquer traité'}
                                    </button>
                                </span>
                            </li>
                        ))}
                    </ul>
                )}
            </div>

            {items.last_page > 1 && (
                <nav className="mt-5 flex items-center justify-between gap-3 text-sm" aria-label="Pagination">
                    {items.prev_page_url ? <Link href={items.prev_page_url} preserveScroll className={btnOutline}>← Précédent</Link> : <span />}
                    <span className="text-muted">Page {items.current_page} sur {items.last_page} · {items.total} au total</span>
                    {items.next_page_url ? <Link href={items.next_page_url} preserveScroll className={btnOutline}>Suivant →</Link> : <span />}
                </nav>
            )}
        </AdminLayout>
    );
}

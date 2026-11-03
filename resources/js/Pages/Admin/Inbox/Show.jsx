import { Link, router } from '@inertiajs/react';
import AdminLayout, { StatusBadge, btnOutline, btnPrimary, card } from '@/Components/Admin/AdminLayout';
import Icon from '@/Components/Site/Icon';

export default function InboxShow({ labels, baseUrl, item }) {
    return (
        <AdminLayout title={labels.singular}>
            <Link href={baseUrl} className="text-sm font-bold text-brand hover:underline">← {labels.title}</Link>

            <article className={`${card} mt-4 max-w-3xl`}>
                <header className="flex flex-wrap items-start justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
                    <div className="min-w-0">
                        <h2 className="font-display text-xl font-extrabold text-navy">{item.title}</h2>
                        <p className="text-sm text-muted">Reçu le {item.date}{item.doneAt && ` · ${labels.done.toLowerCase()} le ${item.doneAt}`}</p>
                    </div>
                    <StatusBadge done={item.done} doneLabel={labels.done} />
                </header>

                <dl className="divide-y divide-line">
                    {item.details.map(({ label, value }) => (
                        <div key={label} className="grid gap-1 px-5 py-3 sm:grid-cols-[200px_1fr] sm:px-6">
                            <dt className="text-sm font-bold text-muted">{label}</dt>
                            <dd className="break-words whitespace-pre-line text-ink">{value || <span className="text-muted">—</span>}</dd>
                        </div>
                    ))}
                </dl>

                <footer className="flex flex-wrap gap-3 border-t border-line px-5 py-4 sm:px-6">
                    <a href={`mailto:${item.email}`} className={btnPrimary}>
                        <Icon name="mail" className="size-5" /> Répondre par e-mail
                    </a>
                    <button type="button" onClick={() => router.patch(`${baseUrl}/${item.id}/statut`, {}, { preserveScroll: true })} className={btnOutline}>
                        <Icon name="check" className="size-5" /> {item.done ? 'Remettre en nouveau' : 'Marquer comme traité'}
                    </button>
                </footer>
            </article>
        </AdminLayout>
    );
}

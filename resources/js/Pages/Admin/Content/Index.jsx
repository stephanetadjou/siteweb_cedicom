import { Link, router } from '@inertiajs/react';
import AdminLayout, { btnAccent, card } from '@/Components/Admin/AdminLayout';
import Icon from '@/Components/Site/Icon';

const iconBtn = 'rounded-lg border border-line p-2 text-navy transition hover:border-brand hover:text-brand disabled:opacity-30 disabled:hover:border-line disabled:hover:text-navy';

export default function ContentIndex({ labels, baseUrl, items }) {
    const remove = (item) => {
        if (window.confirm(`Supprimer « ${item.title} » ? Cette action est définitive.`)) {
            router.delete(`${baseUrl}/${item.id}`, { preserveScroll: true });
        }
    };
    const move = (item, direction) => router.patch(`${baseUrl}/${item.id}/${direction}`, {}, { preserveScroll: true });

    return (
        <AdminLayout
            title={labels.title}
            actions={
                <Link href={`${baseUrl}/ajouter`} className={btnAccent}>
                    <Icon name="plus" className="size-5" /> <span className="hidden sm:inline">Ajouter</span>
                </Link>
            }
        >
            {labels.help && <p className="mb-5 max-w-3xl text-[15px] text-muted">{labels.help}</p>}

            <div className={`${card} overflow-hidden`}>
                {items.length === 0 ? (
                    <p className="px-5 py-10 text-center text-muted">Aucun élément pour le moment.</p>
                ) : (
                    <ul className="divide-y divide-line">
                        {items.map((item, i) => (
                            <li key={item.id} className="flex items-center gap-3 px-3 py-3 sm:gap-4 sm:px-5">
                                <span className="flex flex-col gap-1">
                                    <button type="button" onClick={() => move(item, 'monter')} disabled={i === 0} className={`${iconBtn} p-1`} aria-label={`Monter ${item.title}`}>
                                        <Icon name="arrow-up" className="size-4" />
                                    </button>
                                    <button type="button" onClick={() => move(item, 'descendre')} disabled={i === items.length - 1} className={`${iconBtn} p-1`} aria-label={`Descendre ${item.title}`}>
                                        <Icon name="arrow-down" className="size-4" />
                                    </button>
                                </span>
                                <span className={`grid h-14 w-20 shrink-0 place-items-center overflow-hidden rounded-lg border border-line ${item.contain ? 'bg-white p-1.5' : 'bg-soft'}`}>
                                    {item.image && <img src={item.image} alt="" loading="lazy" className={`size-full ${item.contain ? 'object-contain' : 'object-cover'}`} />}
                                </span>
                                <span className="min-w-0 flex-1">
                                    <Link href={`${baseUrl}/${item.id}/modifier`} className="block truncate font-semibold text-navy hover:text-brand hover:underline">{item.title || 'Sans titre'}</Link>
                                    {item.subtitle && <span className="block truncate text-sm text-muted">{item.subtitle}</span>}
                                    <span className="mt-1 flex flex-wrap gap-1.5">
                                        {item.featured && <span className="rounded-full bg-orange-50 px-2 py-0.5 text-xs font-bold text-accent-dark ring-1 ring-orange-200">Mise en avant</span>}
                                        {item.visible === false && <span className="rounded-full bg-soft px-2 py-0.5 text-xs font-bold text-muted ring-1 ring-line">Masqué</span>}
                                    </span>
                                </span>
                                <span className="flex shrink-0 gap-1.5">
                                    <Link href={`${baseUrl}/${item.id}/modifier`} className={iconBtn} aria-label={`Modifier ${item.title}`}>
                                        <Icon name="edit" className="size-5" />
                                    </Link>
                                    <button type="button" onClick={() => remove(item)} className="rounded-lg border border-line p-2 text-red-700 hover:border-red-300 hover:bg-red-50" aria-label={`Supprimer ${item.title}`}>
                                        <Icon name="trash" className="size-5" />
                                    </button>
                                </span>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AdminLayout>
    );
}

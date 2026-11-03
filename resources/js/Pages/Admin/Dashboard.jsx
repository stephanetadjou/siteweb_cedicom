import { Link } from '@inertiajs/react';
import AdminLayout, { StatusBadge, card } from '@/Components/Admin/AdminLayout';
import Icon from '@/Components/Site/Icon';

function StatCard({ href, icon, label, stat }) {
    return (
        <Link href={href} className={`${card} flex items-center gap-4 p-5 transition hover:border-brand`}>
            <span className="grid size-12 place-items-center rounded-xl bg-sky text-brand">
                <Icon name={icon} className="size-6" />
            </span>
            <span className="min-w-0">
                <span className="block text-[15px] font-semibold text-muted">{label}</span>
                <span className="block font-display text-2xl font-extrabold text-navy">
                    {stat.new} <span className="text-base font-bold text-accent">nouveau{stat.new > 1 ? 'x' : ''}</span>
                </span>
                <span className="block text-sm text-muted">{stat.total} au total</span>
            </span>
        </Link>
    );
}

function Latest({ title, href, items, empty }) {
    return (
        <section className={card}>
            <div className="flex items-center justify-between border-b border-line px-5 py-4">
                <h2 className="font-display text-lg font-extrabold text-navy">{title}</h2>
                <Link href={href} className="text-sm font-bold text-brand hover:underline">Tout voir</Link>
            </div>
            {items.length === 0 ? (
                <p className="px-5 py-6 text-[15px] text-muted">{empty}</p>
            ) : (
                <ul className="divide-y divide-line">
                    {items.map((item) => (
                        <li key={item.id}>
                            <Link href={`${href}/${item.id}`} className="flex items-start justify-between gap-3 px-5 py-3.5 hover:bg-soft">
                                <span className="min-w-0">
                                    <span className="block truncate font-semibold text-navy">{item.title}</span>
                                    <span className="block truncate text-sm text-muted">{item.subtitle}</span>
                                </span>
                                <span className="shrink-0 text-right">
                                    <StatusBadge done={item.done} />
                                    <span className="mt-1 block text-xs text-muted">{item.date}</span>
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}

export default function Dashboard({ stats, latestPartnerships, latestMessages }) {
    return (
        <AdminLayout title="Tableau de bord">
            <div className="grid gap-4 sm:grid-cols-2">
                <StatCard href="/admin/partenariats?status=new" icon="handshake" label="Demandes de partenariat" stat={stats.partnerships} />
                <StatCard href="/admin/messages?status=new" icon="mail" label="Messages de contact" stat={stats.messages} />
            </div>

            <div className="mt-6 grid gap-6 xl:grid-cols-2">
                <Latest title="Dernières demandes de partenariat" href="/admin/partenariats" items={latestPartnerships} empty="Aucune demande pour le moment." />
                <Latest title="Derniers messages" href="/admin/messages" items={latestMessages} empty="Aucun message pour le moment." />
            </div>

        </AdminLayout>
    );
}

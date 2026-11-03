import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Icon from '@/Components/Site/Icon';

const NAV = [
    ['/admin', 'Tableau de bord', 'home'],
    ['/admin/partenariats', 'Partenariats', 'handshake'],
    ['/admin/messages', 'Messages de contact', 'mail'],
];

function Nav({ url }) {
    return (
        <nav className="px-3 py-5" aria-label="Back office">
            <ul className="space-y-1">
                {NAV.map(([href, label, icon]) => {
                    const active = href === '/admin' ? url === '/admin' : url.startsWith(href);
                    return (
                        <li key={href}>
                            <Link
                                href={href}
                                aria-current={active ? 'page' : undefined}
                                className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-[15px] font-semibold transition ${active ? 'bg-white text-brand' : 'text-white/90 hover:bg-white/10'}`}
                            >
                                <Icon name={icon} className="size-5" />
                                {label}
                            </Link>
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}

/** Mise en page du back office : menu latéral (tiroir sur mobile), en-tête et message de confirmation. */
export default function AdminLayout({ title, actions, children }) {
    const { url, props } = usePage();
    const user = props.auth?.user;
    const success = props.flash?.success;
    const [open, setOpen] = useState(false);

    useEffect(() => setOpen(false), [url]);

    return (
        <div className="min-h-screen bg-page lg:flex">
            <Head title={`${title} · Administration CEDICOM`} />

            {/* Menu : tiroir sur mobile, colonne fixe sur grand écran */}
            <aside
                className={`fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-navy text-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}
            >
                <div className="flex items-center justify-between border-b border-white/10 px-5 py-4">
                    <Link href="/admin" className="flex items-center gap-2">
                        <img src="/images/logo/logo-blanc.png" alt="CEDICOM" className="h-8 w-auto" />
                    </Link>
                    <button type="button" onClick={() => setOpen(false)} className="rounded-lg p-1.5 hover:bg-white/10 lg:hidden" aria-label="Fermer le menu">
                        <Icon name="close" className="size-6" />
                    </button>
                </div>
                <div className="flex-1 overflow-y-auto">
                    <Nav url={url.split('?')[0]} />
                </div>
                <div className="border-t border-white/10 p-4 text-sm">
                    {user && <p className="truncate font-semibold">{user.name}<span className="block truncate font-normal text-white/60">{user.email}</span></p>}
                    <div className="mt-3 flex gap-2">
                        <a href="/" target="_blank" rel="noreferrer" className="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-white/10 px-3 py-2 font-semibold hover:bg-white/20">
                            <Icon name="external" className="size-4" /> Voir le site
                        </a>
                        <button type="button" onClick={() => router.post('/admin/logout')} className="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-accent px-3 py-2 font-semibold hover:bg-accent-dark">
                            <Icon name="logout" className="size-4" /> Déconnexion
                        </button>
                    </div>
                </div>
            </aside>
            {open && <div className="fixed inset-0 z-30 bg-navy/50 lg:hidden" onClick={() => setOpen(false)} aria-hidden="true" />}

            <div className="min-w-0 flex-1">
                <header className="sticky top-0 z-20 flex items-center gap-3 border-b border-line bg-white/95 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                    <button type="button" onClick={() => setOpen(true)} className="rounded-lg border border-line p-2 text-navy lg:hidden" aria-label="Ouvrir le menu">
                        <Icon name="menu" className="size-5" />
                    </button>
                    <h1 className="min-w-0 flex-1 truncate font-display text-lg font-extrabold text-navy sm:text-xl">{title}</h1>
                    {actions}
                </header>

                <main className="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    {success && (
                        <p role="status" className="mb-5 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-[15px] font-semibold text-green-800">
                            <Icon name="check" className="size-5" /> {success}
                        </p>
                    )}
                    {children}
                </main>
            </div>
        </div>
    );
}

export const card = 'rounded-2xl border border-line bg-white shadow-card';
export const btn = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-[15px] font-bold leading-tight transition disabled:opacity-60';
export const btnPrimary = `${btn} bg-brand text-white hover:bg-brand-dark`;
export const btnOutline = `${btn} border border-line bg-white text-navy hover:border-brand hover:text-brand`;

export function StatusBadge({ done, doneLabel = 'Traité' }) {
    return done ? (
        <span className="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-bold text-green-800 ring-1 ring-green-200">{doneLabel}</span>
    ) : (
        <span className="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-bold text-accent-dark ring-1 ring-orange-200">Nouveau</span>
    );
}

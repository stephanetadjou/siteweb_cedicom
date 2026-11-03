import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useLocale, useT } from '@/lib/i18n';
import Icon from './Icon';

const SECTIONS = ['accueil', 'a-propos', 'divisions', 'logistique'];

/** Lien vers une section de l'accueil : simple ancre sur l'accueil, visite Inertia ailleurs. */
export function SectionLink({ section, onHome, ...props }) {
    return onHome ? <a href={`#${section}`} {...props} /> : <Link href={`/#${section}`} {...props} />;
}

function LanguageSwitch({ className = '' }) {
    const t = useT();
    const { locale, locales } = useLocale();

    return (
        <div className={`flex rounded-lg border border-line p-0.5 ${className}`} role="group" aria-label={t('nav.language')}>
            {locales.map((code) => (
                <Link
                    key={code}
                    href={`/langue/${code}`}
                    preserveScroll
                    lang={code}
                    aria-current={code === locale ? 'true' : undefined}
                    className="rounded-md px-2.5 py-1.5 text-xs font-bold uppercase text-muted transition hover:text-navy aria-[current=true]:bg-navy aria-[current=true]:text-white"
                >
                    {code}
                </Link>
            ))}
        </div>
    );
}

export default function Header() {
    const t = useT();
    const { url } = usePage();
    const path = url.split(/[?#]/)[0];
    const onHome = path === '/';
    const [open, setOpen] = useState(false);
    const [current, setCurrent] = useState('accueil');

    const links = [
        ['accueil', t('nav.home')],
        ['a-propos', t('nav.about')],
        ['divisions', t('nav.divisions')],
        ['logistique', t('nav.logistics')],
    ];

    // Section visible = lien actif (accueil uniquement)
    useEffect(() => {
        if (!onHome || !('IntersectionObserver' in window)) return;
        const observer = new IntersectionObserver(
            (entries) => entries.forEach((e) => e.isIntersecting && setCurrent(e.target.id)),
            { rootMargin: '-45% 0px -50% 0px' },
        );
        SECTIONS.map((id) => document.getElementById(id)).filter(Boolean).forEach((s) => observer.observe(s));
        return () => observer.disconnect();
    }, [onHome]);

    useEffect(() => {
        const onKey = (e) => e.key === 'Escape' && setOpen(false);
        const mq = window.matchMedia('(min-width: 1024px)');
        const onMq = (e) => e.matches && setOpen(false);
        document.addEventListener('keydown', onKey);
        mq.addEventListener('change', onMq);
        return () => {
            document.removeEventListener('keydown', onKey);
            mq.removeEventListener('change', onMq);
        };
    }, []);

    const desktopLink =
        'rounded-lg px-4 py-2 text-[15px] font-semibold text-[#2a4a7a] transition hover:bg-soft hover:text-navy aria-[current=page]:bg-sky aria-[current=page]:text-navy';
    const mobileLink = 'flex items-center justify-between rounded-lg px-3 py-3.5 text-base font-semibold text-navy hover:bg-soft';

    return (
        <header className="sticky top-0 z-50 border-b border-line/70 bg-white/95 shadow-[0_2px_12px_-6px_rgb(11_42_91/0.25)] backdrop-blur supports-[backdrop-filter]:bg-white/85">
            <div className="container-site flex h-[72px] items-center gap-4 lg:gap-6">
                <Link href="/" className="shrink-0" aria-label={t('nav.home_link')}>
                    <img src="/images/logo/logo-cedicom.png" alt={t('logo.alt')} width="944" height="200" className="h-9 w-auto sm:h-10" />
                </Link>

                <nav className="ml-auto hidden lg:block" aria-label={t('nav.main')}>
                    <ul className="flex items-center gap-1">
                        {links.map(([section, label]) => (
                            <li key={section}>
                                <SectionLink
                                    section={section}
                                    onHome={onHome}
                                    aria-current={onHome && current === section ? 'page' : undefined}
                                    className={desktopLink}
                                >
                                    {label}
                                </SectionLink>
                            </li>
                        ))}
                        <li>
                            <Link href="/contact" aria-current={path === '/contact' ? 'page' : undefined} className={desktopLink}>
                                {t('nav.contact')}
                            </Link>
                        </li>
                    </ul>
                </nav>

                <LanguageSwitch className="ml-auto lg:ml-0" />

                <Link href="/devenir-partenaire" className="btn btn-accent hidden px-4 py-2.5 text-sm uppercase tracking-wide sm:inline-flex">
                    <Icon name="handshake" />
                    {t('nav.partner')}
                </Link>

                <button
                    type="button"
                    className="inline-grid size-11 place-items-center rounded-lg border border-line text-navy transition hover:bg-soft lg:hidden"
                    aria-expanded={open}
                    aria-controls="menu-mobile"
                    aria-label={open ? t('nav.close_menu') : t('nav.open_menu')}
                    onClick={() => setOpen(!open)}
                >
                    <Icon name={open ? 'close' : 'menu'} className="size-6" />
                </button>
            </div>

            <div id="menu-mobile" className="border-t border-line bg-white lg:hidden" hidden={!open}>
                <nav className="container-site py-3" aria-label={t('nav.mobile')} onClick={(e) => e.target.closest('a') && setOpen(false)}>
                    <ul className="flex flex-col">
                        {links.map(([section, label]) => (
                            <li key={section}>
                                <SectionLink section={section} onHome={onHome} className={mobileLink}>
                                    {label}
                                    <Icon name="arrow-right" className="size-4 text-muted" />
                                </SectionLink>
                            </li>
                        ))}
                        <li>
                            <Link href="/contact" className={mobileLink}>
                                {t('nav.contact')}
                                <Icon name="arrow-right" className="size-4 text-muted" />
                            </Link>
                        </li>
                    </ul>
                    <Link href="/devenir-partenaire" className="btn btn-accent mt-3 w-full uppercase tracking-wide">
                        <Icon name="handshake" />
                        {t('nav.partner')}
                    </Link>
                </nav>
            </div>
        </header>
    );
}

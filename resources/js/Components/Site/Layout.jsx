import { Head, usePage } from '@inertiajs/react';
import { useReveal } from '@/lib/motion';
import { useEffect } from 'react';
import { useLocale, useT } from '@/lib/i18n';
import Footer from './Footer';
import Header from './Header';

export default function Layout({ title, description, children }) {
    const t = useT();
    const { locale } = useLocale();
    const { url } = usePage();

    useReveal([url, locale]);

    // Garde <html lang> à jour après un changement de langue sans rechargement
    useEffect(() => {
        document.documentElement.lang = locale;
    }, [locale]);

    return (
        <div className="flex min-h-screen flex-col">
            <Head title={title ? `${title} · CEDICOM Group` : t('meta.title')}>
                <meta head-key="description" name="description" content={description ?? t('meta.description')} />
            </Head>

            <a
                href="#contenu"
                className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:font-bold focus:text-brand focus:shadow-lg"
            >
                {t('nav.skip')}
            </a>

            <Header />

            <main id="contenu" className="flex-1">
                {children}
            </main>

            <Footer />
        </div>
    );
}

import { Link, usePage } from '@inertiajs/react';
import { useT } from '@/lib/i18n';
import { SectionLink } from './Header';
import Icon from './Icon';

export default function Footer() {
    const t = useT();
    const onHome = usePage().url.split(/[?#]/)[0] === '/';
    const link = 'text-white/90 hover:text-white hover:underline';
    const heading = 'mb-3 text-xs font-bold uppercase tracking-[0.16em] text-white/65';

    return (
        <footer className="bg-brand text-white">
            <div className="container-site grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1.3fr]">
                <div>
                    <img src="/images/logo/logo-blanc.png" alt={t('logo.alt')} width="460" height="98" className="h-11 w-auto" loading="lazy" />
                    <p className="mt-4 max-w-[40ch] text-white/80">{t('footer.tagline')}</p>
                </div>

                <div>
                    <h2 className={heading}>{t('footer.group')}</h2>
                    <ul className="space-y-1.5">
                        <li><SectionLink section="poles" onHome={onHome} className={link}>{t('footer.activities')}</SectionLink></li>
                        <li><SectionLink section="a-propos" onHome={onHome} className={link}>{t('footer.history')}</SectionLink></li>
                        <li><SectionLink section="marques" onHome={onHome} className={link}>{t('footer.partners')}</SectionLink></li>
                        <li><SectionLink section="divisions" onHome={onHome} className={link}>{t('footer.divisions')}</SectionLink></li>
                    </ul>
                </div>

                <div>
                    <h2 className={heading}>{t('footer.partnership')}</h2>
                    <ul className="space-y-1.5">
                        <li><Link href="/devenir-partenaire" className={link}>{t('footer.become_partner')}</Link></li>
                        <li><SectionLink section="logistique" onHome={onHome} className={link}>{t('footer.logistics')}</SectionLink></li>
                        <li><Link href="/contact" className={link}>{t('footer.contact_us')}</Link></li>
                        <li>
                            <a href="https://www.retailgroup-brands.com/" target="_blank" rel="noopener" className={`inline-flex items-center gap-1 ${link}`}>
                                Retail Group <Icon name="external" className="size-3.5" />
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 className={heading}>{t('footer.contact')}</h2>
                    <ul className="space-y-2.5 text-white/90">
                        <li className="flex gap-2.5">
                            <Icon name="phone" className="mt-0.5 size-5 text-white/70" />
                            <a href="tel:+24101775748" className="hover:underline">(+241) 01 77 57 48 / 49</a>
                        </li>
                        <li className="flex gap-2.5">
                            <Icon name="mail" className="mt-0.5 size-5 text-white/70" />
                            <a href="mailto:info@cedicomgroup.com" className="break-all hover:underline">info@cedicomgroup.com</a>
                        </li>
                        <li className="flex gap-2.5">
                            <Icon name="pin" className="mt-0.5 size-5 text-white/70" />
                            {t('contact.city')}
                        </li>
                    </ul>
                </div>
            </div>

            <div className="border-t border-white/15">
                <div className="container-site flex flex-col gap-2 py-5 text-sm text-white/75 sm:flex-row sm:items-center sm:justify-between">
                    <p>© CEDICOM {new Date().getFullYear()} · {t('footer.legal')}</p>
                    <p>{t('contact.city')}</p>
                </div>
            </div>
        </footer>
    );
}

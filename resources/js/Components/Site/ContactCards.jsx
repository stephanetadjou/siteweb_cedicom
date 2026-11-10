import { usePage } from '@inertiajs/react';
import { useT } from '@/lib/i18n';
import Icon from './Icon';

export default function ContactCards({ className = '' }) {
    const t = useT();
    const { site } = usePage().props;
    const card = 'flex flex-col gap-3 rounded-2xl border border-line bg-white p-6';
    const line = 'flex items-center gap-2.5 font-semibold text-ink hover:text-brand';

    return (
        <div className={`grid gap-4 md:grid-cols-2 ${className}`}>
            <div className={card}>
                <h3 className="font-display text-lg font-extrabold text-navy">CEDICOM</h3>
                <p className="flex items-center gap-2.5 text-muted"><Icon name="pin" className="size-5 text-brand" />{site.address}</p>
                <a href={`tel:${site.phone_link}`} className={line}><Icon name="phone" className="size-5 text-brand" />{site.phone}</a>
                <a href={`mailto:${site.email}`} className={`${line} break-all`}><Icon name="mail" className="size-5 text-brand" />{site.email}</a>
            </div>
            <div className={card}>
                <h3 className="font-display text-lg font-extrabold text-navy">Retail Group</h3>
                <p className="flex items-center gap-2.5 text-muted"><Icon name="pin" className="size-5 text-brand" />{t('contact.retail_desc')}</p>
                <a href="https://www.retailgroup-brands.com/" target="_blank" rel="noopener" className={line}><Icon name="globe" className="size-5 text-brand" />retailgroup-brands.com</a>
            </div>
        </div>
    );
}

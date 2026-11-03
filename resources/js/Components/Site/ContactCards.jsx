import { useT } from '@/lib/i18n';
import Icon from './Icon';

export default function ContactCards({ className = '' }) {
    const t = useT();
    const card = 'flex flex-col gap-3 rounded-2xl border border-line bg-white p-6';
    const line = 'flex items-center gap-2.5 font-semibold text-ink hover:text-brand';

    return (
        <div className={`grid gap-4 md:grid-cols-2 ${className}`}>
            <div className={card}>
                <h3 className="font-display text-lg font-extrabold text-navy">CEDICOM</h3>
                <p className="flex items-center gap-2.5 text-muted"><Icon name="pin" className="size-5 text-brand" />{t('contact.city')}</p>
                <a href="tel:+24101775748" className={line}><Icon name="phone" className="size-5 text-brand" />(+241) 01 77 57 48 / 49</a>
                <a href="mailto:info@cedicomgroup.com" className={`${line} break-all`}><Icon name="mail" className="size-5 text-brand" />info@cedicomgroup.com</a>
            </div>
            <div className={card}>
                <h3 className="font-display text-lg font-extrabold text-navy">Retail Group</h3>
                <p className="flex items-center gap-2.5 text-muted"><Icon name="pin" className="size-5 text-brand" />{t('contact.retail_desc')}</p>
                <a href="https://www.retailgroup-brands.com/" target="_blank" rel="noopener" className={line}><Icon name="globe" className="size-5 text-brand" />retailgroup-brands.com</a>
            </div>
        </div>
    );
}

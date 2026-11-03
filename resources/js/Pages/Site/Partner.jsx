import Icon from '@/Components/Site/Icon';
import Layout from '@/Components/Site/Layout';
import PageBanner from '@/Components/Site/PageBanner';
import PartnershipForm from '@/Components/Site/PartnershipForm';
import { useT } from '@/lib/i18n';

export default function Partner({ sectors }) {
    const t = useT();
    const offers = ['offer_1', 'offer_2', 'offer_3', 'offer_4'];

    return (
        <Layout title={t('partner.title')} description={t('meta.partner_description')}>
            <PageBanner eyebrow={t('nav.partner')} title={t('partner.page_title')} lead={t('partner.page_lead')} />

            <section className="py-12 lg:py-16">
                <div className="container-site grid items-start gap-6 lg:grid-cols-[1fr_360px] lg:gap-8">
                    <div id="partenariat" className="rounded-xl bg-white p-5 shadow-card sm:p-8">
                        <h2 className="mb-5 font-display text-2xl font-extrabold text-navy">{t('partner.form_title')}</h2>
                        <PartnershipForm sectors={sectors} full />
                    </div>

                    <aside className="rounded-xl bg-sky p-6 sm:p-7 lg:sticky lg:top-24">
                        <h2 className="font-display text-lg font-extrabold text-navy">{t('partner.offer_title')}</h2>
                        <ul className="mt-4 grid gap-3">
                            {offers.map((key) => (
                                <li key={key} className="flex gap-3">
                                    <span className="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-accent text-white">
                                        <Icon name="check" className="size-4" strokeWidth={2.4} />
                                    </span>
                                    {t(`partner.${key}`)}
                                </li>
                            ))}
                        </ul>
                        <div className="mt-6 border-t border-brand/15 pt-5 text-sm text-muted">
                            {t('partner.question')}
                            <br />
                            <a href="tel:+24101775748" className="font-semibold text-brand hover:underline">(+241) 01 77 57 48 / 49</a>
                        </div>
                    </aside>
                </div>
            </section>
        </Layout>
    );
}

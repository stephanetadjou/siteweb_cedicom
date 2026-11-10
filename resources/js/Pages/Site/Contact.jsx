import { useForm } from '@inertiajs/react';
import ContactCards from '@/Components/Site/ContactCards';
import Field from '@/Components/Site/Field';
import FormStatus from '@/Components/Site/FormStatus';
import Icon from '@/Components/Site/Icon';
import Layout from '@/Components/Site/Layout';
import PageBanner from '@/Components/Site/PageBanner';
import { useAntiSpam } from '@/lib/antispam';
import { useT } from '@/lib/i18n';

export default function Contact({ subjects }) {
    const t = useT();
    const antispam = useAntiSpam('contact');
    const form = useForm({
        name: '',
        email: '',
        phone: '',
        subject: subjects[0],
        message: '',
        website: '', // champ piège anti-spam
    });

    const submit = async (e) => {
        e.preventDefault();
        const extra = await antispam();
        form.transform((data) => ({ ...data, ...extra }));
        form.post('/contact', {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <Layout title={t('nav.contact')} description={t('meta.contact_description')}>
            <PageBanner eyebrow={t('contact.eyebrow')} title={t('contact.page_title')} lead={t('contact.page_lead')} />

            <section className="py-12 lg:py-16">
                <div className="container-site grid gap-8">
                    <ContactCards />

                    <div id="message" className="rounded-xl bg-white p-5 shadow-card sm:p-8">
                        <h2 className="mb-5 font-display text-2xl font-extrabold text-navy">{t('contact.form_title')}</h2>

                        <form onSubmit={submit} className="grid gap-4" noValidate>
                            <div className="hidden" aria-hidden="true">
                                <label>
                                    Website <input type="text" name="website" tabIndex={-1} autoComplete="off" value={form.data.website} onChange={(e) => form.setData('website', e.target.value)} />
                                </label>
                            </div>

                            <FormStatus hasErrors={Object.keys(form.errors).length > 0} message={form.errors.form} />

                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field form={form} name="name" label={t('form.name')} required autoComplete="name" />
                                <Field form={form} name="email" label={t('form.email')} type="email" required autoComplete="email" placeholder={t('form.email_ph')} />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field form={form} name="phone" label={t('form.phone')} type="tel" autoComplete="tel" />
                                <Field form={form} name="subject" label={t('form.subject')} type="select" required options={subjects.map((key) => [key, t(`form.subjects.${key}`)])} />
                            </div>
                            <Field form={form} name="message" label={t('form.message')} type="textarea" rows={5} required />

                            <button type="submit" disabled={form.processing} className="btn btn-primary justify-self-start disabled:opacity-70">
                                {form.processing ? t('form.sending') : t('form.contact_submit')}
                                <Icon name="arrow-right" />
                            </button>
                            {antispam.enabled && <p className="text-xs text-muted">{t('form.recaptcha_notice')}</p>}
                        </form>
                    </div>
                </div>
            </section>
        </Layout>
    );
}

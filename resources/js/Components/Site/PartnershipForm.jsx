import { useForm } from '@inertiajs/react';
import { useAntiSpam } from '@/lib/antispam';
import { useT } from '@/lib/i18n';
import Field from './Field';
import FormStatus from './FormStatus';
import Icon from './Icon';

export default function PartnershipForm({ sectors, full = false, idPrefix = 'partenariat' }) {
    const t = useT();
    const antispam = useAntiSpam('partnership');
    const form = useForm({
        company: '',
        country: '',
        contact_name: '',
        phone: '',
        email: '',
        sector: sectors[0],
        message: '',
        website: '', // champ piège anti-spam
    });

    const submit = async (e) => {
        e.preventDefault();
        const extra = await antispam();
        form.transform((data) => ({ ...data, ...extra }));
        form.post('/devenir-partenaire', {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    const id = (name) => `${idPrefix}-${name}`;
    const sectorOptions = sectors.map((key) => [key, t(`form.sectors.${key}`)]);

    return (
        <form onSubmit={submit} className="grid gap-4" noValidate>
            <div className="hidden" aria-hidden="true">
                <label>
                    Website <input type="text" name="website" tabIndex={-1} autoComplete="off" value={form.data.website} onChange={(e) => form.setData('website', e.target.value)} />
                </label>
            </div>

            <FormStatus hasErrors={Object.keys(form.errors).length > 0} message={form.errors.form} />

            <div className="grid gap-4 sm:grid-cols-2">
                <Field form={form} name="company" id={id('company')} label={t('form.company')} required placeholder={t('form.company_ph')} autoComplete="organization" />
                <Field form={form} name="country" id={id('country')} label={t('form.country')} required placeholder={t('form.country_ph')} autoComplete="country-name" />
            </div>

            {full && (
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field form={form} name="contact_name" id={id('contact')} label={t('form.contact_name')} autoComplete="name" />
                    <Field form={form} name="phone" id={id('phone')} label={t('form.phone')} type="tel" autoComplete="tel" />
                </div>
            )}

            <div className="grid gap-4 sm:grid-cols-2">
                <Field form={form} name="email" id={id('email')} label={t('form.email_pro')} type="email" required placeholder={t('form.email_ph')} autoComplete="email" />
                <Field form={form} name="sector" id={id('sector')} label={t('form.sector')} type="select" options={sectorOptions} required />
            </div>

            {full && (
                <Field form={form} name="message" id={id('message')} label={t('form.project')} type="textarea" rows={5} placeholder={t('form.project_ph')} />
            )}

            <button type="submit" disabled={form.processing} className="btn btn-accent mt-1 w-full py-3.5 text-base disabled:opacity-70">
                {form.processing ? t('form.sending') : t('form.partner_submit')}
                <Icon name="arrow-right" />
            </button>
            {antispam.enabled && <p className="text-center text-xs text-muted">{t('form.recaptcha_notice')}</p>}
            <p className="text-center text-xs text-muted">{t('form.required_note')}</p>
        </form>
    );
}

import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useT } from '@/lib/i18n';
import Icon from './Icon';

/** Message de succès (flash) ou résumé des erreurs, en tête de formulaire. */
export default function FormStatus({ hasErrors }) {
    const t = useT();
    const success = usePage().props.flash?.success;
    const [visible, setVisible] = useState(true);

    useEffect(() => setVisible(true), [success]);

    if (success && visible) {
        return (
            <div className="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900" role="status">
                <span className="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-emerald-600 text-white">
                    <Icon name="check" className="size-4" />
                </span>
                <p className="flex-1">{success}</p>
                <button type="button" className="text-emerald-800/70 hover:text-emerald-900" onClick={() => setVisible(false)} aria-label={t('form.close')}>
                    <Icon name="close" className="size-5" />
                </button>
            </div>
        );
    }

    if (hasErrors) {
        return (
            <div className="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
                <Icon name="alert" className="mt-0.5 size-5" />
                <p>{t('form.errors')}</p>
            </div>
        );
    }

    return null;
}

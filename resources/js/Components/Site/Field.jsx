import { useT } from '@/lib/i18n';
import Icon from './Icon';

/** Champ de formulaire relié à useForm : value/onChange + erreur de validation. */
export default function Field({ form, name, label, id, type = 'text', options = [], required = false, rows = 4, className = '', ...input }) {
    const t = useT();
    const fieldId = id ?? `f-${name}`;
    const error = form.errors[name];
    const common = {
        id: fieldId,
        name,
        value: form.data[name] ?? '',
        onChange: (e) => form.setData(name, e.target.value),
        required,
        className: 'field-input',
        'aria-invalid': error ? 'true' : undefined,
        'aria-describedby': error ? `${fieldId}-error` : undefined,
    };

    return (
        <label htmlFor={fieldId} className={`field-label ${className}`}>
            <span>
                {label}{' '}
                {required ? (
                    <span className="text-accent" aria-hidden="true">*</span>
                ) : (
                    <span className="font-normal text-muted">{t('form.optional')}</span>
                )}
            </span>

            {type === 'select' ? (
                <select {...common}>
                    {options.map(([value, text]) => (
                        <option key={value} value={value}>{text}</option>
                    ))}
                </select>
            ) : type === 'textarea' ? (
                <textarea {...common} {...input} rows={rows} className="field-input resize-y" />
            ) : (
                <input {...common} {...input} type={type} />
            )}

            {error && (
                <span id={`${fieldId}-error`} className="flex items-center gap-1.5 text-sm font-normal text-red-600">
                    <Icon name="alert" className="size-4" /> {error}
                </span>
            )}
        </label>
    );
}

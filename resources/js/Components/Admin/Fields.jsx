import { useEffect, useState } from 'react';

/** Champs de formulaire du back office (contenus et paramètres du site). */
export const input = 'mt-1.5 block w-full rounded-lg border border-line bg-white px-3.5 py-2.5 text-[16px] text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';

export function FieldError({ message }) {
    return message ? <p className="mt-1.5 text-sm font-semibold text-red-700">{message}</p> : null;
}

export function FieldLabel({ field, htmlFor, lang }) {
    return (
        <label htmlFor={htmlFor} className="block text-sm font-bold text-navy">
            {field.label}
            {lang && <span className="ml-1 rounded bg-sky px-1.5 py-0.5 text-[11px] font-bold text-brand">{lang}</span>}
            {field.required && lang !== 'EN' && <span className="text-accent"> *</span>}
        </label>
    );
}

function ImageField({ field, current, value, onChange, error }) {
    const [preview, setPreview] = useState(current);

    useEffect(() => {
        if (!value) return setPreview(current);
        const url = URL.createObjectURL(value);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [value, current]);

    return (
        <div>
            <FieldLabel field={field} htmlFor={field.name} />
            <div className="mt-1.5 flex flex-col gap-3 sm:flex-row sm:items-center">
                <span className="grid h-28 w-44 shrink-0 place-items-center overflow-hidden rounded-lg border border-line bg-soft">
                    {preview ? <img src={preview} alt="" className="size-full object-contain" /> : <span className="text-sm text-muted">Aucune image</span>}
                </span>
                <div className="min-w-0">
                    <input
                        id={field.name}
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        onChange={(e) => onChange(e.target.files[0] ?? null)}
                        className="block w-full text-sm text-ink file:mr-3 file:rounded-lg file:border-0 file:bg-brand file:px-4 file:py-2 file:font-bold file:text-white hover:file:bg-brand-dark"
                    />
                    <p className="mt-1.5 text-sm text-muted">
                        JPG, PNG ou WebP, 5 Mo maximum{field.width ? `, réduite à ${field.width} px de large` : ''}.{current && ' Laisser vide pour garder l’image actuelle.'}
                    </p>
                </div>
            </div>
            <FieldError message={error} />
        </div>
    );
}

/** Affiche un champ décrit par le serveur : { name, label, type, required, help, options, width }. */
export function Field({ field: f, data, setData, errors, current }) {
    const id = f.name;
    let control;

    if (f.type === 'image') {
        control = <ImageField field={f} current={current} value={data[f.name]} onChange={(file) => setData(f.name, file)} error={errors[f.name]} />;
    } else if (['text_tr', 'textarea_tr', 'lines_tr'].includes(f.type)) {
        const Tag = f.type === 'text_tr' ? 'input' : 'textarea';
        control = (
            <div className="grid gap-4 md:grid-cols-2">
                {['fr', 'en'].map((lang) => (
                    <div key={lang}>
                        <FieldLabel field={f} htmlFor={`${id}-${lang}`} lang={lang.toUpperCase()} />
                        <Tag
                            id={`${id}-${lang}`}
                            value={data[f.name]?.[lang] ?? ''}
                            onChange={(e) => setData(f.name, { ...data[f.name], [lang]: e.target.value })}
                            rows={f.type === 'text_tr' ? undefined : f.type === 'lines_tr' ? 6 : 5}
                            className={input}
                            lang={lang}
                        />
                        <FieldError message={errors[`${f.name}.${lang}`]} />
                    </div>
                ))}
            </div>
        );
    } else if (f.type === 'checkbox') {
        control = (
            <label className="flex items-center gap-2.5 text-[16px] font-semibold text-navy">
                <input type="checkbox" checked={!!data[f.name]} onChange={(e) => setData(f.name, e.target.checked)} className="size-5 rounded border-line" />
                {f.label}
            </label>
        );
    } else {
        const type = { number: 'number', email: 'email', url: 'url' }[f.type] ?? 'text';
        control = (
            <div className={f.type === 'number' ? 'max-w-48' : f.type === 'textarea' ? '' : 'max-w-xl'}>
                <FieldLabel field={f} htmlFor={id} />
                {f.type === 'select' ? (
                    <select id={id} value={data[f.name] ?? ''} onChange={(e) => setData(f.name, e.target.value)} className={input}>
                        {!data[f.name] && <option value="">Choisir…</option>}
                        {f.options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                    </select>
                ) : f.type === 'textarea' ? (
                    <textarea id={id} rows={5} value={data[f.name] ?? ''} onChange={(e) => setData(f.name, e.target.value)} className={input} />
                ) : (
                    <input id={id} type={type} min={type === 'number' ? 0 : undefined} placeholder={type === 'url' ? 'https://…' : undefined} value={data[f.name] ?? ''} onChange={(e) => setData(f.name, e.target.value)} className={input} />
                )}
                <FieldError message={errors[f.name]} />
            </div>
        );
    }

    return (
        <div>
            {control}
            {f.help && <p className="mt-1.5 text-sm text-muted">{f.help}</p>}
        </div>
    );
}

import { Link, useForm } from '@inertiajs/react';
import AdminLayout, { btnOutline, btnPrimary, card } from '@/Components/Admin/AdminLayout';
import { Field } from '@/Components/Admin/Fields';

export default function ContentForm({ labels, baseUrl, item, fields, values }) {
    const initial = Object.fromEntries(fields.map((f) => [f.name, f.type === 'image' ? null : values[f.name]]));
    const { data, setData, post, processing, errors, transform } = useForm(initial);

    const submit = (e) => {
        e.preventDefault();
        transform((d) => (item ? { ...d, _method: 'put' } : d));
        post(item ? `${baseUrl}/${item.id}` : baseUrl, { forceFormData: true, preserveScroll: true });
    };

    return (
        <AdminLayout title={item ? `Modifier : ${item.title || labels.singular}` : `Ajouter : ${labels.singular}`}>
            <Link href={baseUrl} className="text-sm font-bold text-brand hover:underline">← {labels.title}</Link>

            <form onSubmit={submit} className={`${card} mt-4 max-w-4xl`} noValidate>
                <div className="space-y-6 p-5 sm:p-6">
                    {fields.map((f) => (
                        <Field key={f.name} field={f} data={data} setData={setData} errors={errors} current={values[f.name]} />
                    ))}
                </div>
                <div className="flex flex-wrap items-center gap-3 border-t border-line px-5 py-4 sm:px-6">
                    <button type="submit" disabled={processing} className={btnPrimary}>{processing ? 'Enregistrement…' : 'Enregistrer'}</button>
                    <Link href={baseUrl} className={btnOutline}>Annuler</Link>
                    {Object.keys(errors).length > 0 && <p className="text-sm font-semibold text-red-700" role="alert">Vérifiez les champs signalés.</p>}
                </div>
            </form>
        </AdminLayout>
    );
}

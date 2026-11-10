import { useForm } from '@inertiajs/react';
import AdminLayout, { btnPrimary, card } from '@/Components/Admin/AdminLayout';
import { Field } from '@/Components/Admin/Fields';

const SECTIONS = [
    ['Coordonnées', 'Affichées dans le pied de page et sur la page Contact.', ['phone', 'phone_link', 'email', 'address']],
    ['Réseaux sociaux', 'Laisser vide pour ne pas afficher l’icône dans le pied de page.', ['facebook', 'instagram', 'linkedin', 'whatsapp']],
    ['Chiffres clés', 'Compteurs sous le titre de l’accueil et section Logistique.', ['stat_warehouses', 'stat_trucks', 'stat_years']],
    ['Haut de l’accueil', 'Grand titre et paragraphe d’introduction, en français et en anglais.', ['hero_title', 'hero_lead']],
];

export default function Settings({ fields, values }) {
    const byName = Object.fromEntries(fields.map((f) => [f.name, f]));
    const { data, setData, put, processing, errors } = useForm(values);

    const submit = (e) => {
        e.preventDefault();
        put('/admin/parametres', { preserveScroll: true });
    };

    return (
        <AdminLayout title="Paramètres du site">
            <form onSubmit={submit} className="max-w-4xl space-y-6" noValidate>
                {SECTIONS.map(([title, help, names]) => (
                    <section key={title} className={card}>
                        <header className="border-b border-line px-5 py-4 sm:px-6">
                            <h2 className="font-display text-lg font-extrabold text-navy">{title}</h2>
                            <p className="text-sm text-muted">{help}</p>
                        </header>
                        <div className="space-y-5 p-5 sm:p-6">
                            {names.map((name) => (
                                <Field key={name} field={byName[name]} data={data} setData={setData} errors={errors} />
                            ))}
                        </div>
                    </section>
                ))}

                <div className="sticky bottom-0 flex items-center gap-3 rounded-xl border border-line bg-white/95 px-5 py-3 shadow-card backdrop-blur">
                    <button type="submit" disabled={processing} className={btnPrimary}>{processing ? 'Enregistrement…' : 'Enregistrer les paramètres'}</button>
                    {Object.keys(errors).length > 0 && <p className="text-sm font-semibold text-red-700" role="alert">Vérifiez les champs signalés.</p>}
                </div>
            </form>
        </AdminLayout>
    );
}

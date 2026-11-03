import { Head, useForm } from '@inertiajs/react';

const input = 'mt-1.5 block w-full rounded-lg border border-line bg-white px-3.5 py-2.5 text-[16px] text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({ email: '', password: '', remember: false });

    const submit = (e) => {
        e.preventDefault();
        post('/admin/login', { onFinish: () => reset('password') });
    };

    return (
        <div className="grid min-h-screen place-items-center bg-gradient-to-b from-[#eaf1f9] to-page px-4 py-10">
            <Head title="Connexion · Administration CEDICOM" />
            <div className="w-full max-w-sm">
                <img src="/images/logo/logo-cedicom.png" alt="CEDICOM" width="944" height="200" className="mx-auto h-11 w-auto" />
                <form onSubmit={submit} className="mt-8 rounded-2xl border border-line bg-white p-6 shadow-card sm:p-8" noValidate>
                    <h1 className="font-display text-xl font-extrabold text-navy">Administration du site</h1>
                    <p className="mt-1 text-[15px] text-muted">Connectez-vous avec votre compte administrateur.</p>

                    <label className="mt-6 block text-sm font-bold text-navy" htmlFor="email">Adresse e-mail</label>
                    <input id="email" type="email" autoComplete="username" autoFocus value={data.email} onChange={(e) => setData('email', e.target.value)} className={input} aria-invalid={!!errors.email} />
                    {errors.email && <p className="mt-1.5 text-sm font-semibold text-red-700">{errors.email}</p>}

                    <label className="mt-4 block text-sm font-bold text-navy" htmlFor="password">Mot de passe</label>
                    <input id="password" type="password" autoComplete="current-password" value={data.password} onChange={(e) => setData('password', e.target.value)} className={input} aria-invalid={!!errors.password} />
                    {errors.password && <p className="mt-1.5 text-sm font-semibold text-red-700">{errors.password}</p>}

                    <label className="mt-4 flex items-center gap-2 text-[15px] text-ink">
                        <input type="checkbox" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} className="size-4 rounded border-line text-brand" />
                        Rester connecté
                    </label>

                    <button type="submit" disabled={processing} className="mt-6 w-full rounded-lg bg-brand px-4 py-3 font-bold text-white transition hover:bg-brand-dark disabled:opacity-60">
                        {processing ? 'Connexion…' : 'Se connecter'}
                    </button>
                </form>
                <p className="mt-6 text-center text-sm text-muted">
                    <a href="/" className="font-semibold text-brand hover:underline">← Retour au site</a>
                </p>
            </div>
        </div>
    );
}

import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Mail, Send, Loader2 } from 'lucide-react';

interface TypeMail { key: string; label: string }
interface Props { types: TypeMail[] }

export default function EmailTest({ types }: Props) {
    const flashProps = (usePage().props as any).flash ?? {};
    const flash = flashProps.success as string | undefined;
    const flashErr = flashProps.error as string | undefined;

    const [type, setType] = useState(types[0]?.key ?? '');
    const [email, setEmail] = useState('');
    const [busy, setBusy] = useState(false);

    const envoyer = () => {
        if (!type || !email.trim()) return;
        setBusy(true);
        router.post('/admin/reglages/email-test', { type, email }, {
            preserveScroll: true,
            onFinish: () => setBusy(false),
        });
    };

    return (
        <AdminLayout title="Tester les e-mails">
            <Head title="Tester les e-mails" />

            <div className="max-w-xl">
                <div className="flex items-center gap-3 mb-1">
                    <Mail className="w-6 h-6 text-emerald-600" />
                    <h1 className="text-xl font-bold text-zinc-900">Tester les e-mails</h1>
                </div>
                <p className="text-sm text-zinc-500 mb-5">
                    Choisis un modèle et une adresse : l'e-mail correspondant est envoyé avec des <b>données d'exemple</b>. Pas besoin de créer un compte ou un paiement.
                </p>

                {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}
                {flashErr && <div className="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 text-sm">{flashErr}</div>}

                <div className="bg-white rounded-2xl border border-zinc-200 p-5 space-y-4">
                    <div>
                        <label className="block text-xs font-medium text-zinc-500 mb-1">Modèle d'e-mail</label>
                        <select value={type} onChange={(e) => setType(e.target.value)}
                            className="w-full px-3 py-2.5 rounded-xl border border-zinc-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            {types.map((t) => <option key={t.key} value={t.key}>{t.label}</option>)}
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-zinc-500 mb-1">Envoyer à</label>
                        <div className="relative">
                            <Mail className="absolute left-3 top-3 w-4 h-4 text-zinc-400" />
                            <input value={email} onChange={(e) => setEmail(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && envoyer()}
                                type="email" placeholder="ton@email.com"
                                className="w-full pl-9 pr-3 py-2.5 rounded-xl border border-zinc-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                    </div>

                    <button onClick={envoyer} disabled={busy || !email.trim()}
                        className="w-full py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold flex items-center justify-center gap-2 disabled:opacity-60">
                        {busy ? <Loader2 className="w-5 h-5 animate-spin" /> : <><Send className="w-4 h-4" /> Envoyer l'e-mail de test</>}
                    </button>
                </div>

                <p className="mt-4 text-xs text-zinc-400">
                    Les e-mails partent avec la configuration active (Brevo si activé). Le sujet des tests est préfixé <b>[TEST]</b> quand c'est possible.
                </p>
            </div>
        </AdminLayout>
    );
}

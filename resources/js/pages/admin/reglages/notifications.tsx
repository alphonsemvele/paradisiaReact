import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Bell, Plus, Trash2, Mail, Check, X } from 'lucide-react';

interface Destinataire { id: number; email: string; actif: boolean }
interface Props {
    destinataires: Destinataire[];
    defaut: string | null;
}

export default function Notifications({ destinataires, defaut }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;
    const [email, setEmail] = useState('');
    const [busy, setBusy] = useState(false);

    const ajouter = () => {
        if (!email.trim()) return;
        setBusy(true);
        router.post('/admin/reglages/notifications', { email }, {
            preserveScroll: true,
            onSuccess: () => setEmail(''),
            onFinish: () => setBusy(false),
        });
    };
    const basculer = (d: Destinataire) => router.patch(`/admin/reglages/notifications/${d.id}/toggle`, {}, { preserveScroll: true });
    const supprimer = (d: Destinataire) => {
        if (!confirm(`Retirer ${d.email} des destinataires ?`)) return;
        router.delete(`/admin/reglages/notifications/${d.id}`, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Notifications admin">
            <Head title="Notifications admin" />

            <div className="max-w-2xl">
                <div className="flex items-center gap-3 mb-1">
                    <Bell className="w-6 h-6 text-emerald-600" />
                    <h1 className="text-xl font-bold text-zinc-900">Notifications par e-mail</h1>
                </div>
                <p className="text-sm text-zinc-500 mb-5">
                    Ces adresses reçoivent un e-mail (avec lien direct) pour <b>tout paiement, inscription, ticket à valider</b> et autres événements de la plateforme.
                </p>

                {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

                {/* Ajouter */}
                <div className="bg-white rounded-2xl border border-zinc-200 p-5 mb-5">
                    <label className="block text-xs font-medium text-zinc-500 mb-1">Ajouter un destinataire</label>
                    <div className="flex gap-2">
                        <div className="relative flex-1">
                            <Mail className="absolute left-3 top-3 w-4 h-4 text-zinc-400" />
                            <input value={email} onChange={(e) => setEmail(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && ajouter()}
                                type="email" placeholder="admin@exemple.com"
                                className="w-full pl-9 pr-3 py-2.5 rounded-xl border border-zinc-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        <button onClick={ajouter} disabled={busy || !email.trim()}
                            className="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm flex items-center gap-1.5 disabled:opacity-60">
                            <Plus className="w-4 h-4" /> Ajouter
                        </button>
                    </div>
                </div>

                {/* Liste */}
                <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                    <div className="px-4 py-3 border-b border-zinc-100"><h2 className="font-bold text-zinc-900 text-sm">Destinataires ({destinataires.length})</h2></div>
                    <ul className="divide-y divide-zinc-100">
                        {destinataires.map((d) => (
                            <li key={d.id} className="flex items-center gap-3 px-4 py-3">
                                <span className={`w-2.5 h-2.5 rounded-full ${d.actif ? 'bg-emerald-500' : 'bg-zinc-300'}`} />
                                <span className={`flex-1 text-sm ${d.actif ? 'text-zinc-900 font-medium' : 'text-zinc-400 line-through'}`}>{d.email}</span>
                                <button onClick={() => basculer(d)} title={d.actif ? 'Désactiver' : 'Activer'}
                                    className={`text-xs font-semibold px-2.5 py-1 rounded-lg ${d.actif ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'}`}>
                                    {d.actif ? <span className="inline-flex items-center gap-1"><Check className="w-3.5 h-3.5" /> actif</span> : <span className="inline-flex items-center gap-1"><X className="w-3.5 h-3.5" /> inactif</span>}
                                </button>
                                <button onClick={() => supprimer(d)} className="p-1.5 text-zinc-400 hover:text-red-600"><Trash2 className="w-4 h-4" /></button>
                            </li>
                        ))}
                        {destinataires.length === 0 && (
                            <li className="px-4 py-8 text-center text-sm text-zinc-400">
                                Aucun destinataire. Les notifications iront à l'adresse par défaut{defaut ? ` (${defaut})` : ''}.
                            </li>
                        )}
                    </ul>
                </div>

                <p className="mt-4 text-xs text-zinc-400">
                    Repli : si aucun destinataire actif, les notifications partent vers l'adresse par défaut{defaut ? ` (${defaut})` : ''}.
                </p>
            </div>
        </AdminLayout>
    );
}

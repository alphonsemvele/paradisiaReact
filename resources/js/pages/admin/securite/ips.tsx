import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { ShieldBan, Plus, Trash2 } from 'lucide-react';

interface Ip { id: number; ip: string; raison: string | null; date: string }
interface Props { ips: Ip[] }

export default function BannedIps({ ips }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;
    const [ip, setIp] = useState('');
    const [raison, setRaison] = useState('');
    const [busy, setBusy] = useState(false);
    const [erreur, setErreur] = useState('');

    const ajouter = () => {
        setErreur('');
        if (!ip.trim()) { setErreur('Saisis une adresse IP.'); return; }
        setBusy(true);
        router.post('/admin/securite/ips', { ip, raison }, {
            preserveScroll: true,
            onSuccess: () => { setIp(''); setRaison(''); },
            onError: (e) => setErreur((e as any).ip ?? 'Erreur'),
            onFinish: () => setBusy(false),
        });
    };

    const supprimer = (b: Ip) => {
        if (!confirm(`Débannir l'IP ${b.ip} ?`)) return;
        router.delete(`/admin/securite/ips/${b.id}`, { preserveScroll: true });
    };

    return (
        <AdminLayout title="IP bannies">
            <Head title="IP bannies — Admin" />

            <div className="flex items-center gap-3 mb-5">
                <div className="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center"><ShieldBan className="w-6 h-6 text-red-600" /></div>
                <div>
                    <h1 className="text-xl font-bold text-zinc-900">Adresses IP bannies</h1>
                    <p className="text-sm text-zinc-500">Ces adresses ne peuvent plus accéder au site (sauf les administrateurs).</p>
                </div>
            </div>

            {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

            {/* Ajout */}
            <div className="bg-white rounded-2xl border border-zinc-200 p-5 mb-6">
                <div className="grid sm:grid-cols-[1fr_1.5fr_auto] gap-3 items-end">
                    <div>
                        <span className="block text-xs font-medium text-zinc-500 mb-1">Adresse IP</span>
                        <input className="ipt" placeholder="Ex : 41.202.219.10" value={ip} onChange={(e) => setIp(e.target.value)} />
                    </div>
                    <div>
                        <span className="block text-xs font-medium text-zinc-500 mb-1">Raison (facultatif)</span>
                        <input className="ipt" placeholder="Faux comptes, spam…" value={raison} onChange={(e) => setRaison(e.target.value)} />
                    </div>
                    <button onClick={ajouter} disabled={busy}
                        className="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2.5 disabled:opacity-60">
                        <Plus className="w-4 h-4" /> Bannir
                    </button>
                </div>
                {erreur && <p className="text-xs text-red-600 mt-2">{erreur}</p>}
                <style>{`.ipt{width:100%;padding:.55rem .7rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.875rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #ef4444}`}</style>
            </div>

            {/* Liste */}
            <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Adresse IP</th>
                                <th className="text-left px-4 py-3 font-medium">Raison</th>
                                <th className="text-left px-4 py-3 font-medium">Bannie le</th>
                                <th className="text-right px-4 py-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {ips.map((b) => (
                                <tr key={b.id} className="hover:bg-zinc-50">
                                    <td className="px-4 py-3 font-mono font-medium text-zinc-900">{b.ip}</td>
                                    <td className="px-4 py-3 text-zinc-600">{b.raison ?? '—'}</td>
                                    <td className="px-4 py-3 text-zinc-400 whitespace-nowrap">{b.date}</td>
                                    <td className="px-4 py-3 text-right">
                                        <button onClick={() => supprimer(b)} className="inline-flex items-center gap-1 text-sm text-emerald-700 hover:text-emerald-900 font-medium">
                                            <Trash2 className="w-4 h-4" /> Débannir
                                        </button>
                                    </td>
                                </tr>
                            ))}
                            {ips.length === 0 && (
                                <tr><td colSpan={4} className="px-4 py-12 text-center text-zinc-400">Aucune IP bannie.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}

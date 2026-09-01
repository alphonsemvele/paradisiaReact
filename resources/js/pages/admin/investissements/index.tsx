import { useEffect, useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { TrendingUp, Users, PieChart, Layers, Gift, Search, Check, Sparkles } from 'lucide-react';

interface Round { id: number; name: string; prix_part: number; status: string; actif: boolean; begin: string | null; end: string | null; parts_vendues: number; collecte: number }
interface Achat { id: number; user_id: number; user: string; round: string; parts: number; montant: number; status: string; type: string; credit: boolean; date: string | null }
interface Props {
    stats: { total_invested: number; total_shares: number; total_investors: number; nb_rounds: number };
    rounds: Round[];
    derniers: Achat[];
}
interface U { id: number; name: string; email: string }

const fcfa = (n: number) => n.toLocaleString('fr-FR') + ' FCFA';

export default function Investissements({ stats, rounds, derniers }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;

    // Formulaire de crédit.
    const [q, setQ] = useState('');
    const [resultats, setResultats] = useState<U[]>([]);
    const [user, setUser] = useState<U | null>(null);
    const [roundId, setRoundId] = useState<number | ''>('');
    const [parts, setParts] = useState('');
    const [note, setNote] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (user) return;
        const t = setTimeout(async () => {
            try {
                const r = await fetch(`/admin/investments/users?q=${encodeURIComponent(q)}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (r.ok) { const d = await r.json(); setResultats(d.users ?? []); }
            } catch { /* silencieux */ }
        }, 250);
        return () => clearTimeout(t);
    }, [q, user]);

    const round = rounds.find((r) => r.id === roundId);
    const montant = round && parts ? Number(parts) * round.prix_part : 0;

    const crediter = () => {
        if (!user || !roundId || !parts || Number(parts) <= 0) return;
        setBusy(true);
        router.post('/admin/investments/crediter', { id_user: user.id, id_round: roundId, parts: Number(parts), note }, {
            preserveScroll: true,
            onSuccess: () => { setUser(null); setQ(''); setParts(''); setNote(''); setRoundId(''); },
            onFinish: () => setBusy(false),
        });
    };

    return (
        <AdminLayout title="Investissements">
            <Head title="Investissements — Admin" />

            <h1 className="text-xl font-bold text-zinc-900 mb-1">Investissements</h1>
            <p className="text-sm text-zinc-500 mb-5">Suivi des parts, rounds et crédit manuel.</p>

            {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

            {/* Stats */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <Stat label="Total investi" value={fcfa(stats.total_invested)} icon={TrendingUp} color="#14532d" />
                <Stat label="Parts vendues" value={stats.total_shares.toLocaleString('fr-FR')} icon={PieChart} color="#0d9488" />
                <Stat label="Investisseurs" value={stats.total_investors.toLocaleString('fr-FR')} icon={Users} color="#7c3aed" />
                <Stat label="Rounds" value={stats.nb_rounds.toLocaleString('fr-FR')} icon={Layers} color="#E8792B" />
            </div>

            <div className="grid lg:grid-cols-2 gap-6">
                {/* Crédit de parts */}
                <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                    <h2 className="font-bold text-zinc-900 mb-1 flex items-center gap-2"><Gift className="w-5 h-5 text-emerald-600" /> Créditer des parts</h2>
                    <p className="text-xs text-zinc-500 mb-4">Attribue des parts à un utilisateur sur un round — <b>même s'il n'est pas actif</b>.</p>

                    {/* Utilisateur */}
                    <label className="block text-xs font-medium text-zinc-500 mb-1">Utilisateur</label>
                    {user ? (
                        <div className="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 mb-3">
                            <Check className="w-4 h-4 text-emerald-600" />
                            <div className="flex-1 min-w-0"><p className="text-sm font-semibold text-zinc-900 truncate">{user.name}</p><p className="text-xs text-zinc-500 truncate">{user.email}</p></div>
                            <button onClick={() => { setUser(null); setQ(''); }} className="text-xs text-zinc-500 hover:text-red-600">changer</button>
                        </div>
                    ) : (
                        <div className="relative mb-3">
                            <Search className="absolute left-3 top-2.5 w-4 h-4 text-zinc-400" />
                            <input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Rechercher par nom ou e-mail…" className="ipt pl-9" />
                            {resultats.length > 0 && (
                                <div className="absolute z-10 mt-1 w-full bg-white border border-zinc-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                                    {resultats.map((u) => (
                                        <button key={u.id} onClick={() => { setUser(u); setResultats([]); }} className="w-full text-left px-3 py-2 hover:bg-zinc-50">
                                            <p className="text-sm font-medium text-zinc-900">{u.name}</p><p className="text-xs text-zinc-400">{u.email}</p>
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    {/* Round */}
                    <label className="block text-xs font-medium text-zinc-500 mb-1">Round</label>
                    <select className="ipt mb-3" value={roundId} onChange={(e) => setRoundId(e.target.value ? Number(e.target.value) : '')}>
                        <option value="">Choisir un round…</option>
                        {rounds.map((r) => <option key={r.id} value={r.id}>{r.name} — {fcfa(r.prix_part)}/part {r.actif ? '' : '(inactif)'}</option>)}
                    </select>

                    <div className="grid grid-cols-2 gap-3">
                        <div><label className="block text-xs font-medium text-zinc-500 mb-1">Nombre de parts</label><input type="number" className="ipt" value={parts} onChange={(e) => setParts(e.target.value)} placeholder="ex : 10" /></div>
                        <div><label className="block text-xs font-medium text-zinc-500 mb-1">Montant équivalent</label><div className="ipt bg-zinc-50 text-zinc-700 font-semibold">{montant ? fcfa(montant) : '—'}</div></div>
                    </div>
                    <div className="mt-3"><label className="block text-xs font-medium text-zinc-500 mb-1">Note (optionnel)</label><input className="ipt" value={note} onChange={(e) => setNote(e.target.value)} placeholder="Raison du crédit…" /></div>

                    <button onClick={crediter} disabled={busy || !user || !roundId || !parts}
                        className="mt-4 w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm flex items-center justify-center gap-2 disabled:opacity-60">
                        <Sparkles className="w-4 h-4" /> Créditer les parts
                    </button>
                </div>

                {/* Rounds */}
                <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                    <h2 className="font-bold text-zinc-900 mb-4">Rounds</h2>
                    <div className="space-y-2">
                        {rounds.map((r) => (
                            <div key={r.id} className="flex items-center gap-3 rounded-xl border border-zinc-100 p-3">
                                <div className="flex-1 min-w-0">
                                    <p className="font-semibold text-sm text-zinc-900 truncate">{r.name} {r.actif ? <span className="text-[10px] font-semibold text-emerald-700 bg-emerald-100 rounded px-1.5 py-0.5">actif</span> : <span className="text-[10px] font-semibold text-zinc-500 bg-zinc-100 rounded px-1.5 py-0.5">inactif</span>}</p>
                                    <p className="text-xs text-zinc-400">{fcfa(r.prix_part)}/part · {r.parts_vendues.toLocaleString('fr-FR')} parts vendues</p>
                                </div>
                                <span className="text-sm font-bold text-emerald-700">{fcfa(r.collecte)}</span>
                            </div>
                        ))}
                        {rounds.length === 0 && <p className="text-sm text-zinc-400 text-center py-6">Aucun round.</p>}
                    </div>
                </div>
            </div>

            {/* Derniers achats de parts */}
            <div className="mt-6 bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <div className="px-4 py-3 border-b border-zinc-100"><h2 className="font-bold text-zinc-900 text-sm">Derniers achats de parts</h2></div>
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Investisseur</th>
                                <th className="text-left px-4 py-3 font-medium">Round</th>
                                <th className="text-right px-4 py-3 font-medium">Parts</th>
                                <th className="text-right px-4 py-3 font-medium">Montant</th>
                                <th className="text-center px-4 py-3 font-medium">Statut</th>
                                <th className="text-left px-4 py-3 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {derniers.map((a) => (
                                <tr key={a.id} className="hover:bg-zinc-50">
                                    <td className="px-4 py-3 font-medium text-zinc-900">{a.user}{a.credit && <span className="ml-1.5 text-[10px] font-semibold text-violet-700 bg-violet-100 rounded px-1.5 py-0.5">crédit admin</span>}</td>
                                    <td className="px-4 py-3 text-zinc-600">{a.round}</td>
                                    <td className="px-4 py-3 text-right font-semibold text-zinc-900">{a.parts.toLocaleString('fr-FR')}</td>
                                    <td className="px-4 py-3 text-right text-zinc-700">{fcfa(a.montant)}</td>
                                    <td className="px-4 py-3 text-center"><span className={`text-[11px] font-semibold rounded-full px-2 py-0.5 ${a.status === 'Success' ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-500'}`}>{a.status}</span></td>
                                    <td className="px-4 py-3 text-zinc-400 whitespace-nowrap">{a.date}</td>
                                </tr>
                            ))}
                            {derniers.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-zinc-400">Aucun achat de parts.</td></tr>}
                        </tbody>
                    </table>
                </div>
            </div>

            <style>{`.ipt{width:100%;padding:.55rem .7rem;background:#fff;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.875rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </AdminLayout>
    );
}

function Stat({ label, value, icon: Icon, color }: { label: string; value: string; icon: any; color: string }) {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-4 flex items-center gap-3">
            <div className="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style={{ background: `${color}18` }}><Icon className="w-5 h-5" style={{ color }} /></div>
            <div className="min-w-0"><p className="text-lg font-bold text-zinc-900 leading-tight truncate">{value}</p><p className="text-xs text-zinc-500">{label}</p></div>
        </div>
    );
}

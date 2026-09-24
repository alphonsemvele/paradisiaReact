import { router, Head, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Ticket, CheckCircle2, Clock, Coins, Check, X, Smartphone } from 'lucide-react';

interface T {
    id: number; reference: string; code: string; type: string; type_libelle: string;
    montant: number; promo: boolean; moyen: string; statut: string;
    client: string | null; email: string | null; telephone: string | null;
    equipe: string | null; couleur: string | null; date: string; paye_le: string | null;
}
interface Props {
    tickets: T[];
    filtre: string | null;
    stats: { total: number; payes: number; en_attente: number; recette: number; participants: number; fans: number };
}

const fcfa = (n: number) => n.toLocaleString('fr-FR') + ' FCFA';

const STATUTS: Record<string, { label: string; cls: string }> = {
    paye: { label: 'Payé', cls: 'bg-emerald-100 text-emerald-700' },
    en_attente: { label: 'En attente', cls: 'bg-amber-100 text-amber-700' },
    echoue: { label: 'Échoué', cls: 'bg-red-100 text-red-600' },
    annule: { label: 'Annulé', cls: 'bg-zinc-100 text-zinc-500' },
};

export default function AdminFestyTickets({ tickets, filtre, stats }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;

    const filtrer = (s: string | null) => router.get('/admin/festy/tickets', s ? { statut: s } : {}, { preserveScroll: true, preserveState: true });

    const valider = (t: T) => {
        if (!confirm(`Valider le ticket ${t.code} de ${t.client} ? Le ticket lui sera envoyé par e-mail.`)) return;
        router.post(`/admin/festy/tickets/${t.id}/valider`, {}, { preserveScroll: true });
    };
    const refuser = (t: T) => {
        if (!confirm(`Annuler le ticket ${t.code} ?`)) return;
        router.post(`/admin/festy/tickets/${t.id}/refuser`, {}, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Tickets Festy">
            <Head title="Tickets Festy — Admin" />

            {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

            {/* Stats */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                <Stat icon={Coins} label="Recette" value={fcfa(stats.recette)} color="#14532d" />
                <Stat icon={CheckCircle2} label="Tickets payés" value={String(stats.payes)} color="#0d9488" />
                <Stat icon={Clock} label="En attente" value={String(stats.en_attente)} color={stats.en_attente ? '#d97706' : '#16a34a'} />
                <Stat icon={Ticket} label="Part. / Fans" value={`${stats.participants} / ${stats.fans}`} color="#E8792B" />
            </div>

            {/* Filtres */}
            <div className="flex flex-wrap gap-2 mb-4">
                {[
                    { k: null, l: 'Tous' },
                    { k: 'en_attente', l: 'En attente' },
                    { k: 'paye', l: 'Payés' },
                    { k: 'annule', l: 'Annulés' },
                    { k: 'echoue', l: 'Échoués' },
                ].map((f) => (
                    <button key={f.l} onClick={() => filtrer(f.k)}
                        className={`px-3 py-1.5 rounded-full text-xs font-semibold ${(filtre ?? null) === f.k ? 'bg-emerald-700 text-white' : 'bg-white border border-zinc-200 text-zinc-600 hover:bg-zinc-50'}`}>
                        {f.l}
                    </button>
                ))}
            </div>

            {/* Table */}
            <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Client</th>
                                <th className="text-left px-4 py-3 font-medium">Formule</th>
                                <th className="text-left px-4 py-3 font-medium">Équipe</th>
                                <th className="text-right px-4 py-3 font-medium">Montant</th>
                                <th className="text-left px-4 py-3 font-medium">Moyen</th>
                                <th className="text-center px-4 py-3 font-medium">Statut</th>
                                <th className="text-left px-4 py-3 font-medium">Code</th>
                                <th className="text-right px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {tickets.map((t) => (
                                <tr key={t.id} className="hover:bg-zinc-50">
                                    <td className="px-4 py-3">
                                        <p className="font-medium text-zinc-900">{t.client ?? '—'}</p>
                                        <p className="text-xs text-zinc-400">{t.telephone ?? t.email ?? ''}</p>
                                        <p className="text-[11px] text-zinc-400">{t.date}</p>
                                    </td>
                                    <td className="px-4 py-3 text-zinc-700">{t.type_libelle}</td>
                                    <td className="px-4 py-3">
                                        {t.equipe ? <span className="inline-flex items-center gap-1.5"><span className="w-2.5 h-2.5 rounded-full" style={{ background: t.couleur ?? '#999' }} />{t.equipe}</span> : <span className="text-zinc-400">—</span>}
                                    </td>
                                    <td className="px-4 py-3 text-right font-semibold text-zinc-900">{fcfa(t.montant)}{t.promo && <span className="ml-1 text-[10px] text-orange-500">promo</span>}</td>
                                    <td className="px-4 py-3">
                                        <span className="inline-flex items-center gap-1 text-xs text-zinc-600">
                                            {t.moyen === 'mtn' ? <><Smartphone className="w-3.5 h-3.5 text-amber-500" /> MTN</> : <>🟠 Orange</>}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <span className={`text-[11px] font-semibold rounded-full px-2 py-0.5 ${STATUTS[t.statut]?.cls ?? 'bg-zinc-100 text-zinc-500'}`}>{STATUTS[t.statut]?.label ?? t.statut}</span>
                                    </td>
                                    <td className="px-4 py-3 font-mono text-xs text-zinc-500">{t.code}</td>
                                    <td className="px-4 py-3 text-right whitespace-nowrap">
                                        {t.statut === 'en_attente' && (
                                            <div className="inline-flex gap-1.5">
                                                <button onClick={() => valider(t)} title="Valider" className="p-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"><Check className="w-4 h-4" /></button>
                                                <button onClick={() => refuser(t)} title="Annuler" className="p-1.5 rounded-lg bg-zinc-100 text-zinc-500 hover:bg-red-50 hover:text-red-600"><X className="w-4 h-4" /></button>
                                            </div>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {tickets.length === 0 && <tr><td colSpan={8} className="px-4 py-12 text-center text-zinc-400">Aucun ticket.</td></tr>}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}

function Stat({ icon: Icon, label, value, color }: { icon: any; label: string; value: string; color: string }) {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-4 flex items-center gap-3">
            <div className="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style={{ background: `${color}18` }}><Icon className="w-5 h-5" style={{ color }} /></div>
            <div className="min-w-0"><p className="text-lg font-bold text-zinc-900 leading-tight truncate">{value}</p><p className="text-xs text-zinc-500">{label}</p></div>
        </div>
    );
}

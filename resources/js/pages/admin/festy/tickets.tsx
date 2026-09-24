import { useEffect, useState } from 'react';
import { router, Head, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Ticket, CheckCircle2, Clock, Coins, Check, X, Smartphone, Gift, Search, UserCheck, Sparkles, Mail, Trash2 } from 'lucide-react';

interface T {
    id: number; reference: string; code: string; type: string; type_libelle: string;
    montant: number; promo: boolean; moyen: string; statut: string;
    client: string | null; email: string | null; telephone: string | null;
    equipe: string | null; couleur: string | null; date: string; paye_le: string | null;
}
interface Prix { type: string; montant: number; normal: number; promo: boolean }
interface Equipe { id: number; nom: string; couleur: string; places_restantes: number; occupees: number; limite: number; complet: boolean }
interface U {
    id: number; name: string; email: string; phone: string | null;
    // Équipe retenue par le participant lors de son inscription.
    equipe: { id: number; nom: string; couleur: string | null } | null;
}
interface Props {
    tickets: T[];
    filtre: string | null;
    equipes: Equipe[];
    prix: { participant: Prix; fan: Prix };
    stats: { total: number; payes: number; en_attente: number; recette: number; participants: number; fans: number };
}

const fcfa = (n: number) => n.toLocaleString('fr-FR') + ' FCFA';

const STATUTS: Record<string, { label: string; cls: string }> = {
    paye: { label: 'Payé', cls: 'bg-emerald-100 text-emerald-700' },
    en_attente: { label: 'En attente', cls: 'bg-amber-100 text-amber-700' },
    echoue: { label: 'Échoué', cls: 'bg-red-100 text-red-600' },
    annule: { label: 'Annulé', cls: 'bg-zinc-100 text-zinc-500' },
};

export default function AdminFestyTickets({ tickets, filtre, equipes, prix, stats }: Props) {
    const flashProps = (usePage().props as any).flash ?? {};
    const flash = flashProps.success as string | undefined;
    const flashErr = flashProps.error as string | undefined;

    const filtrer = (s: string | null) => router.get('/admin/festy/tickets', s ? { statut: s } : {}, { preserveScroll: true, preserveState: true });

    const valider = (t: T) => {
        if (!confirm(`Valider le ticket ${t.code} de ${t.client} ? Le ticket lui sera envoyé par e-mail.`)) return;
        router.post(`/admin/festy/tickets/${t.id}/valider`, {}, { preserveScroll: true });
    };
    const renvoyer = (t: T) => {
        router.post(`/admin/festy/tickets/${t.id}/renvoyer`, {}, { preserveScroll: true });
    };
    const refuser = (t: T) => {
        if (!confirm(`Annuler le ticket ${t.code} de ${t.client ?? ''} ?${t.statut === 'paye' ? ' Ce ticket est déjà payé.' : ''}`)) return;
        router.post(`/admin/festy/tickets/${t.id}/refuser`, {}, { preserveScroll: true });
    };
    const supprimer = (t: T) => {
        if (!confirm(`Supprimer DÉFINITIVEMENT le ticket ${t.code} de ${t.client ?? ''} ? Cette action est irréversible.`)) return;
        router.delete(`/admin/festy/tickets/${t.id}`, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Tickets Festy">
            <Head title="Tickets Festy — Admin" />

            {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}
            {flashErr && <div className="mb-4 rounded-lg bg-amber-50 border border-amber-300 text-amber-800 px-4 py-2.5 text-sm">{flashErr}</div>}

            {/* Stats */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                <Stat icon={Coins} label="Recette" value={fcfa(stats.recette)} color="#14532d" />
                <Stat icon={CheckCircle2} label="Tickets payés" value={String(stats.payes)} color="#0d9488" />
                <Stat icon={Clock} label="En attente" value={String(stats.en_attente)} color={stats.en_attente ? '#d97706' : '#16a34a'} />
                <Stat icon={Ticket} label="Part. / Fans" value={`${stats.participants} / ${stats.fans}`} color="#E8792B" />
            </div>

            {/* Places participants par équipe */}
            <div className="bg-white rounded-2xl border border-zinc-200 p-4 mb-5">
                <p className="text-xs font-bold text-zinc-700 mb-3">Places participants par équipe</p>
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    {equipes.map((e) => (
                        <div key={e.id} className="rounded-xl border border-zinc-100 p-3">
                            <div className="flex items-center gap-1.5 mb-1.5">
                                <span className="w-2.5 h-2.5 rounded-full" style={{ background: e.couleur }} />
                                <span className="text-sm font-semibold text-zinc-800 truncate">{e.nom}</span>
                            </div>
                            <p className="text-lg font-extrabold" style={{ color: e.complet ? '#dc2626' : '#14532d' }}>{e.places_restantes}<span className="text-xs font-medium text-zinc-400"> / {e.limite}</span></p>
                            <div className="mt-1.5 h-1.5 w-full rounded-full bg-zinc-100 overflow-hidden">
                                <div className="h-full rounded-full" style={{ width: `${Math.min(100, (e.occupees / Math.max(1, e.limite)) * 100)}%`, background: e.complet ? '#dc2626' : e.couleur }} />
                            </div>
                            <p className="text-[10px] text-zinc-400 mt-1">{e.complet ? 'Complet' : `${e.occupees} pris`}</p>
                        </div>
                    ))}
                </div>
            </div>

            {/* Activer un ticket pour un utilisateur */}
            <ActiverTicket equipes={equipes} prix={prix} />

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
                                            {t.moyen === 'mtn' ? <><Smartphone className="w-3.5 h-3.5 text-amber-500" /> MTN</>
                                                : t.moyen === 'offert' ? <><Gift className="w-3.5 h-3.5 text-violet-500" /> Admin</>
                                                : <>🟠 Orange</>}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        <span className={`text-[11px] font-semibold rounded-full px-2 py-0.5 ${STATUTS[t.statut]?.cls ?? 'bg-zinc-100 text-zinc-500'}`}>{STATUTS[t.statut]?.label ?? t.statut}</span>
                                    </td>
                                    <td className="px-4 py-3 font-mono text-xs text-zinc-500">{t.code}</td>
                                    <td className="px-4 py-3 text-right whitespace-nowrap">
                                        <div className="inline-flex gap-1.5">
                                            {t.statut === 'en_attente' && (
                                                <button onClick={() => valider(t)} title="Valider et envoyer" className="p-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"><Check className="w-4 h-4" /></button>
                                            )}
                                            {t.statut === 'paye' && (
                                                <button onClick={() => renvoyer(t)} title="Renvoyer l'e-mail" className="p-1.5 rounded-lg bg-zinc-100 text-zinc-600 hover:bg-emerald-50 hover:text-emerald-700"><Mail className="w-4 h-4" /></button>
                                            )}
                                            {t.statut !== 'annule' && (
                                                <button onClick={() => refuser(t)} title="Annuler le ticket" className="p-1.5 rounded-lg bg-zinc-100 text-zinc-500 hover:bg-amber-50 hover:text-amber-600"><X className="w-4 h-4" /></button>
                                            )}
                                            <button onClick={() => supprimer(t)} title="Supprimer définitivement" className="p-1.5 rounded-lg bg-zinc-100 text-zinc-500 hover:bg-red-50 hover:text-red-600"><Trash2 className="w-4 h-4" /></button>
                                        </div>
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

function ActiverTicket({ equipes, prix }: { equipes: Equipe[]; prix: { participant: Prix; fan: Prix } }) {
    const [ouvert, setOuvert] = useState(false);
    const [q, setQ] = useState('');
    const [resultats, setResultats] = useState<U[]>([]);
    const [user, setUser] = useState<U | null>(null);
    const [type, setType] = useState<'participant' | 'fan'>('participant');
    const [teamId, setTeamId] = useState<number | ''>('');
    const [montant, setMontant] = useState<string>('');
    const [busy, setBusy] = useState(false);

    // Prix par défaut quand on change de formule (montant reste modifiable).
    useEffect(() => { setMontant(String(prix[type].montant)); }, [type, prix]);

    useEffect(() => {
        if (user || !ouvert) return;
        const t = setTimeout(async () => {
            try {
                const r = await fetch(`/admin/festy/tickets/users?q=${encodeURIComponent(q)}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (r.ok) { const d = await r.json(); setResultats(d.users ?? []); }
            } catch { /* silencieux */ }
        }, 250);
        return () => clearTimeout(t);
    }, [q, user, ouvert]);

    const activer = () => {
        if (!user) return;
        setBusy(true);
        router.post('/admin/festy/tickets/activer', {
            user_id: user.id, type, festy_team_id: teamId || null, montant: montant ? Number(montant) : null,
        }, {
            preserveScroll: true,
            onSuccess: () => { setUser(null); setQ(''); setTeamId(''); setResultats([]); setOuvert(false); },
            onFinish: () => setBusy(false),
        });
    };

    if (!ouvert) {
        return (
            <div className="mb-5">
                <button onClick={() => setOuvert(true)} className="inline-flex items-center gap-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5">
                    <Gift className="w-4 h-4" /> Activer un ticket pour un utilisateur
                </button>
            </div>
        );
    }

    return (
        <div className="mb-5 bg-white rounded-2xl border-2 border-violet-200 p-5">
            <div className="flex items-center justify-between mb-1">
                <h2 className="font-bold text-zinc-900 flex items-center gap-2"><Gift className="w-5 h-5 text-violet-600" /> Activer un ticket</h2>
                <button onClick={() => setOuvert(false)} className="text-zinc-400 hover:text-zinc-700"><X className="w-5 h-5" /></button>
            </div>
            <p className="text-xs text-zinc-500 mb-4">Recherche l'utilisateur, choisis la formule, puis active — le ticket lui est envoyé par e-mail aussitôt.</p>

            {/* Recherche utilisateur */}
            <label className="block text-xs font-medium text-zinc-500 mb-1">Utilisateur</label>
            {user ? (
                <div className="flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-3 py-2 mb-3">
                    <UserCheck className="w-4 h-4 text-violet-600" />
                    <div className="flex-1 min-w-0"><p className="text-sm font-semibold text-zinc-900 truncate">{user.name}</p><p className="text-xs text-zinc-500 truncate">
                        {user.email}{user.phone ? ' · ' + user.phone : ''}
                        {user.equipe && <span className="ml-1 font-medium text-violet-600">· {user.equipe.nom}</span>}
                    </p></div>
                    <button onClick={() => { setUser(null); setQ(''); setTeamId(''); }} className="text-xs text-zinc-500 hover:text-red-600">changer</button>
                </div>
            ) : (
                <div className="relative mb-3">
                    <Search className="absolute left-3 top-2.5 w-4 h-4 text-zinc-400" />
                    <input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Nom, e-mail ou téléphone…" autoFocus
                        className="w-full pl-9 pr-3 py-2.5 rounded-xl border border-zinc-200 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                    {resultats.length > 0 && (
                        <div className="absolute z-10 mt-1 w-full bg-white border border-zinc-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                            {resultats.map((u) => (
                                <button key={u.id} onClick={() => { setUser(u); setTeamId(u.equipe?.id ?? ''); setResultats([]); }} className="w-full text-left px-3 py-2 hover:bg-zinc-50">
                                    <p className="text-sm font-medium text-zinc-900">{u.name}</p>
                                    <p className="text-xs text-zinc-400">
                                        {u.email}{u.phone ? ' · ' + u.phone : ''}
                                        {u.equipe && <span className="ml-1 font-medium text-violet-600">· {u.equipe.nom}</span>}
                                    </p>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            )}

            <div className="grid grid-cols-2 gap-3">
                <div>
                    <label className="block text-xs font-medium text-zinc-500 mb-1">Formule</label>
                    <select className="w-full px-3 py-2.5 rounded-xl border border-zinc-200 text-sm bg-white" value={type} onChange={(e) => setType(e.target.value as 'participant' | 'fan')}>
                        <option value="participant">Participant — {fcfa(prix.participant.montant)}</option>
                        <option value="fan">Fan — {fcfa(prix.fan.montant)}</option>
                    </select>
                </div>
                <div>
                    <label className="block text-xs font-medium text-zinc-500 mb-1">
                        Équipe {user?.equipe ? <span className="text-violet-600">— reprise de son inscription</span> : '(optionnel)'}
                    </label>
                    <select className="w-full px-3 py-2.5 rounded-xl border border-zinc-200 text-sm bg-white" value={teamId} onChange={(e) => setTeamId(e.target.value ? Number(e.target.value) : '')}>
                        <option value="">{user?.equipe ? 'Aucune' : '— équipe actuelle —'}</option>
                        {equipes.map((e) => <option key={e.id} value={e.id}>{e.nom}</option>)}
                    </select>
                    {user?.equipe && (
                        <p className="mt-1 text-[11px] leading-relaxed text-zinc-400">
                            Ne le changez que pour corriger une erreur : le participant a choisi
                            <strong className="text-zinc-600"> {user.equipe.nom}</strong> lui-même.
                        </p>
                    )}
                </div>
            </div>

            <div className="mt-3">
                <label className="block text-xs font-medium text-zinc-500 mb-1">Montant payé (FCFA)</label>
                <input type="number" className="w-full px-3 py-2.5 rounded-xl border border-zinc-200 text-sm" value={montant} onChange={(e) => setMontant(e.target.value)} />
            </div>

            <button onClick={activer} disabled={busy || !user}
                className="mt-4 w-full py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-semibold text-sm flex items-center justify-center gap-2 disabled:opacity-60">
                <Sparkles className="w-4 h-4" /> Activer et envoyer le ticket
            </button>
        </div>
    );
}

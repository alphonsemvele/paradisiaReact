import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Users, Download, Phone, Mail, MapPin, Pencil, Trash2, X } from 'lucide-react';

interface Inscrit {
    id: number;
    nom: string;
    prenom: string | null;
    telephone: string;
    email: string | null;
    ville: string | null;
    quartier: string | null;
    festy_team_id: number;
    equipe: string | null;
    date: string;
}
interface Props {
    inscrits: Inscrit[];
    equipes: { id: number; nom: string }[];
    filtre: number | null;
    total: number;
    par_equipe: { nom: string; couleur: string; membres: number }[];
}

export default function FestyInscrits({ inscrits, equipes, filtre, total, par_equipe }: Props) {
    const [edition, setEdition] = useState<Inscrit | null>(null);
    const [busy, setBusy] = useState(false);

    const filtrer = (id: number | null) => {
        router.get('/admin/festy/inscrits', id ? { equipe: id } : {}, { preserveScroll: true, preserveState: true });
    };

    const enregistrer = () => {
        if (!edition) return;
        setBusy(true);
        router.post(`/admin/festy/inscrits/${edition.id}`, {
            _method: 'PATCH',
            festy_team_id: edition.festy_team_id,
            nom: edition.nom ?? '',
            prenom: edition.prenom ?? '',
            telephone: edition.telephone ?? '',
            email: edition.email ?? '',
            ville: edition.ville ?? '',
            quartier: edition.quartier ?? '',
        }, { preserveScroll: true, onSuccess: () => setEdition(null), onFinish: () => setBusy(false) });
    };

    const supprimer = (i: Inscrit) => {
        if (!confirm(`Supprimer l'inscription de ${i.nom} ${i.prenom ?? ''} ?`)) return;
        router.delete(`/admin/festy/inscrits/${i.id}`, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Inscrits Festy">
            <Head title="Inscrits Festy — Admin" />

            <div className="flex items-center justify-between mb-5">
                <div>
                    <h1 className="text-xl font-bold text-zinc-900">Inscrits Paradisia Festy</h1>
                    <p className="text-sm text-zinc-500">{total} inscription{total > 1 ? 's' : ''} au total</p>
                </div>
                <a href="/admin/festy/export"
                    className="inline-flex items-center gap-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2.5">
                    <Download className="w-4 h-4" /> Exporter (CSV)
                </a>
            </div>

            {/* Répartition par équipe */}
            <div className="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
                {par_equipe.map((e) => (
                    <div key={e.nom} className="bg-white rounded-xl border border-zinc-200 p-3 text-center">
                        <p className="text-2xl font-bold" style={{ color: e.couleur }}>{e.membres}</p>
                        <p className="text-xs text-zinc-500">{e.nom}</p>
                    </div>
                ))}
            </div>

            {/* Filtres */}
            <div className="flex flex-wrap gap-2 mb-4">
                <Chip actif={!filtre} onClick={() => filtrer(null)}>Toutes</Chip>
                {equipes.map((eq) => (
                    <Chip key={eq.id} actif={filtre === eq.id} onClick={() => filtrer(eq.id)}>{eq.nom}</Chip>
                ))}
            </div>

            {/* Tableau */}
            <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Nom</th>
                                <th className="text-left px-4 py-3 font-medium">Prénom</th>
                                <th className="text-left px-4 py-3 font-medium">Équipe</th>
                                <th className="text-left px-4 py-3 font-medium">Téléphone</th>
                                <th className="text-left px-4 py-3 font-medium">E-mail</th>
                                <th className="text-left px-4 py-3 font-medium">Ville</th>
                                <th className="text-left px-4 py-3 font-medium">Quartier</th>
                                <th className="text-left px-4 py-3 font-medium">Date</th>
                                <th className="text-right px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {inscrits.map((i) => (
                                <tr key={i.id} className="hover:bg-zinc-50">
                                    <td className="px-4 py-3 font-medium text-zinc-900">{i.nom}</td>
                                    <td className="px-4 py-3 text-zinc-600">{i.prenom ?? '—'}</td>
                                    <td className="px-4 py-3 text-zinc-600">{i.equipe ?? '—'}</td>
                                    <td className="px-4 py-3 text-zinc-600"><a href={`tel:${i.telephone}`} className="hover:text-emerald-700 inline-flex items-center gap-1"><Phone className="w-3 h-3" />{i.telephone}</a></td>
                                    <td className="px-4 py-3 text-zinc-600">{i.email ? <span className="inline-flex items-center gap-1"><Mail className="w-3 h-3" />{i.email}</span> : '—'}</td>
                                    <td className="px-4 py-3 text-zinc-600">{i.ville ? <span className="inline-flex items-center gap-1"><MapPin className="w-3 h-3" />{i.ville}</span> : '—'}</td>
                                    <td className="px-4 py-3 text-zinc-600">{i.quartier ?? '—'}</td>
                                    <td className="px-4 py-3 text-zinc-400 whitespace-nowrap">{i.date}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center justify-end gap-1">
                                            <button onClick={() => setEdition({ ...i })} className="p-2 text-zinc-500 hover:text-emerald-700" title="Modifier"><Pencil className="w-4 h-4" /></button>
                                            <button onClick={() => supprimer(i)} className="p-2 text-zinc-500 hover:text-red-600" title="Supprimer"><Trash2 className="w-4 h-4" /></button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {inscrits.length === 0 && (
                                <tr><td colSpan={9} className="px-4 py-12 text-center text-zinc-400">
                                    <Users className="w-8 h-8 mx-auto mb-2 opacity-40" /> Aucun inscrit pour ce filtre.
                                </td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modale d'édition */}
            {edition && (
                <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onClick={() => setEdition(null)}>
                    <div className="bg-white rounded-2xl w-full max-w-md p-6" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="font-bold text-zinc-900">Modifier l'inscrit</h3>
                            <button onClick={() => setEdition(null)} className="text-zinc-400 hover:text-zinc-700"><X className="w-5 h-5" /></button>
                        </div>
                        <div className="space-y-3">
                            <div className="grid grid-cols-2 gap-3">
                                <L label="Nom"><input className="ipt" value={edition.nom ?? ''} onChange={(e) => setEdition({ ...edition, nom: e.target.value })} /></L>
                                <L label="Prénom"><input className="ipt" value={edition.prenom ?? ''} onChange={(e) => setEdition({ ...edition, prenom: e.target.value })} /></L>
                            </div>
                            <L label="Équipe">
                                <select className="ipt" value={edition.festy_team_id} onChange={(e) => setEdition({ ...edition, festy_team_id: Number(e.target.value) })}>
                                    {equipes.map((eq) => <option key={eq.id} value={eq.id}>{eq.nom}</option>)}
                                </select>
                            </L>
                            <L label="Téléphone / WhatsApp"><input className="ipt" value={edition.telephone ?? ''} onChange={(e) => setEdition({ ...edition, telephone: e.target.value })} /></L>
                            <L label="E-mail"><input className="ipt" value={edition.email ?? ''} onChange={(e) => setEdition({ ...edition, email: e.target.value })} /></L>
                            <div className="grid grid-cols-2 gap-3">
                                <L label="Ville"><input className="ipt" value={edition.ville ?? ''} onChange={(e) => setEdition({ ...edition, ville: e.target.value })} /></L>
                                <L label="Quartier"><input className="ipt" value={edition.quartier ?? ''} onChange={(e) => setEdition({ ...edition, quartier: e.target.value })} /></L>
                            </div>
                        </div>
                        <button onClick={enregistrer} disabled={busy}
                            className="mt-5 w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm disabled:opacity-60">
                            Enregistrer
                        </button>
                    </div>
                    <style>{`.ipt{width:100%;padding:.55rem .7rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.875rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
                </div>
            )}
        </AdminLayout>
    );
}

function L({ label, children }: { label: string; children: React.ReactNode }) {
    return (
        <label className="block">
            <span className="block text-xs font-medium text-zinc-500 mb-1">{label}</span>
            {children}
        </label>
    );
}

function Chip({ actif, onClick, children }: { actif: boolean; onClick: () => void; children: React.ReactNode }) {
    return (
        <button onClick={onClick}
            className={`px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors ${actif ? 'bg-emerald-700 text-white' : 'bg-white border border-zinc-200 text-zinc-600 hover:bg-zinc-50'}`}>
            {children}
        </button>
    );
}

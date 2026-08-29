import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Boxes, Plus, Pencil, Trash2, X, AlertTriangle, Package, Wine, Sprout, History, SlidersHorizontal } from 'lucide-react';

interface Materiel {
    id: number; nom: string; type: string; unite: string; stock: number;
    seuil_alerte: number; consignable: boolean; actif: boolean; alerte: boolean;
}
interface Props {
    materiels: Materiel[];
    filtre: string | null;
    stats: { total: number; alertes: number; cartons: number; bouteilles: number };
}

const TYPES: Record<string, { label: string; icon: any; color: string }> = {
    carton: { label: 'Carton', icon: Package, color: '#E8792B' },
    bouteille: { label: 'Bouteille', icon: Wine, color: '#0d9488' },
    matiere_premiere: { label: 'Matière première', icon: Sprout, color: '#16a34a' },
    autre: { label: 'Autre', icon: Boxes, color: '#6b7280' },
};

const vide = { nom: '', type: 'carton', unite: 'pièce', stock: 0, seuil_alerte: 0, consignable: true, actif: true };

export default function StockIndex({ materiels, filtre, stats }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;
    const [edition, setEdition] = useState<any>(null);
    const [ajust, setAjust] = useState<Materiel | null>(null);
    const [ajustQte, setAjustQte] = useState('');
    const [ajustMotif, setAjustMotif] = useState('');

    const filtrer = (t: string | null) => router.get('/admin/stock', t ? { type: t } : {}, { preserveScroll: true, preserveState: true });

    const enregistrer = () => {
        const payload = { ...edition, consignable: edition.consignable ? 1 : 0, actif: edition.actif ? 1 : 0 };
        if (edition.id) router.post(`/admin/stock/${edition.id}`, { ...payload, _method: 'PATCH' }, { preserveScroll: true, onSuccess: () => setEdition(null) });
        else router.post('/admin/stock', payload, { preserveScroll: true, onSuccess: () => setEdition(null) });
    };
    const supprimer = (m: Materiel) => { if (confirm(`Supprimer « ${m.nom} » ?`)) router.delete(`/admin/stock/${m.id}`, { preserveScroll: true }); };
    const ajusterStock = () => {
        if (!ajust || !ajustQte) return;
        router.post(`/admin/stock/${ajust.id}/ajuster`, { quantite: Number(ajustQte), motif: ajustMotif }, {
            preserveScroll: true, onSuccess: () => { setAjust(null); setAjustQte(''); setAjustMotif(''); },
        });
    };

    return (
        <AdminLayout title="Matériel & stock">
            <Head title="Matériel & stock — Admin" />

            <div className="flex items-center justify-between mb-5">
                <div>
                    <h1 className="text-xl font-bold text-zinc-900">Matériel & stock</h1>
                    <p className="text-sm text-zinc-500">Cartons, bouteilles, matières premières…</p>
                </div>
                <button onClick={() => setEdition({ ...vide })} className="inline-flex items-center gap-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2.5">
                    <Plus className="w-4 h-4" /> Nouveau matériel
                </button>
            </div>

            {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

            {/* Stats */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                <Stat label="Matériels" value={stats.total} icon={Boxes} color="#14532d" />
                <Stat label="En alerte" value={stats.alertes} icon={AlertTriangle} color={stats.alertes ? '#dc2626' : '#16a34a'} />
                <Stat label="Cartons (stock)" value={stats.cartons} icon={Package} color="#E8792B" />
                <Stat label="Bouteilles (stock)" value={stats.bouteilles} icon={Wine} color="#0d9488" />
            </div>

            {/* Filtres */}
            <div className="flex flex-wrap gap-2 mb-4">
                <Chip actif={!filtre} onClick={() => filtrer(null)}>Tous</Chip>
                {Object.entries(TYPES).map(([k, v]) => <Chip key={k} actif={filtre === k} onClick={() => filtrer(k)}>{v.label}</Chip>)}
            </div>

            {/* Tableau */}
            <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Matériel</th>
                                <th className="text-left px-4 py-3 font-medium">Type</th>
                                <th className="text-right px-4 py-3 font-medium">Stock</th>
                                <th className="text-right px-4 py-3 font-medium">Seuil</th>
                                <th className="text-center px-4 py-3 font-medium">Consignable</th>
                                <th className="text-right px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {materiels.map((m) => {
                                const t = TYPES[m.type] ?? TYPES.autre;
                                return (
                                    <tr key={m.id} className={`hover:bg-zinc-50 ${m.alerte ? 'bg-red-50/40' : ''}`}>
                                        <td className="px-4 py-3 font-medium text-zinc-900">{m.nom}{!m.actif && <span className="text-zinc-400 font-normal"> · inactif</span>}</td>
                                        <td className="px-4 py-3"><span className="inline-flex items-center gap-1.5 text-xs font-semibold" style={{ color: t.color }}><t.icon className="w-3.5 h-3.5" />{t.label}</span></td>
                                        <td className="px-4 py-3 text-right font-bold" style={{ color: m.alerte ? '#dc2626' : '#111827' }}>
                                            {m.alerte && <AlertTriangle className="w-3.5 h-3.5 inline mr-1 -mt-0.5" />}
                                            {m.stock.toLocaleString('fr-FR')} <span className="text-xs font-normal text-zinc-400">{m.unite}</span>
                                        </td>
                                        <td className="px-4 py-3 text-right text-zinc-500">{m.seuil_alerte || '—'}</td>
                                        <td className="px-4 py-3 text-center">{m.consignable ? '✅' : '—'}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-1">
                                                <button onClick={() => setAjust(m)} className="p-2 text-zinc-500 hover:text-emerald-700" title="Ajuster le stock"><SlidersHorizontal className="w-4 h-4" /></button>
                                                <Link href={`/admin/stock/${m.id}/historique`} className="p-2 text-zinc-500 hover:text-emerald-700" title="Historique"><History className="w-4 h-4" /></Link>
                                                <button onClick={() => setEdition({ ...m })} className="p-2 text-zinc-500 hover:text-emerald-700" title="Modifier"><Pencil className="w-4 h-4" /></button>
                                                <button onClick={() => supprimer(m)} className="p-2 text-zinc-500 hover:text-red-600" title="Supprimer"><Trash2 className="w-4 h-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                            {materiels.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-zinc-400"><Boxes className="w-8 h-8 mx-auto mb-2 opacity-40" /> Aucun matériel. Ajoute cartons, bouteilles, matières…</td></tr>}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modale ajout/édition */}
            {edition && (
                <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onClick={() => setEdition(null)}>
                    <div className="bg-white rounded-2xl w-full max-w-md p-6" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="font-bold text-zinc-900">{edition.id ? 'Modifier le matériel' : 'Nouveau matériel'}</h3>
                            <button onClick={() => setEdition(null)} className="text-zinc-400 hover:text-zinc-700"><X className="w-5 h-5" /></button>
                        </div>
                        <div className="space-y-3">
                            <L label="Nom"><input className="ipt" value={edition.nom} onChange={(e) => setEdition({ ...edition, nom: e.target.value })} placeholder="Carton 12 jus, Bouteille 50cl…" /></L>
                            <div className="grid grid-cols-2 gap-3">
                                <L label="Type"><select className="ipt" value={edition.type} onChange={(e) => setEdition({ ...edition, type: e.target.value })}>{Object.entries(TYPES).map(([k, v]) => <option key={k} value={k}>{v.label}</option>)}</select></L>
                                <L label="Unité"><input className="ipt" value={edition.unite} onChange={(e) => setEdition({ ...edition, unite: e.target.value })} placeholder="pièce, kg, litre…" /></L>
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                {!edition.id && <L label="Stock initial"><input type="number" className="ipt" value={edition.stock} onChange={(e) => setEdition({ ...edition, stock: e.target.value })} /></L>}
                                <L label="Seuil d'alerte"><input type="number" className="ipt" value={edition.seuil_alerte} onChange={(e) => setEdition({ ...edition, seuil_alerte: e.target.value })} /></L>
                            </div>
                            <div className="flex gap-4">
                                <label className="flex items-center gap-2 text-sm text-zinc-700"><input type="checkbox" checked={!!edition.consignable} onChange={(e) => setEdition({ ...edition, consignable: e.target.checked })} /> Consignable (rendable)</label>
                                <label className="flex items-center gap-2 text-sm text-zinc-700"><input type="checkbox" checked={!!edition.actif} onChange={(e) => setEdition({ ...edition, actif: e.target.checked })} /> Actif</label>
                            </div>
                        </div>
                        <button onClick={enregistrer} disabled={!edition.nom} className="mt-5 w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm disabled:opacity-60">Enregistrer</button>
                    </div>
                </div>
            )}

            {/* Modale ajustement */}
            {ajust && (
                <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onClick={() => setAjust(null)}>
                    <div className="bg-white rounded-2xl w-full max-w-sm p-6" onClick={(e) => e.stopPropagation()}>
                        <h3 className="font-bold text-zinc-900 mb-1">Ajuster le stock</h3>
                        <p className="text-sm text-zinc-500 mb-4">{ajust.nom} — stock actuel : <b>{ajust.stock} {ajust.unite}</b></p>
                        <L label="Quantité (+ entrée, − sortie)"><input type="number" className="ipt" value={ajustQte} onChange={(e) => setAjustQte(e.target.value)} placeholder="ex : 50 ou -10" autoFocus /></L>
                        <div className="mt-3"><L label="Motif (optionnel)"><input className="ipt" value={ajustMotif} onChange={(e) => setAjustMotif(e.target.value)} placeholder="Inventaire, correction…" /></L></div>
                        <button onClick={ajusterStock} disabled={!ajustQte} className="mt-5 w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm disabled:opacity-60">Valider l'ajustement</button>
                    </div>
                </div>
            )}

            <style>{`.ipt{width:100%;padding:.55rem .7rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.875rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </AdminLayout>
    );
}

function Stat({ label, value, icon: Icon, color }: { label: string; value: number; icon: any; color: string }) {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-4 flex items-center gap-3">
            <div className="w-11 h-11 rounded-xl flex items-center justify-center" style={{ background: `${color}18` }}><Icon className="w-5 h-5" style={{ color }} /></div>
            <div><p className="text-2xl font-bold text-zinc-900 leading-none">{Number(value).toLocaleString('fr-FR')}</p><p className="text-xs text-zinc-500 mt-1">{label}</p></div>
        </div>
    );
}
function L({ label, children }: { label: string; children: React.ReactNode }) {
    return <label className="block"><span className="block text-xs font-medium text-zinc-500 mb-1">{label}</span>{children}</label>;
}
function Chip({ actif, onClick, children }: { actif: boolean; onClick: () => void; children: React.ReactNode }) {
    return <button onClick={onClick} className={`px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors ${actif ? 'bg-emerald-700 text-white' : 'bg-white border border-zinc-200 text-zinc-600 hover:bg-zinc-50'}`}>{children}</button>;
}

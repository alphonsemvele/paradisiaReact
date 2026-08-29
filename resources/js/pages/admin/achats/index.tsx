import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { ShoppingCart, Plus, Trash2, Package, Factory, Eye } from 'lucide-react';

interface Mat { id: number; nom: string; unite: string; type: string }
interface Achat { id: number; fournisseur: string | null; date: string; total: number; pour_production: boolean; nb_lignes: number; note: string | null }
interface Props {
    achats: Achat[];
    materiels: Mat[];
    stats: { total_mois: number; nb: number };
}

interface Ligne { material_id: number | ''; quantite: string; cout_unitaire: string }

export default function AchatsIndex({ achats, materiels, stats }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;
    const today = new Date().toISOString().slice(0, 10);
    const [fournisseur, setFournisseur] = useState('');
    const [date, setDate] = useState(today);
    const [pourProd, setPourProd] = useState(false);
    const [note, setNote] = useState('');
    const [lignes, setLignes] = useState<Ligne[]>([{ material_id: '', quantite: '', cout_unitaire: '' }]);
    const [busy, setBusy] = useState(false);

    const total = lignes.reduce((s, l) => s + (Number(l.quantite) || 0) * (Number(l.cout_unitaire) || 0), 0);

    const setLigne = (i: number, patch: Partial<Ligne>) => setLignes((arr) => arr.map((l, j) => (j === i ? { ...l, ...patch } : l)));
    const ajouterLigne = () => setLignes((a) => [...a, { material_id: '', quantite: '', cout_unitaire: '' }]);
    const retirerLigne = (i: number) => setLignes((a) => (a.length > 1 ? a.filter((_, j) => j !== i) : a));

    const enregistrer = () => {
        const items = lignes.filter((l) => l.material_id && Number(l.quantite) > 0)
            .map((l) => ({ material_id: l.material_id, quantite: Number(l.quantite), cout_unitaire: Number(l.cout_unitaire) || 0 }));
        if (items.length === 0) { alert('Ajoute au moins une ligne (matériel + quantité).'); return; }
        setBusy(true);
        router.post('/admin/achats', { fournisseur, date_achat: date, pour_production: pourProd ? 1 : 0, note, items }, {
            preserveScroll: true,
            onSuccess: () => { setFournisseur(''); setNote(''); setPourProd(false); setLignes([{ material_id: '', quantite: '', cout_unitaire: '' }]); },
            onFinish: () => setBusy(false),
        });
    };

    return (
        <AdminLayout title="Achats">
            <Head title="Achats — Admin" />

            <div className="mb-5">
                <h1 className="text-xl font-bold text-zinc-900">Achats</h1>
                <p className="text-sm text-zinc-500">Répertorie tout : matériel, matières premières, achats pour la production.</p>
            </div>

            {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

            <div className="grid lg:grid-cols-2 gap-6">
                {/* Nouveau */}
                <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                    <h2 className="font-bold text-zinc-900 mb-4 flex items-center gap-2"><ShoppingCart className="w-5 h-5 text-emerald-600" /> Nouvel achat</h2>
                    <div className="space-y-3">
                        <div className="grid grid-cols-2 gap-3">
                            <L label="Fournisseur (optionnel)"><input className="ipt" value={fournisseur} onChange={(e) => setFournisseur(e.target.value)} placeholder="Nom du fournisseur" /></L>
                            <L label="Date"><input type="date" className="ipt" value={date} onChange={(e) => setDate(e.target.value)} /></L>
                        </div>
                        <label className="flex items-center gap-2 text-sm text-zinc-700 cursor-pointer">
                            <input type="checkbox" checked={pourProd} onChange={(e) => setPourProd(e.target.checked)} /> <Factory className="w-4 h-4 text-zinc-400" /> Achat pour la production
                        </label>

                        {/* Lignes */}
                        <div className="space-y-2 pt-1">
                            {lignes.map((l, i) => (
                                <div key={i} className="grid grid-cols-[1fr_70px_80px_auto] gap-2 items-center">
                                    <select className="ipt" value={l.material_id} onChange={(e) => setLigne(i, { material_id: e.target.value ? Number(e.target.value) : '' })}>
                                        <option value="">Matériel…</option>
                                        {materiels.map((m) => <option key={m.id} value={m.id}>{m.nom}</option>)}
                                    </select>
                                    <input className="ipt" type="number" placeholder="Qté" value={l.quantite} onChange={(e) => setLigne(i, { quantite: e.target.value })} />
                                    <input className="ipt" type="number" placeholder="P.U." value={l.cout_unitaire} onChange={(e) => setLigne(i, { cout_unitaire: e.target.value })} />
                                    <button onClick={() => retirerLigne(i)} className="p-2 text-zinc-400 hover:text-red-600"><Trash2 className="w-4 h-4" /></button>
                                </div>
                            ))}
                            <button onClick={ajouterLigne} className="text-sm font-semibold text-emerald-700 hover:text-emerald-900 flex items-center gap-1"><Plus className="w-4 h-4" /> Ajouter une ligne</button>
                        </div>

                        <L label="Note (optionnel)"><textarea className="ipt" rows={2} value={note} onChange={(e) => setNote(e.target.value)} /></L>

                        <div className="flex items-center justify-between pt-1">
                            <span className="text-sm text-zinc-500">Total : <b className="text-zinc-900">{total.toLocaleString('fr-FR')} FCFA</b></span>
                            <button onClick={enregistrer} disabled={busy} className="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm disabled:opacity-60">
                                Enregistrer l'achat
                            </button>
                        </div>
                    </div>
                </div>

                {/* Liste */}
                <div>
                    <div className="grid grid-cols-2 gap-3 mb-4">
                        <div className="bg-white rounded-2xl border border-zinc-200 p-4"><p className="text-xs text-zinc-500">Achats (mois)</p><p className="text-xl font-bold text-emerald-700">{stats.total_mois.toLocaleString('fr-FR')} FCFA</p></div>
                        <div className="bg-white rounded-2xl border border-zinc-200 p-4"><p className="text-xs text-zinc-500">Total achats</p><p className="text-xl font-bold text-zinc-900">{stats.nb}</p></div>
                    </div>
                    <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                        <div className="px-4 py-3 border-b border-zinc-100"><h2 className="font-bold text-zinc-900 text-sm">Derniers achats</h2></div>
                        <div className="divide-y divide-zinc-100 max-h-[28rem] overflow-y-auto">
                            {achats.length === 0 && <p className="text-center text-zinc-400 py-10 text-sm"><Package className="w-8 h-8 mx-auto mb-2 opacity-40" /> Aucun achat.</p>}
                            {achats.map((a) => (
                                <Link key={a.id} href={`/admin/achats/${a.id}`} className="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50">
                                    <div className="flex-1 min-w-0">
                                        <p className="font-medium text-sm text-zinc-900 truncate">{a.fournisseur || 'Achat'} {a.pour_production && <span className="ml-1 text-[10px] font-semibold text-amber-700 bg-amber-100 rounded px-1.5 py-0.5">production</span>}</p>
                                        <p className="text-xs text-zinc-400">{a.date} · {a.nb_lignes} ligne(s)</p>
                                    </div>
                                    <span className="text-sm font-bold text-zinc-800">{a.total.toLocaleString('fr-FR')}</span>
                                    <Eye className="w-4 h-4 text-zinc-300" />
                                </Link>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            <style>{`.ipt{width:100%;padding:.55rem .7rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.875rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </AdminLayout>
    );
}
function L({ label, children }: { label: string; children: React.ReactNode }) {
    return <label className="block"><span className="block text-xs font-medium text-zinc-500 mb-1">{label}</span>{children}</label>;
}

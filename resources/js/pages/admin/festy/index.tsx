import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { PartyPopper, Users, MessageCircle, AlertTriangle, Plus, Pencil, Trash2, X, Save } from 'lucide-react';

interface Equipe {
    id: number;
    nom: string;
    trait: string | null;
    couleur: string;
    emoji: string | null;
    whatsapp_group: string | null;
    actif: boolean;
    membres: number;
}
interface Settings {
    titre: string;
    sous_titre: string | null;
    date_label: string | null;
    prix: string | null;
    description: string | null;
    inscriptions_ouvertes: boolean;
}
interface Props {
    settings: Settings;
    equipes: Equipe[];
    stats: { inscrits: number; equipes: number; sans_groupe: number };
}

const vide: Omit<Equipe, 'id' | 'membres'> = {
    nom: '', trait: '', couleur: '#F5B301', emoji: '', whatsapp_group: '', actif: true,
};

export default function AdminFesty({ settings, equipes, stats }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;
    const [form, setForm] = useState<Settings>(settings);
    const [edition, setEdition] = useState<(Partial<Equipe> & { id?: number }) | null>(null);
    const [busy, setBusy] = useState(false);

    const enregistrerReglages = () => {
        setBusy(true);
        router.post('/admin/festy/settings', { ...form }, {
            preserveScroll: true,
            onFinish: () => setBusy(false),
        });
    };

    const enregistrerEquipe = () => {
        if (!edition) return;
        setBusy(true);
        const payload = {
            nom: edition.nom ?? '', trait: edition.trait ?? '', couleur: edition.couleur ?? '#F5B301',
            emoji: edition.emoji ?? '', whatsapp_group: edition.whatsapp_group ?? '', actif: edition.actif ? 1 : 0,
        };
        const opts = { preserveScroll: true, onSuccess: () => setEdition(null), onFinish: () => setBusy(false) };
        if (edition.id) router.post(`/admin/festy/teams/${edition.id}`, { ...payload, _method: 'PATCH' }, opts);
        else router.post('/admin/festy/teams', payload, opts);
    };

    const supprimer = (eq: Equipe) => {
        if (!confirm(`Supprimer l'équipe ${eq.nom} et ses ${eq.membres} inscrit(s) ?`)) return;
        router.post(`/admin/festy/teams/${eq.id}`, { _method: 'DELETE' }, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Paradisia Festy">
            <Head title="Paradisia Festy — Admin" />

            {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

            {/* Stats */}
            <div className="grid grid-cols-3 gap-3 mb-6">
                <Stat icon={Users} label="Inscrits" value={stats.inscrits} color="#14532d" />
                <Stat icon={PartyPopper} label="Équipes actives" value={stats.equipes} color="#F5B301" />
                <Stat icon={AlertTriangle} label="Sans groupe WhatsApp" value={stats.sans_groupe} color={stats.sans_groupe ? '#dc2626' : '#16a34a'} />
            </div>

            <div className="grid lg:grid-cols-2 gap-6">
                {/* Réglages */}
                <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                    <h2 className="font-bold text-zinc-900 mb-4 flex items-center gap-2"><PartyPopper className="w-5 h-5 text-amber-500" /> Réglages</h2>
                    <div className="space-y-3">
                        <L label="Titre"><input className="ipt" value={form.titre} onChange={(e) => setForm({ ...form, titre: e.target.value })} /></L>
                        <L label="Sous-titre"><input className="ipt" value={form.sous_titre ?? ''} onChange={(e) => setForm({ ...form, sous_titre: e.target.value })} /></L>
                        <div className="grid grid-cols-2 gap-3">
                            <L label="Date (label)"><input className="ipt" value={form.date_label ?? ''} onChange={(e) => setForm({ ...form, date_label: e.target.value })} placeholder="Décembre 2026" /></L>
                            <L label="Lot à gagner"><input className="ipt" value={form.prix ?? ''} onChange={(e) => setForm({ ...form, prix: e.target.value })} placeholder="300 000 FCFA" /></L>
                        </div>
                        <L label="Description"><textarea className="ipt" rows={4} value={form.description ?? ''} onChange={(e) => setForm({ ...form, description: e.target.value })} /></L>
                        <label className="flex items-center gap-2 text-sm text-zinc-700 cursor-pointer">
                            <input type="checkbox" checked={form.inscriptions_ouvertes} onChange={(e) => setForm({ ...form, inscriptions_ouvertes: e.target.checked })} />
                            Inscriptions ouvertes
                        </label>
                        <button onClick={enregistrerReglages} disabled={busy}
                            className="w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm flex items-center justify-center gap-2 disabled:opacity-60">
                            <Save className="w-4 h-4" /> Enregistrer les réglages
                        </button>
                    </div>
                </div>

                {/* Équipes */}
                <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="font-bold text-zinc-900">Équipes</h2>
                        <button onClick={() => setEdition({ ...vide })}
                            className="text-sm font-semibold text-emerald-700 hover:text-emerald-900 flex items-center gap-1"><Plus className="w-4 h-4" /> Ajouter</button>
                    </div>
                    <div className="space-y-2">
                        {equipes.map((eq) => (
                            <div key={eq.id} className="flex items-center gap-3 rounded-xl border border-zinc-150 p-3" style={{ borderColor: '#f0f0f0' }}>
                                <div className="w-10 h-10 rounded-lg flex items-center justify-center text-xl" style={{ background: `${eq.couleur}22` }}>{eq.emoji ?? '🍹'}</div>
                                <div className="flex-1 min-w-0">
                                    <p className="font-semibold text-sm" style={{ color: eq.couleur }}>{eq.nom}{!eq.actif && <span className="text-zinc-400 font-normal"> · inactive</span>}</p>
                                    <p className="text-xs text-zinc-500 flex items-center gap-2">
                                        <span className="flex items-center gap-1"><Users className="w-3 h-3" />{eq.membres}</span>
                                        {eq.whatsapp_group
                                            ? <span className="flex items-center gap-1 text-emerald-600"><MessageCircle className="w-3 h-3" /> groupe OK</span>
                                            : <span className="flex items-center gap-1 text-red-500"><AlertTriangle className="w-3 h-3" /> sans groupe</span>}
                                    </p>
                                </div>
                                <button onClick={() => setEdition({ ...eq })} className="p-2 text-zinc-500 hover:text-emerald-700"><Pencil className="w-4 h-4" /></button>
                                <button onClick={() => supprimer(eq)} className="p-2 text-zinc-500 hover:text-red-600"><Trash2 className="w-4 h-4" /></button>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Modale édition équipe */}
            {edition && (
                <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onClick={() => setEdition(null)}>
                    <div className="bg-white rounded-2xl w-full max-w-md p-6" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="font-bold text-zinc-900">{edition.id ? "Modifier l'équipe" : 'Nouvelle équipe'}</h3>
                            <button onClick={() => setEdition(null)} className="text-zinc-400 hover:text-zinc-700"><X className="w-5 h-5" /></button>
                        </div>
                        <div className="space-y-3">
                            <div className="grid grid-cols-3 gap-3">
                                <L label="Nom" className="col-span-2"><input className="ipt" value={edition.nom ?? ''} onChange={(e) => setEdition({ ...edition, nom: e.target.value })} /></L>
                                <L label="Emoji"><input className="ipt" value={edition.emoji ?? ''} onChange={(e) => setEdition({ ...edition, emoji: e.target.value })} /></L>
                            </div>
                            <L label="Trait"><input className="ipt" value={edition.trait ?? ''} onChange={(e) => setEdition({ ...edition, trait: e.target.value })} placeholder="Énergie & Force" /></L>
                            <div className="grid grid-cols-3 gap-3 items-end">
                                <L label="Couleur">
                                    <input type="color" className="w-full h-10 rounded-lg border border-zinc-200" value={edition.couleur ?? '#F5B301'} onChange={(e) => setEdition({ ...edition, couleur: e.target.value })} />
                                </L>
                                <label className="col-span-2 flex items-center gap-2 text-sm text-zinc-700 cursor-pointer pb-2">
                                    <input type="checkbox" checked={!!edition.actif} onChange={(e) => setEdition({ ...edition, actif: e.target.checked })} /> Équipe active
                                </label>
                            </div>
                            <L label="Lien du groupe WhatsApp">
                                <input className="ipt" value={edition.whatsapp_group ?? ''} onChange={(e) => setEdition({ ...edition, whatsapp_group: e.target.value })} placeholder="https://chat.whatsapp.com/..." />
                            </L>
                        </div>
                        <button onClick={enregistrerEquipe} disabled={busy || !edition.nom}
                            className="mt-5 w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm disabled:opacity-60">
                            {edition.id ? 'Enregistrer' : "Créer l'équipe"}
                        </button>
                    </div>
                </div>
            )}

            <style>{`.ipt{width:100%;padding:.55rem .7rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.875rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </AdminLayout>
    );
}

function Stat({ icon: Icon, label, value, color }: { icon: any; label: string; value: number; color: string }) {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-4 flex items-center gap-3">
            <div className="w-11 h-11 rounded-xl flex items-center justify-center" style={{ background: `${color}18` }}><Icon className="w-5 h-5" style={{ color }} /></div>
            <div>
                <p className="text-2xl font-bold text-zinc-900 leading-none">{value}</p>
                <p className="text-xs text-zinc-500 mt-1">{label}</p>
            </div>
        </div>
    );
}

function L({ label, children, className = '' }: { label: string; children: React.ReactNode; className?: string }) {
    return (
        <label className={`block ${className}`}>
            <span className="block text-xs font-medium text-zinc-500 mb-1">{label}</span>
            {children}
        </label>
    );
}

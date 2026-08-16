import { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Users, Send, Mail, Globe, Phone, CheckCircle2, Pencil, Trash2, X } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

interface Inscrit {
    id: number;
    email: string;
    nom: string | null;
    pays: string | null;
    telephone: string | null;
    profil: string | null;
    profil_label: string;
    lien_envoye: boolean;
    date: string;
}

interface Props {
    event: { id: number; titre: string; date_label: string; lien_reunion: string | null; collecte_profil: boolean; collecte_pays: boolean; collecte_telephone: boolean; collecte_nom: boolean };
    inscrits: Inscrit[];
    filtre: string | null;
    compteurs: { total: number; investisseurs: number; participants: number; lien_envoye: number };
}

export default function Registrations({ event, inscrits, filtre, compteurs }: Props) {
    const envoi = useForm({});
    const [edition, setEdition] = useState<Inscrit | null>(null);
    const [busy, setBusy] = useState(false);

    // Lien de réunion réglable directement ici.
    const lienForm = useForm({ lien_reunion: event.lien_reunion ?? '' });
    const enregistrerLien = () => lienForm.post(`/admin/events/${event.id}/lien`, { preserveScroll: true });

    const filtrer = (profil: string | null) => {
        router.get(`/admin/events/${event.id}/inscrits`, profil ? { profil } : {}, { preserveState: true, preserveScroll: true });
    };

    const enregistrer = () => {
        if (!edition) return;
        setBusy(true);
        router.post(`/admin/events/${event.id}/inscrits/${edition.id}`, {
            _method: 'PATCH',
            email: edition.email ?? '',
            nom: edition.nom ?? '',
            pays: edition.pays ?? '',
            telephone: edition.telephone ?? '',
            profil: edition.profil ?? '',
        }, { preserveScroll: true, onSuccess: () => setEdition(null), onFinish: () => setBusy(false) });
    };

    const supprimer = (i: Inscrit) => {
        if (!confirm(`Supprimer l'inscription de ${i.nom || i.email} ?`)) return;
        router.delete(`/admin/events/${event.id}/inscrits/${i.id}`, { preserveScroll: true });
    };

    const envoyerLien = () => {
        if (!event.lien_reunion) {
            alert('Renseignez d\'abord le lien de réunion dans l\'événement (bouton Modifier).');
            return;
        }
        if (confirm(`Envoyer le lien de réunion aux ${compteurs.total - compteurs.lien_envoye} inscrit(s) qui ne l'ont pas encore reçu ?`)) {
            envoi.post(`/admin/events/${event.id}/envoyer-lien`, { preserveScroll: true });
        }
    };

    const exportCsv = () => {
        const lignes = [
            ['Email', 'Nom', 'Pays', 'Téléphone', 'Profil', 'Lien envoyé', 'Date'],
            ...inscrits.map((i) => [i.email, i.nom ?? '', i.pays ?? '', i.telephone ?? '', i.profil_label, i.lien_envoye ? 'oui' : 'non', i.date]),
        ];
        const csv = lignes.map((l) => l.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `inscrits-${event.id}.csv`;
        a.click();
    };

    const chip = (actif: boolean) =>
        `px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${actif ? 'bg-emerald-600 text-white' : 'bg-white border border-zinc-200 text-zinc-600 hover:bg-zinc-50'}`;

    return (
        <AdminLayout title="Inscrits">
            <Head title={`Inscrits — ${event.titre}`} />

            <div className="space-y-5">
                <Link href="/admin/events" className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900">
                    <ArrowLeft className="w-4 h-4" /> Retour aux événements
                </Link>

                <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                    <div className="flex items-start justify-between gap-4 flex-wrap">
                        <div>
                            <h1 className="text-lg font-bold text-zinc-900">{event.titre}</h1>
                            <p className="text-sm text-zinc-500">{event.date_label}</p>
                        </div>
                        <div className="flex items-center gap-2">
                            <button onClick={exportCsv}
                                className="px-3 py-2 rounded-lg border border-zinc-200 text-sm font-medium text-zinc-700 hover:bg-zinc-50">
                                Exporter CSV
                            </button>
                            <button onClick={envoyerLien} disabled={envoi.processing}
                                className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-300 text-white text-sm font-semibold">
                                <Send className="w-4 h-4" /> {envoi.processing ? 'Envoi…' : 'Envoyer le lien de réunion'}
                            </button>
                        </div>
                    </div>

                    {/* Lien de réunion : réglable directement ici (raccourci) */}
                    <div className="mt-3 flex flex-col sm:flex-row gap-2 sm:items-center">
                        <input
                            value={lienForm.data.lien_reunion}
                            onChange={(e) => lienForm.setData('lien_reunion', e.target.value)}
                            placeholder="Lien de la réunion (Zoom, Google Meet, …)"
                            className="flex-1 px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                        <button onClick={enregistrerLien} disabled={lienForm.processing}
                            className="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 disabled:opacity-60 text-white text-sm font-semibold whitespace-nowrap">
                            {lienForm.processing ? 'Enregistrement…' : 'Enregistrer le lien'}
                        </button>
                    </div>
                    {!event.lien_reunion && !lienForm.data.lien_reunion && (
                        <p className="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            ⚠️ Aucun lien de réunion enregistré. Colle-le ci-dessus et « Enregistrer » avant l'envoi.
                        </p>
                    )}

                    <div className="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        {[
                            { label: 'Total', valeur: compteurs.total },
                            { label: 'Investisseurs', valeur: compteurs.investisseurs },
                            { label: 'Participants', valeur: compteurs.participants },
                            { label: 'Lien reçu', valeur: compteurs.lien_envoye },
                        ].map((s) => (
                            <div key={s.label} className="bg-zinc-50 rounded-xl px-3 py-2">
                                <p className="text-xs text-zinc-500">{s.label}</p>
                                <p className="text-xl font-bold text-emerald-700">{s.valeur}</p>
                            </div>
                        ))}
                    </div>
                </div>

                {event.collecte_profil && (
                    <div className="flex items-center gap-2">
                        <button className={chip(!filtre)} onClick={() => filtrer(null)}>Tous</button>
                        <button className={chip(filtre === 'investisseur')} onClick={() => filtrer('investisseur')}>Investisseurs</button>
                        <button className={chip(filtre === 'participant')} onClick={() => filtrer('participant')}>Participants</button>
                    </div>
                )}

                <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                    {inscrits.length === 0 ? (
                        <p className="text-center text-zinc-500 py-16 flex flex-col items-center gap-2">
                            <Users className="w-8 h-8 text-zinc-300" /> Aucun inscrit pour ce filtre.
                        </p>
                    ) : (
                        <div className="divide-y divide-zinc-100">
                            {inscrits.map((i) => (
                                <div key={i.id} className="px-5 py-3 flex items-center gap-4">
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2">
                                            <Mail className="w-4 h-4 text-zinc-400 flex-shrink-0" />
                                            <span className="text-sm font-medium text-zinc-900 truncate">{i.email}</span>
                                            {i.nom && <span className="text-sm text-zinc-500">· {i.nom}</span>}
                                        </div>
                                        <div className="mt-1 flex items-center gap-3 text-xs text-zinc-500 flex-wrap">
                                            {i.pays && <span className="flex items-center gap-1"><Globe className="w-3 h-3" />{i.pays}</span>}
                                            {i.telephone && <span className="flex items-center gap-1"><Phone className="w-3 h-3" />{i.telephone}</span>}
                                            <span>{i.date}</span>
                                        </div>
                                    </div>
                                    {i.profil && (
                                        <span className={`text-xs font-semibold px-2.5 py-1 rounded-full ${i.profil === 'investisseur' ? 'bg-violet-100 text-violet-700' : 'bg-blue-100 text-blue-700'}`}>
                                            {i.profil_label}
                                        </span>
                                    )}
                                    {i.lien_envoye && (
                                        <span title="Lien de réunion envoyé" className="text-emerald-600">
                                            <CheckCircle2 className="w-4 h-4" />
                                        </span>
                                    )}
                                    <div className="flex items-center gap-1">
                                        <button onClick={() => setEdition({ ...i })} className="p-2 text-zinc-500 hover:text-emerald-700" title="Modifier"><Pencil className="w-4 h-4" /></button>
                                        <button onClick={() => supprimer(i)} className="p-2 text-zinc-500 hover:text-red-600" title="Supprimer"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
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
                            <L label="E-mail"><input className="ipt" value={edition.email ?? ''} onChange={(e) => setEdition({ ...edition, email: e.target.value })} /></L>
                            {event.collecte_nom && <L label="Nom"><input className="ipt" value={edition.nom ?? ''} onChange={(e) => setEdition({ ...edition, nom: e.target.value })} /></L>}
                            {event.collecte_telephone && <L label="Numéro WhatsApp"><input className="ipt" value={edition.telephone ?? ''} onChange={(e) => setEdition({ ...edition, telephone: e.target.value })} /></L>}
                            {event.collecte_pays && <L label="Pays"><input className="ipt" value={edition.pays ?? ''} onChange={(e) => setEdition({ ...edition, pays: e.target.value })} /></L>}
                            {event.collecte_profil && (
                                <L label="Profil">
                                    <select className="ipt" value={edition.profil ?? ''} onChange={(e) => setEdition({ ...edition, profil: e.target.value })}>
                                        <option value="">—</option>
                                        <option value="investisseur">Investisseur</option>
                                        <option value="participant">Participant</option>
                                    </select>
                                </L>
                            )}
                        </div>
                        <button onClick={enregistrer} disabled={busy}
                            className="mt-5 w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm disabled:opacity-60">
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

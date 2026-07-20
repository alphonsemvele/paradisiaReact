import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Users, Send, Mail, Globe, Phone, CheckCircle2 } from 'lucide-react';
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
    event: { id: number; titre: string; date_label: string; lien_reunion: string | null; collecte_profil: boolean };
    inscrits: Inscrit[];
    filtre: string | null;
    compteurs: { total: number; investisseurs: number; participants: number; lien_envoye: number };
}

export default function Registrations({ event, inscrits, filtre, compteurs }: Props) {
    const envoi = useForm({});

    const filtrer = (profil: string | null) => {
        router.get(`/admin/events/${event.id}/inscrits`, profil ? { profil } : {}, { preserveState: true, preserveScroll: true });
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

                    {!event.lien_reunion && (
                        <p className="mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            Aucun lien de réunion renseigné. Ajoutez-le via « Modifier » avant l'envoi.
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
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}

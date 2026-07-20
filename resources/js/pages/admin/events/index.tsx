import { Head, Link, router } from '@inertiajs/react';
import { Calendar, Plus, Users, Trash2, Pencil, MapPin, Monitor, Radio } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

interface Event {
    id: number;
    titre: string;
    type: string;
    mode_label: string;
    date_label: string;
    statut: string;
    passe: boolean;
    inscriptions_ouvertes: boolean;
    inscrits: number;
    image: string | null;
}

interface Props {
    events: Event[];
    stats: { total: number; publies: number; a_venir: number; inscrits: number };
}

const statutBadge: Record<string, { label: string; c: string }> = {
    publie: { label: 'Publié', c: 'bg-emerald-100 text-emerald-700' },
    brouillon: { label: 'Brouillon', c: 'bg-zinc-100 text-zinc-600' },
    termine: { label: 'Terminé', c: 'bg-blue-100 text-blue-700' },
};

export default function EventsIndex({ events, stats }: Props) {
    const supprimer = (e: Event) => {
        if (confirm(`Supprimer l'événement « ${e.titre} » et toutes ses inscriptions ?`)) {
            router.delete(`/admin/events/${e.id}`);
        }
    };

    return (
        <AdminLayout title="Événements">
            <Head title="Admin - Événements" />

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-3 flex-wrap">
                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 flex-1">
                        {[
                            { label: 'Total', valeur: stats.total },
                            { label: 'Publiés', valeur: stats.publies },
                            { label: 'À venir', valeur: stats.a_venir },
                            { label: 'Inscrits', valeur: stats.inscrits },
                        ].map((s) => (
                            <div key={s.label} className="bg-white rounded-xl border border-zinc-200 px-4 py-3">
                                <p className="text-xs text-zinc-500">{s.label}</p>
                                <p className="text-2xl font-bold text-emerald-700">{s.valeur}</p>
                            </div>
                        ))}
                    </div>
                    <Link
                        href="/admin/events/create"
                        className="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl"
                    >
                        <Plus className="w-4 h-4" /> Nouvel événement
                    </Link>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    {events.length === 0 && (
                        <p className="col-span-full text-center text-zinc-500 py-16 bg-white rounded-2xl border border-zinc-200">
                            Aucun événement. Créez le premier.
                        </p>
                    )}

                    {events.map((e) => (
                        <div key={e.id} className="bg-white rounded-2xl border border-zinc-200 overflow-hidden flex flex-col">
                            <div className="h-32 bg-gradient-to-br from-emerald-100 to-teal-100 relative">
                                {e.image ? (
                                    <img src={e.image} alt={e.titre} className="w-full h-full object-cover" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center">
                                        <Calendar className="w-10 h-10 text-emerald-500" />
                                    </div>
                                )}
                                <span className={`absolute top-2 right-2 text-xs font-bold px-2 py-1 rounded-full ${statutBadge[e.statut]?.c}`}>
                                    {statutBadge[e.statut]?.label}
                                </span>
                            </div>

                            <div className="p-4 flex flex-col flex-1">
                                <h3 className="font-semibold text-zinc-900 line-clamp-1">{e.titre}</h3>
                                <div className="mt-1.5 flex items-center gap-3 text-xs text-zinc-500">
                                    <span className="flex items-center gap-1">
                                        {e.mode_label === 'En présentiel' ? <MapPin className="w-3.5 h-3.5" /> : e.mode_label === 'Hybride' ? <Radio className="w-3.5 h-3.5" /> : <Monitor className="w-3.5 h-3.5" />}
                                        {e.mode_label}
                                    </span>
                                    <span>{e.date_label}</span>
                                </div>

                                <Link
                                    href={`/admin/events/${e.id}/inscrits`}
                                    className="mt-3 inline-flex items-center gap-1.5 text-sm text-emerald-700 font-medium hover:underline"
                                >
                                    <Users className="w-4 h-4" /> {e.inscrits} inscrit{e.inscrits > 1 ? 's' : ''}
                                </Link>

                                <div className="mt-auto pt-3 flex items-center gap-2">
                                    <Link
                                        href={`/admin/events/${e.id}/edit`}
                                        className="flex-1 flex items-center justify-center gap-1.5 py-2 bg-zinc-50 hover:bg-zinc-100 rounded-lg text-sm font-medium text-zinc-700"
                                    >
                                        <Pencil className="w-3.5 h-3.5" /> Modifier
                                    </Link>
                                    <button
                                        onClick={() => supprimer(e)}
                                        className="p-2 hover:bg-red-50 rounded-lg text-zinc-500 hover:text-red-600"
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}

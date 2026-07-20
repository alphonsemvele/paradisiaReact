import { Head, Link } from '@inertiajs/react';
import { Calendar, MapPin, Monitor, Radio, ArrowRight } from 'lucide-react';
import AppLayout from '@/components/layouts/AppLayout';

interface EventCard {
    id: number;
    titre: string;
    type: string;
    mode: string;
    mode_label: string;
    date_label: string;
    date_courte: string;
    passe: boolean;
    image: string | null;
    extrait: string | null;
    inscriptions_ouvertes: boolean;
}

export default function EventsIndex({ events }: { events: EventCard[] }) {
    return (
        <AppLayout>
            <Head title="Événements - Paradisia" />

            <div className="max-w-6xl mx-auto px-4 py-10">
                <div className="text-center mb-10">
                    <h1 className="text-3xl font-bold text-zinc-900">Nos événements</h1>
                    <p className="mt-2 text-zinc-500">Meetings, webinaires et rencontres Paradisia</p>
                </div>

                {events.length === 0 ? (
                    <p className="text-center text-zinc-500 py-20">Aucun événement programmé pour le moment.</p>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {events.map((e) => {
                            const ModeIcon = e.mode === 'presentiel' ? MapPin : e.mode === 'hybride' ? Radio : Monitor;
                            return (
                                <Link key={e.id} href={`/events/${e.id}`}
                                    className="group bg-white rounded-2xl border border-zinc-200 overflow-hidden hover:shadow-lg transition-all flex flex-col">
                                    <div className="h-44 bg-gradient-to-br from-emerald-100 to-teal-100 relative overflow-hidden">
                                        {e.image ? (
                                            <img src={e.image} alt={e.titre} loading="lazy"
                                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center">
                                                <Calendar className="w-12 h-12 text-emerald-500" />
                                            </div>
                                        )}
                                        <span className="absolute top-3 left-3 bg-white/95 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-lg shadow-sm">
                                            {e.date_courte}
                                        </span>
                                        {e.passe && (
                                            <span className="absolute inset-0 bg-black/40 flex items-center justify-center text-white font-semibold">
                                                Terminé
                                            </span>
                                        )}
                                    </div>

                                    <div className="p-5 flex flex-col flex-1">
                                        <span className="text-xs font-bold uppercase tracking-wide text-emerald-600">{e.type}</span>
                                        <h3 className="mt-1 font-semibold text-zinc-900 line-clamp-2">{e.titre}</h3>
                                        {e.extrait && <p className="mt-2 text-sm text-zinc-500 line-clamp-2">{e.extrait}</p>}

                                        <div className="mt-3 flex items-center gap-3 text-xs text-zinc-500">
                                            <span className="flex items-center gap-1"><ModeIcon className="w-3.5 h-3.5" />{e.mode_label}</span>
                                            <span className="flex items-center gap-1"><Calendar className="w-3.5 h-3.5" />{e.date_label}</span>
                                        </div>

                                        <span className="mt-auto pt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 group-hover:gap-2.5 transition-all">
                                            {e.passe ? 'Voir le détail' : "S'inscrire"} <ArrowRight className="w-4 h-4" />
                                        </span>
                                    </div>
                                </Link>
                            );
                        })}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

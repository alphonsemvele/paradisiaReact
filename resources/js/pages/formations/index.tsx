import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { GraduationCap, Clock, CalendarDays, ArrowRight, Tag } from 'lucide-react';
import AppLayout from '@/components/layouts/AppLayout';

interface FormationCard {
    id: number;
    titre: string;
    description: string | null;
    prix: number;
    prix_formatte: string;
    duree: string | null;
    session: string | null;
    mode: string;
    mode_label: string;
    image: string | null;
}

interface Props {
    formations: FormationCard[];
}

export default function FormationsList({ formations }: Props) {
    return (
        <AppLayout>
            <Head title="Formations - Paradisia" />

            {/* En-tête */}
            <div className="bg-gradient-to-br from-emerald-600 to-emerald-800 text-white">
                <div className="max-w-6xl mx-auto px-4 py-12 lg:py-16 text-center">
                    <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 mb-4">
                        <GraduationCap className="w-7 h-7" />
                    </div>
                    <h1 className="text-3xl lg:text-4xl font-bold">Nos formations</h1>
                    <p className="mt-3 text-white/85 max-w-2xl mx-auto">
                        Apprenez à transformer les fruits en jus naturels. Choisissez une formation
                        et inscrivez-vous en quelques clics.
                    </p>
                </div>
            </div>

            <div className="max-w-6xl mx-auto px-4 py-10">
                {formations.length === 0 ? (
                    <div className="text-center py-20 text-zinc-500">
                        <GraduationCap className="w-12 h-12 mx-auto mb-3 text-zinc-300" />
                        Aucune formation disponible pour le moment. Revenez bientôt !
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {formations.map((f, i) => (
                            <motion.div
                                key={f.id}
                                initial={{ opacity: 0, y: 12 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: i * 0.05 }}
                                className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden flex flex-col"
                            >
                                <div className="aspect-video bg-zinc-100 overflow-hidden relative">
                                    {f.image ? (
                                        <img src={f.image} alt={f.titre} className="w-full h-full object-cover" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-100 to-orange-100">
                                            <GraduationCap className="w-10 h-10 text-emerald-500" />
                                        </div>
                                    )}
                                    <span
                                        className={`absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-semibold uppercase ${
                                            f.mode === 'en_ligne' ? 'bg-blue-500 text-white' : 'bg-emerald-500 text-white'
                                        }`}
                                    >
                                        {f.mode_label}
                                    </span>
                                </div>

                                <div className="p-5 flex flex-col flex-1">
                                    <h3 className="font-semibold text-zinc-900 text-lg">{f.titre}</h3>
                                    {f.description && (
                                        <p className="mt-1.5 text-sm text-zinc-500 line-clamp-2">{f.description}</p>
                                    )}

                                    <div className="mt-3 space-y-1.5 text-sm text-zinc-600">
                                        {f.duree && (
                                            <p className="flex items-center gap-2">
                                                <Clock className="w-4 h-4 text-emerald-600" /> {f.duree}
                                            </p>
                                        )}
                                        {f.session && (
                                            <p className="flex items-center gap-2">
                                                <CalendarDays className="w-4 h-4 text-emerald-600" /> {f.session}
                                            </p>
                                        )}
                                        <p className="flex items-center gap-2 font-semibold text-zinc-900">
                                            <Tag className="w-4 h-4 text-orange-500" /> {f.prix_formatte}
                                        </p>
                                    </div>

                                    <Link
                                        href={`/formations/${f.id}`}
                                        className="mt-4 inline-flex items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-lg transition-colors"
                                    >
                                        Voir et s'inscrire
                                        <ArrowRight className="w-4 h-4" />
                                    </Link>
                                </div>
                            </motion.div>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

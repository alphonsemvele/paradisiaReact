import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { MapPin, Phone, Clock, Store, ArrowRight, Search } from 'lucide-react';
import { useState, useMemo } from 'react';
import AppLayout from '@/components/layouts/AppLayout';

const FALLBACK = 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&q=80&auto=format&fit=crop';

export default function PointsDeVenteIndex({ points, totalPoints }: any) {
    const [search, setSearch] = useState('');

    const filtered = useMemo(() => {
        if (!search) return points;
        return points.filter((p: any) =>
            p.name.toLowerCase().includes(search.toLowerCase()) ||
            (p.address || '').toLowerCase().includes(search.toLowerCase()),
        );
    }, [search, points]);

    return (
        <AppLayout>
            <Head title="Nos points de vente - Paradisia" />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
                {/* Hero */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-3xl p-8 lg:p-12 text-white text-center mb-8"
                >
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4">
                        <Store className="w-8 h-8" />
                    </div>
                    <h1 className="text-3xl lg:text-4xl font-bold mb-2">Nos points de vente</h1>
                    <p className="text-emerald-50 mb-6 max-w-xl mx-auto">
                        Retrouvez Paradisia dans <strong>{totalPoints}</strong> point{totalPoints > 1 ? 's' : ''} de vente
                    </p>

                    {/* Search */}
                    <div className="max-w-md mx-auto relative">
                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-emerald-300" />
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Rechercher un point de vente..."
                            className="w-full pl-12 pr-4 py-3 bg-white/15 backdrop-blur-sm border border-white/20 rounded-xl text-sm text-white placeholder-emerald-200 focus:outline-none focus:bg-white/25 transition-colors"
                        />
                    </div>
                </motion.div>

                {/* Grille */}
                {filtered.length === 0 ? (
                    <div className="bg-white rounded-2xl p-12 text-center border border-zinc-200">
                        <Store className="w-12 h-12 mx-auto mb-3 text-zinc-300" />
                        <p className="text-zinc-500">Aucun point de vente trouvé</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {filtered.map((point: any, i: number) => (
                            <motion.div
                                key={point.id}
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: i * 0.05 }}
                            >
                                <Link
                                    href={`/points-de-vente/${point.id}`}
                                    className="block bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all group"
                                >
                                    {/* Image */}
                                    <div className="aspect-video bg-zinc-100 relative overflow-hidden">
                                        <img
                                            src={point.image || FALLBACK}
                                            alt={point.name}
                                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                            onError={(e) => {
                                                (e.target as HTMLImageElement).src = FALLBACK;
                                            }}
                                        />
                                        {point.has_location && (
                                            <div className="absolute top-3 right-3">
                                                <span className="inline-flex items-center gap-1 px-2 py-1 bg-white/90 backdrop-blur-sm text-zinc-700 text-[10px] font-semibold rounded-full">
                                                    <MapPin className="w-3 h-3" />
                                                    GPS
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Content */}
                                    <div className="p-5">
                                        <h3 className="font-bold text-zinc-900 mb-3 group-hover:text-emerald-600 transition-colors">
                                            {point.name}
                                        </h3>

                                        <div className="space-y-2 text-sm text-zinc-600 mb-4">
                                            {point.address && (
                                                <p className="flex items-start gap-2">
                                                    <MapPin className="w-4 h-4 flex-shrink-0 mt-0.5 text-zinc-400" />
                                                    <span className="line-clamp-2">{point.address}</span>
                                                </p>
                                            )}
                                            {point.phone && (
                                                <p className="flex items-center gap-2">
                                                    <Phone className="w-4 h-4 flex-shrink-0 text-zinc-400" />
                                                    {point.phone}
                                                </p>
                                            )}
                                            {point.hours && (
                                                <p className="flex items-center gap-2">
                                                    <Clock className="w-4 h-4 flex-shrink-0 text-zinc-400" />
                                                    {point.hours}
                                                </p>
                                            )}
                                        </div>

                                        <div className="flex items-center justify-between pt-3 border-t border-zinc-100">
                                            <span className="text-xs font-medium text-emerald-600">
                                                Voir plus
                                            </span>
                                            <ArrowRight className="w-4 h-4 text-emerald-600 group-hover:translate-x-1 transition-transform" />
                                        </div>
                                    </div>
                                </Link>
                            </motion.div>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    Search,
    Store,
    Plus,
    Edit3,
    Trash2,
    CheckCircle,
    XCircle,
    MapPin,
    Phone,
    Clock,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

const FALLBACK_IMAGE =
    'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&q=80&auto=format&fit=crop';

export default function PointsDeVenteIndex({ points, stats, filters }: any) {
    const [search, setSearch] = useState(filters.search || '');
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const applyFilter = (key: string, value: string | null) => {
        router.get(
            '/admin/points-de-vente',
            { ...filters, [key]: value || undefined },
            { preserveState: true },
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilter('search', search);
    };

    const handleToggleStatus = (id: number) => {
        router.patch(`/admin/points-de-vente/${id}/toggle-status`, {}, { preserveScroll: true });
    };

    const handleDelete = (id: number) => {
        router.delete(`/admin/points-de-vente/${id}`, {
            onSuccess: () => setConfirmDelete(null),
        });
    };

    return (
        <AdminLayout title="Points de vente">
            <Head title="Admin - Points de vente" />

            <div className="space-y-6">
                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard icon={Store} label="Total" value={stats.total} color="emerald" />
                    <StatCard icon={CheckCircle} label="Actifs" value={stats.active} color="blue" />
                    <StatCard icon={XCircle} label="Inactifs" value={stats.inactive} color="rose" />
                    <StatCard icon={MapPin} label="Géolocalisés" value={stats.with_location} color="orange" />
                </div>

                {/* Toolbar */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-4">
                    <div className="flex flex-col sm:flex-row gap-3">
                        <form onSubmit={handleSearch} className="flex-1 relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                            <input
                                type="search"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Rechercher par nom, adresse, téléphone..."
                                className="w-full pl-10 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </form>

                        <select
                            value={filters.status || ''}
                            onChange={(e) => applyFilter('status', e.target.value || null)}
                            className="px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="">Tous les statuts</option>
                            <option value="active">Actifs</option>
                            <option value="inactive">Inactifs</option>
                        </select>

                        <Link
                            href="/admin/points-de-vente/create"
                            className="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors"
                        >
                            <Plus className="w-4 h-4" />
                            Nouveau point
                        </Link>
                    </div>
                </div>

                {/* Grille */}
                {points.data.length === 0 ? (
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-12 text-center">
                        <Store className="w-12 h-12 mx-auto mb-3 text-zinc-300" />
                        <p className="text-zinc-500 mb-4">Aucun point de vente</p>
                        <Link
                            href="/admin/points-de-vente/create"
                            className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white text-sm rounded-lg hover:bg-emerald-600"
                        >
                            <Plus className="w-4 h-4" />
                            Créer le premier point
                        </Link>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {points.data.map((point: any) => (
                            <PointCard
                                key={point.id}
                                point={point}
                                onToggle={() => handleToggleStatus(point.id)}
                                onDelete={() => setConfirmDelete(point.id)}
                            />
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {points.last_page > 1 && (
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 px-5 py-3 flex flex-col sm:flex-row items-center justify-between gap-2">
                        <p className="text-xs text-zinc-500">
                            Page <strong>{points.current_page}</strong> sur{' '}
                            <strong>{points.last_page}</strong> · <strong>{points.total}</strong> points
                        </p>
                        <div className="flex gap-1 flex-wrap">
                            {points.links.map((link: any, i: number) =>
                                link.url ? (
                                    <Link
                                        key={i}
                                        href={link.url}
                                        preserveScroll
                                        className={`px-3 py-1.5 text-xs rounded-lg ${
                                            link.active ? 'bg-emerald-500 text-white' : 'text-zinc-600 hover:bg-zinc-100'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ) : (
                                    <span
                                        key={i}
                                        className="px-3 py-1.5 text-xs text-zinc-400"
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ),
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* Modal Delete */}
            {confirmDelete !== null && (
                <div
                    className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                    onClick={() => setConfirmDelete(null)}
                >
                    <motion.div
                        initial={{ scale: 0.9, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        onClick={(e) => e.stopPropagation()}
                        className="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full"
                    >
                        <div className="w-12 h-12 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <Trash2 className="w-6 h-6 text-red-600" />
                        </div>
                        <h3 className="text-lg font-semibold text-zinc-900 text-center mb-2">
                            Supprimer ce point de vente ?
                        </h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">
                            Cette action est irréversible.
                        </p>
                        <div className="flex gap-3">
                            <button
                                onClick={() => setConfirmDelete(null)}
                                className="flex-1 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg"
                            >
                                Annuler
                            </button>
                            <button
                                onClick={() => handleDelete(confirmDelete)}
                                className="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg"
                            >
                                Supprimer
                            </button>
                        </div>
                    </motion.div>
                </div>
            )}
        </AdminLayout>
    );
}

/* ============ Point Card ============ */
function PointCard({
    point,
    onToggle,
    onDelete,
}: {
    point: any;
    onToggle: () => void;
    onDelete: () => void;
}) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden hover:shadow-md transition-shadow"
        >
            {/* Image */}
            <div className="aspect-video bg-zinc-100 relative">
                <img
                    src={point.image || FALLBACK_IMAGE}
                    alt={point.name}
                    className="w-full h-full object-cover"
                    onError={(e) => {
                        (e.target as HTMLImageElement).src = FALLBACK_IMAGE;
                    }}
                />

                {/* Badge statut */}
                <div className="absolute top-2 left-2">
                    {point.is_active ? (
                        <span className="px-2 py-0.5 bg-emerald-500 text-white text-[10px] font-semibold rounded uppercase">
                            Actif
                        </span>
                    ) : (
                        <span className="px-2 py-0.5 bg-zinc-700 text-white text-[10px] font-semibold rounded uppercase">
                            Inactif
                        </span>
                    )}
                </div>

                {/* Badge géolocalisation */}
                {point.has_location && (
                    <div className="absolute top-2 right-2">
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-white/90 backdrop-blur-sm text-zinc-700 text-[10px] font-semibold rounded">
                            <MapPin className="w-3 h-3" />
                            GPS
                        </span>
                    </div>
                )}
            </div>

            {/* Content */}
            <div className="p-4">
                <h3 className="font-semibold text-zinc-900 mb-2 truncate">{point.name}</h3>

                <div className="space-y-1.5 mb-3 text-xs text-zinc-600">
                    {point.address && (
                        <p className="flex items-start gap-1.5">
                            <MapPin className="w-3 h-3 flex-shrink-0 mt-0.5 text-zinc-400" />
                            <span className="line-clamp-2">{point.address}</span>
                        </p>
                    )}
                    {point.phone && (
                        <p className="flex items-center gap-1.5">
                            <Phone className="w-3 h-3 flex-shrink-0 text-zinc-400" />
                            {point.phone}
                        </p>
                    )}
                    {point.hours && (
                        <p className="flex items-center gap-1.5">
                            <Clock className="w-3 h-3 flex-shrink-0 text-zinc-400" />
                            {point.hours}
                        </p>
                    )}
                    {!point.address && !point.phone && !point.hours && (
                        <p className="italic text-zinc-400">Aucune information supplémentaire</p>
                    )}
                </div>

                {/* Actions */}
                <div className="flex items-center gap-1 pt-3 border-t border-zinc-100">
                    <Link
                        href={`/admin/points-de-vente/${point.id}/edit`}
                        className="flex-1 flex items-center justify-center gap-1 py-1.5 bg-zinc-50 hover:bg-zinc-100 rounded text-xs font-medium text-zinc-700"
                    >
                        <Edit3 className="w-3 h-3" />
                        Modifier
                    </Link>
                    <button
                        onClick={onToggle}
                        className="p-1.5 hover:bg-zinc-100 rounded text-zinc-600"
                        title={point.is_active ? 'Désactiver' : 'Activer'}
                    >
                        {point.is_active ? <XCircle className="w-4 h-4" /> : <CheckCircle className="w-4 h-4" />}
                    </button>
                    <button
                        onClick={onDelete}
                        className="p-1.5 hover:bg-red-50 rounded text-zinc-600 hover:text-red-600"
                        title="Supprimer"
                    >
                        <Trash2 className="w-4 h-4" />
                    </button>
                </div>
            </div>
        </motion.div>
    );
}

function StatCard({
    icon: Icon,
    label,
    value,
    color,
}: {
    icon: any;
    label: string;
    value: number;
    color: 'emerald' | 'blue' | 'rose' | 'orange';
}) {
    const colors = {
        emerald: 'bg-emerald-50 text-emerald-600',
        blue: 'bg-blue-50 text-blue-600',
        rose: 'bg-rose-50 text-rose-600',
        orange: 'bg-orange-50 text-orange-600',
    };
    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-white rounded-2xl p-5 shadow-sm border border-zinc-200"
        >
            <div className={`w-10 h-10 rounded-xl flex items-center justify-center mb-3 ${colors[color]}`}>
                <Icon className="w-5 h-5" />
            </div>
            <p className="text-xs font-medium text-zinc-500 mb-1">{label}</p>
            <p className="text-2xl font-bold text-zinc-900 tabular-nums">{value.toLocaleString('fr-FR')}</p>
        </motion.div>
    );
}
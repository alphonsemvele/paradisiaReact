import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    Search,
    GraduationCap,
    Plus,
    Edit3,
    Trash2,
    Clock,
    CalendarDays,
    Users,
    CheckCircle,
    Eye,
    EyeOff,
    FileText,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

interface FormationItem {
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
    document: string | null;
    statut: string;
    is_active: boolean;
    inscriptions_count: number;
    created_at_date: string;
}

interface Props {
    formations: {
        data: FormationItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    stats: { total: number; active: number; inscriptions: number; this_month: number };
    filters: { search: string | null };
}

export default function AdminFormationsIndex({ formations, stats, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/formations', { search: search || undefined }, { preserveState: true });
    };

    const toggleStatus = (id: number) => {
        router.patch(`/admin/formations/${id}/toggle-status`, {}, { preserveScroll: true });
    };

    const handleDelete = (id: number) => {
        router.delete(`/admin/formations/${id}`, {
            preserveScroll: true,
            onSuccess: () => setConfirmDelete(null),
        });
    };

    return (
        <AdminLayout title="Formations">
            <Head title="Admin - Formations" />

            <div className="space-y-6">
                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard icon={GraduationCap} label="Formations" value={stats.total} color="emerald" />
                    <StatCard icon={CheckCircle} label="Actives" value={stats.active} color="blue" />
                    <StatCard icon={Users} label="Inscriptions" value={stats.inscriptions} color="orange" />
                    <StatCard icon={Plus} label="Ce mois-ci" value={stats.this_month} color="rose" />
                </div>

                {/* Toolbar */}
                <div className="flex flex-col sm:flex-row gap-3">
                    <form onSubmit={handleSearch} className="flex-1 relative">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Rechercher une formation..."
                            className="w-full pl-10 pr-4 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </form>
                    <Link
                        href="/admin/formations/create"
                        className="flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors"
                    >
                        <Plus className="w-4 h-4" />
                        Nouvelle formation
                    </Link>
                </div>

                {/* Grille */}
                {formations.data.length === 0 ? (
                    <div className="bg-white rounded-2xl border border-zinc-200 py-16 text-center text-zinc-500">
                        <GraduationCap className="w-12 h-12 mx-auto mb-3 text-zinc-300" />
                        Aucune formation. Créez la première !
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        {formations.data.map((f) => (
                            <div
                                key={f.id}
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
                                            f.is_active ? 'bg-emerald-500 text-white' : 'bg-zinc-500 text-white'
                                        }`}
                                    >
                                        {f.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                    <span
                                        className={`absolute top-2 right-2 px-2 py-0.5 rounded text-[10px] font-semibold uppercase ${
                                            f.mode === 'en_ligne' ? 'bg-blue-500 text-white' : 'bg-zinc-900/70 text-white'
                                        }`}
                                    >
                                        {f.mode_label}
                                    </span>
                                </div>

                                <div className="p-4 flex flex-col flex-1">
                                    <h3 className="font-semibold text-zinc-900">{f.titre}</h3>

                                    <div className="mt-2 space-y-1 text-xs text-zinc-500">
                                        {f.duree && (
                                            <p className="flex items-center gap-1.5">
                                                <Clock className="w-3.5 h-3.5" /> {f.duree}
                                            </p>
                                        )}
                                        {f.session && (
                                            <p className="flex items-center gap-1.5">
                                                <CalendarDays className="w-3.5 h-3.5" /> {f.session}
                                            </p>
                                        )}
                                        <p className="flex items-center gap-1.5">
                                            <Users className="w-3.5 h-3.5" /> {f.inscriptions_count} inscrit(s)
                                        </p>
                                        {f.document && (
                                            <p className="flex items-center gap-1.5 text-emerald-600">
                                                <FileText className="w-3.5 h-3.5" /> Document joint
                                            </p>
                                        )}
                                    </div>

                                    <p className="mt-2 font-bold text-zinc-900">{f.prix_formatte}</p>

                                    <div className="mt-4 pt-3 border-t border-zinc-100 flex items-center gap-1">
                                        <Link
                                            href={`/admin/formations/${f.id}/edit`}
                                            className="flex-1 inline-flex items-center justify-center gap-1.5 py-2 text-sm text-zinc-700 hover:bg-zinc-100 rounded-lg"
                                        >
                                            <Edit3 className="w-4 h-4" /> Modifier
                                        </Link>
                                        <button
                                            onClick={() => toggleStatus(f.id)}
                                            className="p-2 text-zinc-600 hover:bg-zinc-100 rounded-lg"
                                            title={f.is_active ? 'Désactiver' : 'Activer'}
                                        >
                                            {f.is_active ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                        </button>
                                        <button
                                            onClick={() => setConfirmDelete(f.id)}
                                            className="p-2 text-zinc-600 hover:bg-red-50 hover:text-red-600 rounded-lg"
                                            title="Supprimer"
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {formations.last_page > 1 && (
                    <div className="flex justify-center gap-1 flex-wrap">
                        {formations.links.map((link, i) =>
                            link.url ? (
                                <Link
                                    key={i}
                                    href={link.url}
                                    preserveScroll
                                    className={`px-3 py-1.5 text-xs rounded-lg transition-colors ${
                                        link.active ? 'bg-emerald-500 text-white' : 'bg-white border border-zinc-200 text-zinc-600 hover:bg-zinc-100'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span key={i} className="px-3 py-1.5 text-xs text-zinc-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                            )
                        )}
                    </div>
                )}
            </div>

            {/* Modal delete */}
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
                            Supprimer cette formation ?
                        </h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">
                            Cette action est irréversible. Les inscriptions liées seront conservées mais détachées.
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

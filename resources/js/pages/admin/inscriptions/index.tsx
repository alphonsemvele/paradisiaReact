import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import { Search, Users, Zap, BookOpen, Clock, Trash2, Phone, User, GraduationCap } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

interface InscriptionItem {
    id: number;
    nom: string;
    prenom: string;
    telephone: string | null;
    type: 'acceleree' | 'normale';
    type_label: string;
    statut: 'en_attente' | 'confirme' | 'annule';
    formation: string;
    created_at_date: string;
}

interface Props {
    inscriptions: {
        data: InscriptionItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    stats: { total: number; acceleree: number; normale: number; en_attente: number };
    filters: { search: string | null; type: string | null };
}

const STATUTS: Record<InscriptionItem['statut'], string> = {
    en_attente: 'bg-amber-50 text-amber-700 border-amber-200',
    confirme: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    annule: 'bg-rose-50 text-rose-700 border-rose-200',
};

export default function InscriptionsIndex({ inscriptions, stats, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const applyFilters = (type?: string | null) => {
        router.get(
            '/admin/inscriptions',
            {
                search: search || undefined,
                type: type === undefined ? filters.type || undefined : type || undefined,
            },
            { preserveState: true, preserveScroll: true }
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilters();
    };

    const handleStatut = (id: number, statut: string) => {
        router.patch(`/admin/inscriptions/${id}/status`, { statut }, { preserveScroll: true });
    };

    const handleDelete = (id: number) => {
        router.delete(`/admin/inscriptions/${id}`, {
            preserveScroll: true,
            onSuccess: () => setConfirmDelete(null),
        });
    };

    return (
        <AdminLayout title="Inscriptions">
            <Head title="Admin - Inscriptions" />

            <div className="space-y-6">
                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard icon={Users} label="Total inscriptions" value={stats.total} color="emerald" />
                    <StatCard icon={Zap} label="Accélérée" value={stats.acceleree} color="orange" />
                    <StatCard icon={BookOpen} label="Normale" value={stats.normale} color="blue" />
                    <StatCard icon={Clock} label="En attente" value={stats.en_attente} color="rose" />
                </div>

                {/* Toolbar */}
                <div className="flex flex-col sm:flex-row gap-3">
                    <form onSubmit={handleSearch} className="flex-1 relative">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Rechercher par nom, prénom, téléphone..."
                            className="w-full pl-10 pr-4 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </form>
                    <div className="flex gap-2">
                        {[
                            { key: '', label: 'Toutes' },
                            { key: 'normale', label: 'Normale' },
                            { key: 'acceleree', label: 'Accélérée' },
                        ].map((t) => (
                            <button
                                key={t.key}
                                onClick={() => applyFilters(t.key)}
                                className={`px-4 py-2.5 text-sm font-medium rounded-lg transition-colors ${
                                    (filters.type || '') === t.key
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-white border border-zinc-200 text-zinc-600 hover:bg-zinc-50'
                                }`}
                            >
                                {t.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Liste */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th className="text-left px-5 py-3 font-medium">Participant</th>
                                    <th className="text-left px-5 py-3 font-medium">Formation</th>
                                    <th className="text-left px-5 py-3 font-medium hidden sm:table-cell">Téléphone</th>
                                    <th className="text-left px-5 py-3 font-medium">Type</th>
                                    <th className="text-left px-5 py-3 font-medium">Statut</th>
                                    <th className="text-left px-5 py-3 font-medium hidden md:table-cell">Inscrit</th>
                                    <th className="text-right px-5 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100">
                                {inscriptions.data.length === 0 && (
                                    <tr>
                                        <td colSpan={7} className="px-5 py-12 text-center text-zinc-500">
                                            <Users className="w-10 h-10 mx-auto mb-2 text-zinc-300" />
                                            Aucune inscription pour le moment.
                                        </td>
                                    </tr>
                                )}
                                {inscriptions.data.map((i) => (
                                    <tr key={i.id} className="hover:bg-zinc-50">
                                        <td className="px-5 py-3">
                                            <div className="flex items-center gap-3">
                                                <div className="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                                                    <User className="w-4 h-4 text-emerald-600" />
                                                </div>
                                                <p className="font-medium text-zinc-900">
                                                    {i.prenom} {i.nom}
                                                </p>
                                            </div>
                                        </td>
                                        <td className="px-5 py-3">
                                            <span className="inline-flex items-center gap-1.5 text-zinc-700">
                                                <GraduationCap className="w-3.5 h-3.5 text-zinc-400" />
                                                {i.formation}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3 hidden sm:table-cell text-zinc-600">
                                            {i.telephone ? (
                                                <a href={`tel:${i.telephone}`} className="inline-flex items-center gap-1 hover:text-emerald-600">
                                                    <Phone className="w-3 h-3" />
                                                    {i.telephone}
                                                </a>
                                            ) : (
                                                <span className="italic text-zinc-400">—</span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3">
                                            {i.type === 'acceleree' ? (
                                                <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-50 text-orange-700 text-[10px] font-semibold rounded uppercase">
                                                    <Zap className="w-3 h-3" /> Accélérée
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-semibold rounded uppercase">
                                                    <BookOpen className="w-3 h-3" /> Normale
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3">
                                            <select
                                                value={i.statut}
                                                onChange={(e) => handleStatut(i.id, e.target.value)}
                                                className={`text-xs font-semibold rounded-lg border px-2 py-1 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 ${STATUTS[i.statut]}`}
                                            >
                                                <option value="en_attente">En attente</option>
                                                <option value="confirme">Confirmé</option>
                                                <option value="annule">Annulé</option>
                                            </select>
                                        </td>
                                        <td className="px-5 py-3 hidden md:table-cell text-xs text-zinc-500">
                                            {i.created_at_date}
                                        </td>
                                        <td className="px-5 py-3">
                                            <div className="flex justify-end">
                                                <button
                                                    onClick={() => setConfirmDelete(i.id)}
                                                    className="p-1.5 hover:bg-red-50 rounded-lg text-zinc-600 hover:text-red-600"
                                                    title="Supprimer"
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {inscriptions.last_page > 1 && (
                        <div className="px-5 py-3 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-2">
                            <p className="text-xs text-zinc-500">
                                Page <strong>{inscriptions.current_page}</strong> sur{' '}
                                <strong>{inscriptions.last_page}</strong> · <strong>{inscriptions.total}</strong> inscriptions
                            </p>
                            <div className="flex gap-1 flex-wrap">
                                {inscriptions.links.map((link, idx) =>
                                    link.url ? (
                                        <Link
                                            key={idx}
                                            href={link.url}
                                            preserveScroll
                                            className={`px-3 py-1.5 text-xs rounded-lg transition-colors ${
                                                link.active ? 'bg-emerald-500 text-white' : 'text-zinc-600 hover:bg-zinc-100'
                                            }`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ) : (
                                        <span key={idx} className="px-3 py-1.5 text-xs text-zinc-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                                    )
                                )}
                            </div>
                        </div>
                    )}
                </div>
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
                            Supprimer cette inscription ?
                        </h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">Cette action est irréversible.</p>
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

import { Head, Link, router, useForm } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { useState } from 'react';
import {
    Search,
    FolderTree,
    Plus,
    Edit3,
    Trash2,
    CheckCircle,
    XCircle,
    Package,
    X,
    Save,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

interface CategoryItem {
    id: number;
    name: string;
    description: string | null;
    status: string | null;
    is_active: boolean;
    products_count: number;
    created_at_human: string;
    created_at_date: string;
}

interface Props {
    categories: {
        data: CategoryItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    stats: { total: number; active: number; inactive: number; with_products: number };
    filters: { search: string | null };
}

export default function CategoriesIndex({ categories, stats, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<CategoryItem | null>(null);
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/categories', { search: search || undefined }, { preserveState: true });
    };

    const handleToggleStatus = (id: number) => {
        router.patch(`/admin/categories/${id}/toggle-status`, {}, { preserveScroll: true });
    };

    const handleDelete = (id: number) => {
        router.delete(`/admin/categories/${id}`, {
            onSuccess: () => setConfirmDelete(null),
        });
    };

    const openCreate = () => {
        setEditing(null);
        setShowModal(true);
    };

    const openEdit = (cat: CategoryItem) => {
        setEditing(cat);
        setShowModal(true);
    };

    return (
        <AdminLayout title="Catégories">
            <Head title="Admin - Catégories" />

            <div className="space-y-6">
                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard icon={FolderTree} label="Total" value={stats.total} color="emerald" />
                    <StatCard icon={CheckCircle} label="Actives" value={stats.active} color="blue" />
                    <StatCard icon={XCircle} label="Inactives" value={stats.inactive} color="rose" />
                    <StatCard icon={Package} label="Avec produits" value={stats.with_products} color="orange" />
                </div>

                {/* Toolbar */}
                <div className="flex flex-col sm:flex-row gap-3">
                    <form onSubmit={handleSearch} className="flex-1 relative">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Rechercher une catégorie..."
                            className="w-full pl-10 pr-4 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </form>
                    <button
                        onClick={openCreate}
                        className="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors"
                    >
                        <Plus className="w-4 h-4" />
                        Nouvelle catégorie
                    </button>
                </div>

                {/* Liste */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th className="text-left px-5 py-3 font-medium">Catégorie</th>
                                    <th className="text-left px-5 py-3 font-medium hidden md:table-cell">Description</th>
                                    <th className="text-left px-5 py-3 font-medium">Produits</th>
                                    <th className="text-left px-5 py-3 font-medium">Statut</th>
                                    <th className="text-left px-5 py-3 font-medium hidden sm:table-cell">Créée</th>
                                    <th className="text-right px-5 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100">
                                {categories.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-5 py-12 text-center text-zinc-500">
                                            <FolderTree className="w-10 h-10 mx-auto mb-2 text-zinc-300" />
                                            Aucune catégorie. Créez la première !
                                        </td>
                                    </tr>
                                )}
                                {categories.data.map((cat) => (
                                    <tr key={cat.id} className="hover:bg-zinc-50">
                                        <td className="px-5 py-3">
                                            <div className="flex items-center gap-3">
                                                <div className="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                                                    <FolderTree className="w-4 h-4 text-emerald-600" />
                                                </div>
                                                <div>
                                                    <p className="font-medium text-zinc-900">{cat.name}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-5 py-3 hidden md:table-cell">
                                            <p className="text-xs text-zinc-600 max-w-xs truncate">
                                                {cat.description || <span className="italic text-zinc-400">—</span>}
                                            </p>
                                        </td>
                                        <td className="px-5 py-3">
                                            <Link
                                                href={`/admin/products?category=${cat.id}`}
                                                className="inline-flex items-center gap-1 px-2 py-0.5 bg-zinc-100 hover:bg-emerald-100 hover:text-emerald-700 rounded text-xs font-medium transition-colors"
                                            >
                                                <Package className="w-3 h-3" />
                                                {cat.products_count}
                                            </Link>
                                        </td>
                                        <td className="px-5 py-3">
                                            {cat.is_active ? (
                                                <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-semibold rounded uppercase">
                                                    <CheckCircle className="w-3 h-3" /> Active
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-zinc-100 text-zinc-600 text-[10px] font-semibold rounded uppercase">
                                                    <XCircle className="w-3 h-3" /> Inactive
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3 hidden sm:table-cell text-xs text-zinc-500">
                                            {cat.created_at_date}
                                        </td>
                                        <td className="px-5 py-3">
                                            <div className="flex justify-end gap-1">
                                                <button
                                                    onClick={() => openEdit(cat)}
                                                    className="p-1.5 hover:bg-zinc-100 rounded-lg text-zinc-600 hover:text-emerald-600"
                                                    title="Modifier"
                                                >
                                                    <Edit3 className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={() => handleToggleStatus(cat.id)}
                                                    className="p-1.5 hover:bg-zinc-100 rounded-lg text-zinc-600"
                                                    title={cat.is_active ? 'Désactiver' : 'Activer'}
                                                >
                                                    {cat.is_active ? <XCircle className="w-4 h-4" /> : <CheckCircle className="w-4 h-4" />}
                                                </button>
                                                <button
                                                    onClick={() => setConfirmDelete(cat.id)}
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

                    {categories.last_page > 1 && (
                        <div className="px-5 py-3 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-2">
                            <p className="text-xs text-zinc-500">
                                Page <strong>{categories.current_page}</strong> sur{' '}
                                <strong>{categories.last_page}</strong> · <strong>{categories.total}</strong> catégories
                            </p>
                            <div className="flex gap-1 flex-wrap">
                                {categories.links.map((link, i) =>
                                    link.url ? (
                                        <Link
                                            key={i}
                                            href={link.url}
                                            preserveScroll
                                            className={`px-3 py-1.5 text-xs rounded-lg transition-colors ${
                                                link.active ? 'bg-emerald-500 text-white' : 'text-zinc-600 hover:bg-zinc-100'
                                            }`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ) : (
                                        <span key={i} className="px-3 py-1.5 text-xs text-zinc-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                                    )
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal Create/Edit */}
            <AnimatePresence>
                {showModal && (
                    <CategoryModal
                        category={editing}
                        onClose={() => {
                            setShowModal(false);
                            setEditing(null);
                        }}
                    />
                )}
            </AnimatePresence>

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
                        <h3 className="text-lg font-semibold text-zinc-900 text-center mb-2">Supprimer cette catégorie ?</h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">
                            Cette action est irréversible. La catégorie ne peut être supprimée que si elle ne contient
                            aucun produit.
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

/* ============ Modal Form ============ */
function CategoryModal({ category, onClose }: { category: CategoryItem | null; onClose: () => void }) {
    const isEdit = !!category;

    const { data, setData, post, patch, processing, errors, reset } = useForm({
        name: category?.name || '',
        description: category?.description || '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            patch(`/admin/categories/${category!.id}`, { onSuccess: () => { reset(); onClose(); }, preserveScroll: true });
        } else {
            post('/admin/categories', { onSuccess: () => { reset(); onClose(); }, preserveScroll: true });
        }
    };

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onClick={onClose}>
            <motion.div
                initial={{ scale: 0.9, opacity: 0 }}
                animate={{ scale: 1, opacity: 1 }}
                exit={{ scale: 0.9, opacity: 0 }}
                onClick={(e) => e.stopPropagation()}
                className="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full"
            >
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-lg font-semibold text-zinc-900">
                        {isEdit ? 'Modifier la catégorie' : 'Nouvelle catégorie'}
                    </h3>
                    <button onClick={onClose} className="p-1 hover:bg-zinc-100 rounded">
                        <X className="w-5 h-5" />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5">Nom</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Ex: Jus tropicaux"
                            autoFocus
                            required
                            className={`w-full px-3 py-2.5 bg-zinc-50 border ${
                                errors.name ? 'border-red-300' : 'border-zinc-200'
                            } rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                        />
                        {errors.name && <p className="text-xs text-red-600 mt-1">{errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5">Description</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows={3}
                            placeholder="Description courte..."
                            className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                        />
                    </div>

                    <div className="flex gap-2 pt-2">
                        <button
                            type="button"
                            onClick={onClose}
                            className="flex-1 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-300 text-white font-medium rounded-lg"
                        >
                            <Save className="w-4 h-4" />
                            {processing ? '...' : 'Enregistrer'}
                        </button>
                    </div>
                </form>
            </motion.div>
        </div>
    );
}

function StatCard({ icon: Icon, label, value, color }: { icon: any; label: string; value: number; color: 'emerald' | 'blue' | 'rose' | 'orange' }) {
    const colors = {
        emerald: 'bg-emerald-50 text-emerald-600',
        blue: 'bg-blue-50 text-blue-600',
        rose: 'bg-rose-50 text-rose-600',
        orange: 'bg-orange-50 text-orange-600',
    };
    return (
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="bg-white rounded-2xl p-5 shadow-sm border border-zinc-200">
            <div className={`w-10 h-10 rounded-xl flex items-center justify-center mb-3 ${colors[color]}`}>
                <Icon className="w-5 h-5" />
            </div>
            <p className="text-xs font-medium text-zinc-500 mb-1">{label}</p>
            <p className="text-2xl font-bold text-zinc-900 tabular-nums">{value.toLocaleString('fr-FR')}</p>
        </motion.div>
    );
}
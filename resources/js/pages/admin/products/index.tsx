import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    Search,
    Package,
    Plus,
    Edit3,
    Trash2,
    CheckCircle,
    XCircle,
    Filter,
    ChevronDown,
    TrendingUp,
    ImageOff,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

interface ProductItem {
    id: number;
    name: string;
    description: string | null;
    price: number;
    price_formatted: string;
    status: string | null;
    is_active: boolean;
    img_1: string | null;
    img_2: string | null;
    category: { id: number; name: string } | null;
    created_at_human: string;
    created_at_date: string;
}

interface Props {
    products: {
        data: ProductItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    categories: Array<{ id: number; name: string }>;
    stats: { total: number; active: number; inactive: number; this_month: number };
    filters: { search: string | null; category: string | null; status: string | null };
}

export default function ProductsIndex({ products, categories, stats, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const applyFilter = (key: string, value: string | null) => {
        router.get('/admin/products', { ...filters, [key]: value || undefined }, { preserveState: true });
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilter('search', search);
    };

    const handleToggleStatus = (id: number) => {
        router.patch(`/admin/products/${id}/toggle-status`, {}, { preserveScroll: true });
    };

    const handleDelete = (id: number) => {
        router.delete(`/admin/products/${id}`, { onSuccess: () => setConfirmDelete(null) });
    };

    const hasFilters = filters.category || filters.status;

    return (
        <AdminLayout title="Produits">
            <Head title="Admin - Produits" />

            <div className="space-y-6">
                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard icon={Package} label="Total" value={stats.total} color="emerald" />
                    <StatCard icon={CheckCircle} label="Actifs" value={stats.active} color="blue" />
                    <StatCard icon={XCircle} label="Inactifs" value={stats.inactive} color="rose" />
                    <StatCard icon={TrendingUp} label="Ce mois" value={stats.this_month} color="orange" />
                </div>

                {/* Toolbar */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-4">
                    <div className="flex flex-col lg:flex-row gap-3">
                        <form onSubmit={handleSearch} className="flex-1 relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                            <input
                                type="search"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Rechercher un produit..."
                                className="w-full pl-10 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </form>
                        <button
                            onClick={() => setShowFilters(!showFilters)}
                            className={`flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                                hasFilters ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-zinc-50 text-zinc-700 border border-zinc-200 hover:bg-zinc-100'
                            }`}
                        >
                            <Filter className="w-4 h-4" />
                            Filtres
                            <ChevronDown className={`w-4 h-4 transition-transform ${showFilters ? 'rotate-180' : ''}`} />
                        </button>
                        <Link
                            href="/admin/products/create"
                            className="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors"
                        >
                            <Plus className="w-4 h-4" />
                            Nouveau produit
                        </Link>
                    </div>

                    {showFilters && (
                        <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} className="mt-4 pt-4 border-t border-zinc-100 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label className="block text-xs font-medium text-zinc-500 mb-1.5">Catégorie</label>
                                <select
                                    value={filters.category || ''}
                                    onChange={(e) => applyFilter('category', e.target.value || null)}
                                    className="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                >
                                    <option value="">Toutes</option>
                                    {categories.map((c) => (
                                        <option key={c.id} value={c.id}>{c.name}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-zinc-500 mb-1.5">Statut</label>
                                <select
                                    value={filters.status || ''}
                                    onChange={(e) => applyFilter('status', e.target.value || null)}
                                    className="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                >
                                    <option value="">Tous</option>
                                    <option value="active">Actifs</option>
                                    <option value="inactive">Inactifs</option>
                                </select>
                            </div>
                        </motion.div>
                    )}
                </div>

                {/* Grille produits */}
                {products.data.length === 0 ? (
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-12 text-center">
                        <Package className="w-12 h-12 mx-auto mb-3 text-zinc-300" />
                        <p className="text-zinc-500 mb-4">Aucun produit trouvé</p>
                        <Link
                            href="/admin/products/create"
                            className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white text-sm rounded-lg hover:bg-emerald-600"
                        >
                            <Plus className="w-4 h-4" />
                            Créer le premier produit
                        </Link>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        {products.data.map((product) => (
                            <motion.div
                                key={product.id}
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden hover:shadow-md transition-shadow"
                            >
                                {/* Image */}
                                <div className="aspect-square bg-zinc-100 relative">
                                    {product.img_1 ? (
                                        <img src={product.img_1} alt={product.name} className="w-full h-full object-cover" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center">
                                            <ImageOff className="w-12 h-12 text-zinc-300" />
                                        </div>
                                    )}

                                    {/* Badge statut */}
                                    <div className="absolute top-2 left-2">
                                        {product.is_active ? (
                                            <span className="px-2 py-0.5 bg-emerald-500 text-white text-[10px] font-semibold rounded uppercase">
                                                Actif
                                            </span>
                                        ) : (
                                            <span className="px-2 py-0.5 bg-zinc-700 text-white text-[10px] font-semibold rounded uppercase">
                                                Inactif
                                            </span>
                                        )}
                                    </div>

                                    {/* Actions hover */}
                                    <div className="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Link
                                            href={`/admin/products/${product.id}/edit`}
                                            className="p-1.5 bg-white shadow-md rounded-lg hover:bg-zinc-50"
                                        >
                                            <Edit3 className="w-3.5 h-3.5 text-zinc-700" />
                                        </Link>
                                    </div>
                                </div>

                                {/* Infos */}
                                <div className="p-4">
                                    {product.category && (
                                        <p className="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider mb-1">
                                            {product.category.name}
                                        </p>
                                    )}
                                    <h3 className="font-semibold text-zinc-900 mb-1 truncate">{product.name}</h3>
                                    <p className="text-lg font-bold text-emerald-600 mb-3">{product.price_formatted}</p>

                                    <div className="flex items-center gap-1 pt-3 border-t border-zinc-100">
                                        <Link
                                            href={`/admin/products/${product.id}/edit`}
                                            className="flex-1 flex items-center justify-center gap-1 py-1.5 bg-zinc-50 hover:bg-zinc-100 rounded text-xs font-medium text-zinc-700 transition-colors"
                                        >
                                            <Edit3 className="w-3 h-3" />
                                            Modifier
                                        </Link>
                                        <button
                                            onClick={() => handleToggleStatus(product.id)}
                                            className="p-1.5 hover:bg-zinc-100 rounded text-zinc-600"
                                            title={product.is_active ? 'Désactiver' : 'Activer'}
                                        >
                                            {product.is_active ? <XCircle className="w-4 h-4" /> : <CheckCircle className="w-4 h-4" />}
                                        </button>
                                        <button
                                            onClick={() => setConfirmDelete(product.id)}
                                            className="p-1.5 hover:bg-red-50 rounded text-zinc-600 hover:text-red-600"
                                            title="Supprimer"
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </motion.div>
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {products.last_page > 1 && (
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 px-5 py-3 flex flex-col sm:flex-row items-center justify-between gap-2">
                        <p className="text-xs text-zinc-500">
                            Page <strong>{products.current_page}</strong> sur <strong>{products.last_page}</strong> · <strong>{products.total}</strong> produits
                        </p>
                        <div className="flex gap-1 flex-wrap">
                            {products.links.map((link, i) =>
                                link.url ? (
                                    <Link
                                        key={i}
                                        href={link.url}
                                        preserveScroll
                                        className={`px-3 py-1.5 text-xs rounded-lg ${link.active ? 'bg-emerald-500 text-white' : 'text-zinc-600 hover:bg-zinc-100'}`}
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

            {/* Modal Delete */}
            {confirmDelete !== null && (
                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onClick={() => setConfirmDelete(null)}>
                    <motion.div initial={{ scale: 0.9, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} onClick={(e) => e.stopPropagation()} className="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full">
                        <div className="w-12 h-12 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <Trash2 className="w-6 h-6 text-red-600" />
                        </div>
                        <h3 className="text-lg font-semibold text-zinc-900 text-center mb-2">Supprimer ce produit ?</h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">Cette action est irréversible.</p>
                        <div className="flex gap-3">
                            <button onClick={() => setConfirmDelete(null)} className="flex-1 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg">
                                Annuler
                            </button>
                            <button onClick={() => handleDelete(confirmDelete)} className="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg">
                                Supprimer
                            </button>
                        </div>
                    </motion.div>
                </div>
            )}
        </AdminLayout>
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
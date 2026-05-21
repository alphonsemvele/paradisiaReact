import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    Search,
    Receipt,
    Filter,
    ChevronDown,
    Plus,
    Eye,
    Banknote,
    Smartphone,
    CreditCard,
    Calendar,
    DollarSign,
    Clock,
    CheckCircle,
    XCircle,
    AlertCircle,
    Trash2,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

const formatFCFA = (n: number) =>
    new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';

const PAYMENT_CONFIG: any = {
    cash: { label: 'Espèces', icon: Banknote, color: 'emerald' },
    mobile_money: { label: 'Mobile Money', icon: Smartphone, color: 'orange' },
    card: { label: 'Carte', icon: CreditCard, color: 'blue' },
};

const STATUS_CONFIG: any = {
    Success: { label: 'Validée', icon: CheckCircle, bg: 'bg-emerald-100', text: 'text-emerald-700' },
    pending: { label: 'En attente', icon: Clock, bg: 'bg-orange-100', text: 'text-orange-700' },
    waiting: { label: 'En cours', icon: AlertCircle, bg: 'bg-blue-100', text: 'text-blue-700' },
    failed: { label: 'Échouée', icon: XCircle, bg: 'bg-red-100', text: 'text-red-700' },
    deleted: { label: 'Supprimée', icon: Trash2, bg: 'bg-zinc-200', text: 'text-zinc-700' },
};

export default function SalesIndex({ sales, stats, pointsDeVente, filters }: any) {
    const [search, setSearch] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);

    const applyFilter = (key: string, value: string | null) => {
        router.get(
            '/admin/sales/list',
            { ...filters, [key]: value || undefined },
            { preserveState: true },
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilter('search', search);
    };

    const hasFilters =
        filters.status || filters.point || filters.payment_method || filters.date_from || filters.date_to;

    return (
        <AdminLayout title="Liste des ventes">
            <Head title="Admin - Liste des ventes" />

            <div className="space-y-6">
                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <KpiCard icon={Receipt} label="Total ventes" value={stats.total.toString()} color="emerald" />
                    <KpiCard icon={Calendar} label="Aujourd'hui" value={stats.today.toString()} color="blue" />
                    <KpiCard icon={DollarSign} label="CA Semaine" value={formatFCFA(stats.week_ca)} color="orange" />
                    <KpiCard icon={Clock} label="En attente" value={stats.pending.toString()} color="rose" />
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
                                placeholder="Rechercher par référence, client..."
                                className="w-full pl-10 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </form>
                        <button
                            onClick={() => setShowFilters(!showFilters)}
                            className={`flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium ${
                                hasFilters
                                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                    : 'bg-zinc-50 text-zinc-700 border border-zinc-200 hover:bg-zinc-100'
                            }`}
                        >
                            <Filter className="w-4 h-4" />
                            Filtres
                            <ChevronDown className={`w-4 h-4 transition-transform ${showFilters ? 'rotate-180' : ''}`} />
                        </button>
                        <Link
                            href="/admin/sales/create"
                            className="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600"
                        >
                            <Plus className="w-4 h-4" />
                            Nouvelle vente
                        </Link>
                    </div>

                    {showFilters && (
                        <motion.div
                            initial={{ opacity: 0, height: 0 }}
                            animate={{ opacity: 1, height: 'auto' }}
                            className="mt-4 pt-4 border-t border-zinc-100 grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-3"
                        >
                            <select
                                value={filters.status || ''}
                                onChange={(e) => applyFilter('status', e.target.value || null)}
                                className="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm"
                            >
                                <option value="">Tous statuts</option>
                                <option value="Success">Validée</option>
                                <option value="pending">En attente</option>
                                <option value="waiting">En cours</option>
                                <option value="failed">Échouée</option>
                                <option value="deleted">Supprimée</option>
                            </select>
                            <select
                                value={filters.point || ''}
                                onChange={(e) => applyFilter('point', e.target.value || null)}
                                className="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm"
                            >
                                <option value="">Tous points</option>
                                {pointsDeVente.map((p: any) => (
                                    <option key={p.id} value={p.id}>{p.name}</option>
                                ))}
                            </select>
                            <select
                                value={filters.payment_method || ''}
                                onChange={(e) => applyFilter('payment_method', e.target.value || null)}
                                className="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm"
                            >
                                <option value="">Tous paiements</option>
                                <option value="cash">Espèces</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="card">Carte</option>
                            </select>
                            <input
                                type="date"
                                value={filters.date_from || ''}
                                onChange={(e) => applyFilter('date_from', e.target.value || null)}
                                className="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm"
                            />
                            <input
                                type="date"
                                value={filters.date_to || ''}
                                onChange={(e) => applyFilter('date_to', e.target.value || null)}
                                className="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm"
                            />
                        </motion.div>
                    )}
                </div>

                {/* Table */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th className="text-left px-5 py-3 font-medium">Référence</th>
                                    <th className="text-left px-5 py-3 font-medium hidden sm:table-cell">Client</th>
                                    <th className="text-left px-5 py-3 font-medium hidden md:table-cell">Lieu</th>
                                    <th className="text-left px-5 py-3 font-medium hidden lg:table-cell">Paiement</th>
                                    <th className="text-left px-5 py-3 font-medium">Statut</th>
                                    <th className="text-right px-5 py-3 font-medium">Montant</th>
                                    <th className="text-right px-5 py-3 font-medium hidden sm:table-cell">Date</th>
                                    <th className="text-right px-5 py-3 font-medium">Action</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100">
                                {sales.data.length === 0 && (
                                    <tr>
                                        <td colSpan={8} className="text-center py-12 text-zinc-500">
                                            <Receipt className="w-10 h-10 mx-auto mb-2 text-zinc-300" />
                                            Aucune vente trouvée
                                        </td>
                                    </tr>
                                )}
                                {sales.data.map((s: any) => {
                                    const payment = PAYMENT_CONFIG[s.payment_method];
                                    const status = STATUS_CONFIG[s.status] || STATUS_CONFIG.pending;
                                    const PaymentIcon = payment?.icon || Banknote;
                                    const StatusIcon = status.icon;
                                    return (
                                        <tr key={s.id} className="hover:bg-zinc-50">
                                            <td className="px-5 py-3">
                                                <Link
                                                    href={`/admin/sales/${s.id}`}
                                                    className="text-xs font-mono text-emerald-600 hover:underline"
                                                >
                                                    {s.ref}
                                                </Link>
                                                <p className="text-[10px] text-zinc-400 mt-0.5">
                                                    {s.items_count} article{s.items_count > 1 ? 's' : ''}
                                                </p>
                                            </td>
                                            <td className="px-5 py-3 hidden sm:table-cell text-sm">
                                                {s.customer_name || (
                                                    <span className="italic text-zinc-400">Anonyme</span>
                                                )}
                                                {s.customer_phone && (
                                                    <p className="text-[10px] text-zinc-500">{s.customer_phone}</p>
                                                )}
                                            </td>
                                            <td className="px-5 py-3 hidden md:table-cell text-xs text-zinc-600">
                                                {s.point_name || <span className="italic text-zinc-400">—</span>}
                                            </td>
                                            <td className="px-5 py-3 hidden lg:table-cell">
                                                <span className="inline-flex items-center gap-1 text-xs text-zinc-700">
                                                    <PaymentIcon className="w-3 h-3" />
                                                    {payment?.label}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3">
                                                <span
                                                    className={`inline-flex items-center gap-1 px-2 py-0.5 ${status.bg} ${status.text} text-[10px] font-semibold rounded uppercase`}
                                                >
                                                    <StatusIcon className="w-3 h-3" />
                                                    {status.label}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3 text-right text-sm font-semibold text-zinc-900">
                                                {formatFCFA(s.total)}
                                            </td>
                                            <td className="px-5 py-3 text-right hidden sm:table-cell text-xs text-zinc-500">
                                                {s.sale_date_human}
                                            </td>
                                            <td className="px-5 py-3 text-right">
                                                <Link
                                                    href={`/admin/sales/${s.id}`}
                                                    className="p-1.5 hover:bg-zinc-100 rounded-lg text-zinc-600 hover:text-emerald-600 inline-flex"
                                                >
                                                    <Eye className="w-4 h-4" />
                                                </Link>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    {sales.last_page > 1 && (
                        <div className="px-5 py-3 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-2">
                            <p className="text-xs text-zinc-500">
                                Page <strong>{sales.current_page}</strong> sur <strong>{sales.last_page}</strong> · <strong>{sales.total}</strong> ventes
                            </p>
                            <div className="flex gap-1 flex-wrap">
                                {sales.links.map((link: any, i: number) =>
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
                                        <span key={i} className="px-3 py-1.5 text-xs text-zinc-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                                    )
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}

function KpiCard({ icon: Icon, label, value, color }: any) {
    const colors: any = {
        emerald: 'bg-emerald-50 text-emerald-600',
        blue: 'bg-blue-50 text-blue-600',
        orange: 'bg-orange-50 text-orange-600',
        rose: 'bg-rose-50 text-rose-600',
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
            <p className="text-xl font-bold text-zinc-900 tabular-nums">{value}</p>
        </motion.div>
    );
}
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    DollarSign,
    TrendingUp,
    TrendingDown,
    ShoppingCart,
    Receipt,
    Banknote,
    Smartphone,
    CreditCard,
    Package,
    Store,
    Plus,
} from 'lucide-react';
import {
    AreaChart,
    Area,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer,
    CartesianGrid,
} from 'recharts';
import AdminLayout from '@/components/layouts/AdminLayout';

const PAYMENT_ICONS: any = {
    banknote: Banknote,
    smartphone: Smartphone,
    'credit-card': CreditCard,
};

const formatFCFA = (n: number) =>
    new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';

export default function SalesDashboard({
    kpis,
    salesChart,
    topProducts,
    topPointsDeVente,
    topCategories,
    paymentMethods,
    recentSales,
}: any) {
    return (
        <AdminLayout title="Statistiques de ventes">
            <Head title="Admin - Statistiques de ventes" />

            <div className="space-y-6">
                {/* ============ Header ============ */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-700 rounded-2xl p-6 lg:p-8 text-white shadow-lg shadow-emerald-600/20"
                >
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 className="text-2xl lg:text-3xl font-bold mb-1">
                                Ventes Paradisia 💰
                            </h2>
                            <p className="text-emerald-50 text-sm lg:text-base">
                                Analysez vos performances de vente en temps réel
                            </p>
                        </div>
                        <div className="flex gap-3">
                            <Link
                                href="/admin/sales/list"
                                className="px-4 py-2 bg-white/15 hover:bg-white/25 backdrop-blur-sm rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"
                            >
                                <Receipt className="w-4 h-4" />
                                Toutes les ventes
                            </Link>
                            <Link
                                href="/admin/sales/create"
                                className="px-4 py-2 bg-white text-emerald-700 hover:bg-emerald-50 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"
                            >
                                <Plus className="w-4 h-4" />
                                Nouvelle vente
                            </Link>
                        </div>
                    </div>
                </motion.div>

                {/* ============ KPIs ============ */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <KpiCard
                        icon={DollarSign}
                        label="CA Aujourd'hui"
                        value={formatFCFA(kpis.today_ca)}
                        sublabel={`${kpis.today_sales} vente${kpis.today_sales > 1 ? 's' : ''}`}
                        trend={kpis.day_growth}
                        color="emerald"
                    />
                    <KpiCard
                        icon={TrendingUp}
                        label="CA Ce mois"
                        value={formatFCFA(kpis.month_ca)}
                        sublabel="Vs mois dernier"
                        trend={kpis.month_growth}
                        color="blue"
                    />
                    <KpiCard
                        icon={ShoppingCart}
                        label="CA Total"
                        value={formatFCFA(kpis.total_ca)}
                        sublabel={`${kpis.total_sales} ventes`}
                        color="orange"
                    />
                    <KpiCard
                        icon={Receipt}
                        label="Panier moyen"
                        value={formatFCFA(kpis.avg_basket)}
                        sublabel="Tous temps"
                        color="rose"
                    />
                </div>

                {/* ============ Graphique CA 30j ============ */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5"
                >
                    <div className="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 className="font-semibold text-zinc-900">Chiffre d'affaires</h3>
                            <p className="text-xs text-zinc-500">30 derniers jours</p>
                        </div>
                        <div className="flex items-center gap-4 text-xs">
                            <span className="flex items-center gap-1.5">
                                <span className="w-2.5 h-2.5 bg-emerald-500 rounded-full" />
                                CA (FCFA)
                            </span>
                        </div>
                    </div>

                    <ResponsiveContainer width="100%" height={280}>
                        <AreaChart data={salesChart}>
                            <defs>
                                <linearGradient id="caGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stopColor="#10b981" stopOpacity={0.3} />
                                    <stop offset="100%" stopColor="#10b981" stopOpacity={0} />
                                </linearGradient>
                            </defs>
                            <CartesianGrid strokeDasharray="3 3" stroke="#e4e4e7" vertical={false} />
                            <XAxis
                                dataKey="label"
                                stroke="#a1a1aa"
                                fontSize={11}
                                tickLine={false}
                                axisLine={false}
                            />
                            <YAxis
                                stroke="#a1a1aa"
                                fontSize={11}
                                tickLine={false}
                                axisLine={false}
                                tickFormatter={(v) => `${(v / 1000).toFixed(0)}k`}
                            />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: '#18181b',
                                    border: 'none',
                                    borderRadius: 8,
                                    color: '#fff',
                                    fontSize: 12,
                                }}
                                formatter={(value: any) => [formatFCFA(value), 'CA']}
                            />
                            <Area
                                type="monotone"
                                dataKey="ca"
                                stroke="#10b981"
                                strokeWidth={2.5}
                                fill="url(#caGrad)"
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                </motion.div>

                {/* ============ Top produits + Top points ============ */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Top produits */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                        <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                            <Package className="w-4 h-4 text-emerald-600" />
                            <h3 className="font-semibold text-zinc-900">Top produits</h3>
                        </div>
                        <div className="divide-y divide-zinc-100">
                            {topProducts.length === 0 && (
                                <p className="px-5 py-8 text-sm text-zinc-500 text-center">
                                    Aucune vente enregistrée
                                </p>
                            )}
                            {topProducts.map((p: any, i: number) => {
                                const max = topProducts[0]?.qty || 1;
                                const percent = (p.qty / max) * 100;
                                return (
                                    <div key={p.id} className="px-5 py-3">
                                        <div className="flex items-center gap-3">
                                            <span className="text-xs font-bold text-zinc-400 w-4">
                                                #{i + 1}
                                            </span>
                                            {p.image ? (
                                                <img
                                                    src={p.image}
                                                    alt={p.name}
                                                    className="w-10 h-10 rounded-lg object-cover"
                                                />
                                            ) : (
                                                <div className="w-10 h-10 rounded-lg bg-zinc-100 flex items-center justify-center">
                                                    <Package className="w-4 h-4 text-zinc-400" />
                                                </div>
                                            )}
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium text-zinc-900 truncate">
                                                    {p.name}
                                                </p>
                                                <p className="text-xs text-zinc-500">
                                                    <strong>{p.qty}</strong> vendus ·{' '}
                                                    {formatFCFA(p.ca)}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="mt-2 h-1.5 bg-zinc-100 rounded-full overflow-hidden ml-7">
                                            <motion.div
                                                initial={{ width: 0 }}
                                                animate={{ width: `${percent}%` }}
                                                transition={{ duration: 0.6, delay: i * 0.05 }}
                                                className="h-full bg-emerald-500 rounded-full"
                                            />
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Top points de vente */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                        <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                            <Store className="w-4 h-4 text-emerald-600" />
                            <h3 className="font-semibold text-zinc-900">Top points de vente</h3>
                        </div>
                        <div className="divide-y divide-zinc-100">
                            {topPointsDeVente.length === 0 && (
                                <p className="px-5 py-8 text-sm text-zinc-500 text-center">
                                    Aucune vente en point de vente
                                </p>
                            )}
                            {topPointsDeVente.map((p: any, i: number) => {
                                const max = topPointsDeVente[0]?.ca || 1;
                                const percent = (p.ca / max) * 100;
                                return (
                                    <div key={p.id} className="px-5 py-3">
                                        <div className="flex items-center justify-between mb-1.5">
                                            <div className="flex items-center gap-2">
                                                <span className="text-xs font-bold text-zinc-400">
                                                    #{i + 1}
                                                </span>
                                                <span className="text-sm font-medium text-zinc-900">
                                                    {p.name}
                                                </span>
                                            </div>
                                            <span className="text-xs font-semibold text-emerald-600">
                                                {formatFCFA(p.ca)}
                                            </span>
                                        </div>
                                        <p className="text-xs text-zinc-500 mb-1.5 ml-6">
                                            {p.sales} vente{p.sales > 1 ? 's' : ''}
                                        </p>
                                        <div className="h-1.5 bg-zinc-100 rounded-full overflow-hidden ml-6">
                                            <motion.div
                                                initial={{ width: 0 }}
                                                animate={{ width: `${percent}%` }}
                                                transition={{ duration: 0.6, delay: i * 0.05 }}
                                                className="h-full bg-orange-500 rounded-full"
                                            />
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* ============ Catégories + Paiements ============ */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Top catégories */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                        <h3 className="font-semibold text-zinc-900 mb-1">Top catégories</h3>
                        <p className="text-xs text-zinc-500 mb-4">Ventes par catégorie</p>
                        <div className="space-y-3">
                            {topCategories.length === 0 && (
                                <p className="text-sm text-zinc-400 text-center py-4">
                                    Aucune donnée
                                </p>
                            )}
                            {topCategories.map((c: any) => {
                                const max = topCategories[0]?.ca || 1;
                                const percent = (c.ca / max) * 100;
                                return (
                                    <div key={c.id}>
                                        <div className="flex items-center justify-between mb-1">
                                            <span className="text-sm text-zinc-700 font-medium">
                                                {c.name}
                                            </span>
                                            <span className="text-xs font-semibold text-zinc-900">
                                                {formatFCFA(c.ca)}
                                            </span>
                                        </div>
                                        <div className="h-2 bg-zinc-100 rounded-full overflow-hidden">
                                            <motion.div
                                                initial={{ width: 0 }}
                                                animate={{ width: `${percent}%` }}
                                                transition={{ duration: 0.6 }}
                                                className="h-full bg-blue-500 rounded-full"
                                            />
                                        </div>
                                        <p className="text-[10px] text-zinc-400 mt-0.5">
                                            {c.qty} produits vendus
                                        </p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Méthodes de paiement */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                        <h3 className="font-semibold text-zinc-900 mb-1">Méthodes de paiement</h3>
                        <p className="text-xs text-zinc-500 mb-4">Répartition</p>
                        <div className="space-y-4">
                            {paymentMethods.map((m: any) => {
                                const Icon = PAYMENT_ICONS[m.icon] || Banknote;
                                return (
                                    <div key={m.key}>
                                        <div className="flex items-center justify-between mb-1.5">
                                            <div className="flex items-center gap-2">
                                                <Icon className="w-4 h-4 text-zinc-500" />
                                                <span className="text-sm font-medium text-zinc-700">
                                                    {m.name}
                                                </span>
                                            </div>
                                            <span className="text-sm text-zinc-600 tabular-nums">
                                                {m.percent}%
                                            </span>
                                        </div>
                                        <div className="h-2 bg-zinc-100 rounded-full overflow-hidden">
                                            <motion.div
                                                initial={{ width: 0 }}
                                                animate={{ width: `${m.percent}%` }}
                                                transition={{ duration: 0.8 }}
                                                className={`h-full rounded-full ${
                                                    m.key === 'cash'
                                                        ? 'bg-emerald-500'
                                                        : m.key === 'mobile_money'
                                                        ? 'bg-orange-500'
                                                        : 'bg-blue-500'
                                                }`}
                                            />
                                        </div>
                                        <p className="text-[10px] text-zinc-400 mt-0.5">
                                            {m.count} ventes · {formatFCFA(m.total)}
                                        </p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* ============ Ventes récentes ============ */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                    <div className="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                        <div>
                            <h3 className="font-semibold text-zinc-900">Ventes récentes</h3>
                            <p className="text-xs text-zinc-500">10 dernières</p>
                        </div>
                        <Link
                            href="/admin/sales/list"
                            className="text-xs font-medium text-emerald-600 hover:text-emerald-700"
                        >
                            Voir tout →
                        </Link>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th className="text-left px-5 py-3 font-medium">Référence</th>
                                    <th className="text-left px-5 py-3 font-medium hidden sm:table-cell">
                                        Client
                                    </th>
                                    <th className="text-left px-5 py-3 font-medium hidden md:table-cell">
                                        Lieu
                                    </th>
                                    <th className="text-right px-5 py-3 font-medium">Montant</th>
                                    <th className="text-right px-5 py-3 font-medium hidden sm:table-cell">
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100">
                                {recentSales.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="text-center py-8 text-zinc-500"
                                        >
                                            Aucune vente enregistrée
                                        </td>
                                    </tr>
                                )}
                                {recentSales.map((s: any) => (
                                    <tr key={s.id} className="hover:bg-zinc-50">
                                        <td className="px-5 py-3">
                                            <Link
                                                href={`/admin/sales/${s.id}`}
                                                className="text-xs font-mono text-emerald-600 hover:underline"
                                            >
                                                {s.ref}
                                            </Link>
                                        </td>
                                        <td className="px-5 py-3 hidden sm:table-cell">
                                            <span className="text-sm text-zinc-700">
                                                {s.customer_name || (
                                                    <span className="italic text-zinc-400">
                                                        Anonyme
                                                    </span>
                                                )}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3 hidden md:table-cell text-xs text-zinc-600">
                                            {s.point_name || (
                                                <span className="italic text-zinc-400">—</span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3 text-right">
                                            <span className="text-sm font-semibold text-zinc-900">
                                                {formatFCFA(s.total)}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3 text-right hidden sm:table-cell text-xs text-zinc-500">
                                            {s.sale_date_human}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function KpiCard({
    icon: Icon,
    label,
    value,
    sublabel,
    trend,
    color,
}: {
    icon: any;
    label: string;
    value: string;
    sublabel: string;
    trend?: number;
    color: 'emerald' | 'blue' | 'orange' | 'rose';
}) {
    const colorClasses = {
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
            <div className="flex items-start justify-between mb-3">
                <div
                    className={`w-10 h-10 rounded-xl flex items-center justify-center ${colorClasses[color]}`}
                >
                    <Icon className="w-5 h-5" />
                </div>
                {trend !== undefined && trend !== 0 && (
                    <span
                        className={`flex items-center gap-0.5 text-xs font-medium px-1.5 py-0.5 rounded ${
                            trend >= 0
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-red-50 text-red-700'
                        }`}
                    >
                        {trend >= 0 ? (
                            <TrendingUp className="w-3 h-3" />
                        ) : (
                            <TrendingDown className="w-3 h-3" />
                        )}
                        {Math.abs(trend)}%
                    </span>
                )}
            </div>
            <p className="text-xs font-medium text-zinc-500 mb-1">{label}</p>
            <p className="text-xl font-bold text-zinc-900 tabular-nums">{value}</p>
            <p className="text-xs text-zinc-500 mt-1">{sublabel}</p>
        </motion.div>
    );
}
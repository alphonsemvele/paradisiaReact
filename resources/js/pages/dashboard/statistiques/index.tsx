import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    TrendingUp,
    ShoppingBag,
    Package,
    Store,
    BarChart3,
    Trophy,
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
import AppLayout from '@/components/layouts/AppLayout';

const formatFCFA = (n: number) =>
    new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';

export default function StatistiquesPublique({ kpis, chart, topProducts, topPoints }: any) {
    return (
        <AppLayout>
            <Head title="Statistiques - Paradisia" />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
                {/* Hero */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-3xl p-8 lg:p-12 text-white text-center mb-8"
                >
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4">
                        <BarChart3 className="w-8 h-8" />
                    </div>
                    <h1 className="text-3xl lg:text-4xl font-bold mb-2">
                        Paradisia en chiffres 📊
                    </h1>
                    <p className="text-emerald-50 max-w-xl mx-auto">
                        Découvrez les performances et les statistiques de notre activité en temps
                        réel
                    </p>
                </motion.div>

                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <KpiCard
                        icon={ShoppingBag}
                        label="Ventes totales"
                        value={kpis.total_sales.toLocaleString('fr-FR')}
                        color="emerald"
                    />
                    <KpiCard
                        icon={TrendingUp}
                        label="CA Total"
                        value={formatFCFA(kpis.total_ca)}
                        color="blue"
                    />
                    <KpiCard
                        icon={Package}
                        label="Produits"
                        value={kpis.total_products.toLocaleString('fr-FR')}
                        color="orange"
                    />
                    <KpiCard
                        icon={Store}
                        label="Points de vente"
                        value={kpis.total_points.toLocaleString('fr-FR')}
                        color="rose"
                    />
                </div>

                {/* Graphique */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5 mb-6"
                >
                    <h3 className="font-semibold text-zinc-900 mb-1">
                        Chiffre d'affaires global
                    </h3>
                    <p className="text-xs text-zinc-500 mb-4">30 derniers jours</p>

                    <ResponsiveContainer width="100%" height={280}>
                        <AreaChart data={chart}>
                            <defs>
                                <linearGradient id="globalGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stopColor="#10b981" stopOpacity={0.3} />
                                    <stop offset="100%" stopColor="#10b981" stopOpacity={0} />
                                </linearGradient>
                            </defs>
                            <CartesianGrid strokeDasharray="3 3" stroke="#e4e4e7" vertical={false} />
                            <XAxis dataKey="label" stroke="#a1a1aa" fontSize={11} tickLine={false} axisLine={false} />
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
                                fill="url(#globalGrad)"
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                </motion.div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Top produits */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                        <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                            <Trophy className="w-4 h-4 text-emerald-600" />
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
                                    <Link
                                        key={p.id}
                                        href={`/products/${p.id}/stats`}
                                        className="block px-5 py-3 hover:bg-zinc-50 transition-colors"
                                    >
                                        <div className="flex items-center gap-3">
                                            <span className="text-xs font-bold text-zinc-400 w-5">
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
                                                    <strong>{p.qty}</strong> vendus
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
                                    </Link>
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
                            {topPoints.length === 0 && (
                                <p className="px-5 py-8 text-sm text-zinc-500 text-center">
                                    Aucune vente en point de vente
                                </p>
                            )}
                            {topPoints.map((p: any, i: number) => {
                                const max = topPoints[0]?.ca || 1;
                                const percent = (p.ca / max) * 100;
                                return (
                                    <Link
                                        key={p.id}
                                        href={`/points-de-vente/${p.id}`}
                                        className="block px-5 py-3 hover:bg-zinc-50 transition-colors"
                                    >
                                        <div className="flex items-center gap-3">
                                            <span className="text-xs font-bold text-zinc-400 w-5">
                                                #{i + 1}
                                            </span>
                                            {p.image ? (
                                                <img
                                                    src={p.image}
                                                    alt={p.name}
                                                    className="w-10 h-10 rounded-lg object-cover"
                                                />
                                            ) : (
                                                <div className="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                                                    <Store className="w-4 h-4 text-emerald-600" />
                                                </div>
                                            )}
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium text-zinc-900 truncate">
                                                    {p.name}
                                                </p>
                                                <p className="text-xs text-zinc-500">
                                                    {p.sales} ventes · {formatFCFA(p.ca)}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="mt-2 h-1.5 bg-zinc-100 rounded-full overflow-hidden ml-7">
                                            <motion.div
                                                initial={{ width: 0 }}
                                                animate={{ width: `${percent}%` }}
                                                transition={{ duration: 0.6, delay: i * 0.05 }}
                                                className="h-full bg-orange-500 rounded-full"
                                            />
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
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
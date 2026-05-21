import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ArrowLeft,
    TrendingUp,
    ShoppingBag,
    Package,
    Store,
    CheckCircle,
    XCircle,
} from 'lucide-react';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer,
    CartesianGrid,
} from 'recharts';
import AppLayout from '@/components/layouts/AppLayout';

const formatFCFA = (n: number) =>
    new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';

export default function ProductStats({ product, stats, chart, byPoint }: any) {
    return (
        <AppLayout>
            <Head title={`${product.name} - Statistiques`} />

            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <Link
                    href="/statistiques"
                    className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900 mb-6"
                >
                    <ArrowLeft className="w-4 h-4" />
                    Statistiques globales
                </Link>

                {/* Hero produit */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-3xl shadow-sm border border-zinc-200 overflow-hidden mb-6"
                >
                    <div className="grid grid-cols-1 md:grid-cols-2">
                        {/* Image */}
                        <div className="aspect-square md:aspect-auto bg-zinc-100">
                            {product.image ? (
                                <img
                                    src={product.image}
                                    alt={product.name}
                                    className="w-full h-full object-cover"
                                />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center">
                                    <Package className="w-16 h-16 text-zinc-300" />
                                </div>
                            )}
                        </div>

                        {/* Infos */}
                        <div className="p-6 lg:p-8 flex flex-col justify-center">
                            <div className="flex items-center gap-2 mb-3">
                                {product.category && (
                                    <span className="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded uppercase tracking-wider">
                                        {product.category.name}
                                    </span>
                                )}
                                {product.is_active ? (
                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded">
                                        <CheckCircle className="w-3 h-3" />
                                        Actif
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-zinc-200 text-zinc-700 text-xs font-semibold rounded">
                                        <XCircle className="w-3 h-3" />
                                        Inactif
                                    </span>
                                )}
                            </div>

                            <h1 className="text-2xl lg:text-3xl font-bold text-zinc-900 mb-2">
                                {product.name}
                            </h1>

                            {product.description && (
                                <p className="text-sm text-zinc-600 mb-4 line-clamp-3">
                                    {product.description}
                                </p>
                            )}

                            <p className="text-2xl font-bold text-emerald-600">
                                {formatFCFA(product.price)}
                            </p>
                        </div>
                    </div>
                </motion.div>

                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <KpiCard
                        icon={ShoppingBag}
                        label="Total vendu"
                        value={stats.total_qty.toLocaleString('fr-FR')}
                        sublabel="unités"
                        color="emerald"
                    />
                    <KpiCard
                        icon={TrendingUp}
                        label="CA Total"
                        value={formatFCFA(stats.total_ca)}
                        sublabel="cumulé"
                        color="blue"
                    />
                    <KpiCard
                        icon={ShoppingBag}
                        label="Ce mois"
                        value={stats.month_qty.toLocaleString('fr-FR')}
                        sublabel="unités"
                        color="orange"
                    />
                    <KpiCard
                        icon={TrendingUp}
                        label="CA Mois"
                        value={formatFCFA(stats.month_ca)}
                        sublabel="ce mois-ci"
                        color="rose"
                    />
                </div>

                {/* Graphique 30j */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5 mb-6"
                >
                    <h3 className="font-semibold text-zinc-900 mb-1">Ventes journalières</h3>
                    <p className="text-xs text-zinc-500 mb-4">30 derniers jours</p>

                    <ResponsiveContainer width="100%" height={250}>
                        <BarChart data={chart}>
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
                                allowDecimals={false}
                            />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: '#18181b',
                                    border: 'none',
                                    borderRadius: 8,
                                    color: '#fff',
                                    fontSize: 12,
                                }}
                                formatter={(value: any) => [value, 'Quantité']}
                            />
                            <Bar dataKey="qty" fill="#10b981" radius={[4, 4, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </motion.div>

                {/* Par point de vente */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                    <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                        <Store className="w-4 h-4 text-emerald-600" />
                        <h3 className="font-semibold text-zinc-900">Répartition par lieu</h3>
                    </div>
                    <div className="divide-y divide-zinc-100">
                        {byPoint.length === 0 && (
                            <p className="px-5 py-8 text-sm text-zinc-500 text-center">
                                Aucune vente enregistrée pour ce produit
                            </p>
                        )}
                        {byPoint.map((p: any, i: number) => {
                            const max = byPoint[0]?.qty || 1;
                            const percent = (p.qty / max) * 100;

                            const Content = (
                                <>
                                    <div className="flex items-center justify-between mb-1.5">
                                        <span className="text-sm font-medium text-zinc-900">
                                            {p.name}
                                        </span>
                                        <span className="text-sm font-semibold text-emerald-600">
                                            {p.qty} vendus
                                        </span>
                                    </div>
                                    <p className="text-xs text-zinc-500 mb-1.5">
                                        CA : {formatFCFA(p.ca)}
                                    </p>
                                    <div className="h-2 bg-zinc-100 rounded-full overflow-hidden">
                                        <motion.div
                                            initial={{ width: 0 }}
                                            animate={{ width: `${percent}%` }}
                                            transition={{ duration: 0.6, delay: i * 0.05 }}
                                            className="h-full bg-emerald-500 rounded-full"
                                        />
                                    </div>
                                </>
                            );

                            return p.id ? (
                                <Link
                                    key={p.id || i}
                                    href={`/points-de-vente/${p.id}`}
                                    className="block px-5 py-3 hover:bg-zinc-50 transition-colors"
                                >
                                    {Content}
                                </Link>
                            ) : (
                                <div key={`particular-${i}`} className="px-5 py-3">
                                    {Content}
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function KpiCard({ icon: Icon, label, value, sublabel, color }: any) {
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
            {sublabel && <p className="text-xs text-zinc-500 mt-1">{sublabel}</p>}
        </motion.div>
    );
}
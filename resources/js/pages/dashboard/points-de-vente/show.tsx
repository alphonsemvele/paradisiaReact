import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ArrowLeft,
    MapPin,
    Phone,
    Clock,
    Store,
    TrendingUp,
    Package,
    ShoppingBag,
    Navigation,
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

const FALLBACK = 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=1200&q=80&auto=format&fit=crop';

const formatFCFA = (n: number) =>
    new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';

export default function PointDeVenteShow({ point, stats }: any) {
    const openInMaps = () => {
        if (point.has_location) {
            window.open(
                `https://www.google.com/maps?q=${point.lat},${point.lng}`,
                '_blank',
            );
        }
    };

    return (
        <AppLayout>
            <Head title={`${point.name} - Paradisia`} />

            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <Link
                    href="/points-de-vente"
                    className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900 mb-6"
                >
                    <ArrowLeft className="w-4 h-4" />
                    Tous les points de vente
                </Link>

                {/* Hero */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-3xl shadow-sm border border-zinc-200 overflow-hidden mb-6"
                >
                    <div className="relative aspect-video lg:aspect-[3/1] bg-zinc-100">
                        <img
                            src={point.image || FALLBACK}
                            alt={point.name}
                            className="w-full h-full object-cover"
                            onError={(e) => {
                                (e.target as HTMLImageElement).src = FALLBACK;
                            }}
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />

                        <div className="absolute bottom-0 left-0 right-0 p-6 lg:p-8 text-white">
                            <div className="flex items-center gap-3 mb-2">
                                <div className="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center">
                                    <Store className="w-5 h-5" />
                                </div>
                                <span className="px-2 py-1 bg-white/20 backdrop-blur-sm text-xs font-semibold rounded uppercase">
                                    Point de vente
                                </span>
                            </div>
                            <h1 className="text-2xl lg:text-4xl font-bold">{point.name}</h1>
                        </div>
                    </div>

                    {/* Infos */}
                    <div className="p-6 lg:p-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                        {point.address && (
                            <InfoCard
                                icon={MapPin}
                                label="Adresse"
                                value={point.address}
                                action={point.has_location ? openInMaps : undefined}
                                actionLabel="Y aller"
                            />
                        )}
                        {point.phone && (
                            <InfoCard
                                icon={Phone}
                                label="Téléphone"
                                value={point.phone}
                                action={() => (window.location.href = `tel:${point.phone}`)}
                                actionLabel="Appeler"
                            />
                        )}
                        {point.hours && (
                            <InfoCard icon={Clock} label="Horaires" value={point.hours} />
                        )}
                    </div>
                </motion.div>

                {/* KPIs */}
                <div className="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <KpiCard
                        icon={ShoppingBag}
                        label="Ventes totales"
                        value={stats.total_sales.toLocaleString('fr-FR')}
                        color="emerald"
                    />
                    <KpiCard
                        icon={TrendingUp}
                        label="CA Total"
                        value={formatFCFA(stats.total_ca)}
                        color="blue"
                    />
                    <KpiCard
                        icon={TrendingUp}
                        label="CA Ce mois"
                        value={formatFCFA(stats.month_ca)}
                        color="orange"
                    />
                </div>

                {/* Graphique */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5 mb-6"
                >
                    <h3 className="font-semibold text-zinc-900 mb-1">
                        Évolution du chiffre d'affaires
                    </h3>
                    <p className="text-xs text-zinc-500 mb-4">30 derniers jours</p>

                    <ResponsiveContainer width="100%" height={250}>
                        <AreaChart data={stats.chart}>
                            <defs>
                                <linearGradient id="caGrad" x1="0" y1="0" x2="0" y2="1">
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
                                fill="url(#caGrad)"
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                </motion.div>

                {/* Top produits */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                    <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                        <Package className="w-4 h-4 text-emerald-600" />
                        <h3 className="font-semibold text-zinc-900">Meilleures ventes</h3>
                    </div>
                    <div className="divide-y divide-zinc-100">
                        {stats.top_products.length === 0 && (
                            <p className="px-5 py-8 text-sm text-zinc-500 text-center">
                                Aucune vente enregistrée pour ce point
                            </p>
                        )}
                        {stats.top_products.map((p: any, i: number) => (
                            <Link
                                key={p.id}
                                href={`/products/${p.id}/stats`}
                                className="px-5 py-3 flex items-center gap-3 hover:bg-zinc-50 transition-colors"
                            >
                                <span className="text-sm font-bold text-zinc-400 w-5">
                                    #{i + 1}
                                </span>
                                {p.image ? (
                                    <img
                                        src={p.image}
                                        alt={p.name}
                                        className="w-12 h-12 rounded-lg object-cover"
                                    />
                                ) : (
                                    <div className="w-12 h-12 rounded-lg bg-zinc-100 flex items-center justify-center">
                                        <Package className="w-5 h-5 text-zinc-400" />
                                    </div>
                                )}
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-zinc-900">{p.name}</p>
                                    <p className="text-xs text-zinc-500">
                                        <strong>{p.qty}</strong> vendus
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function InfoCard({
    icon: Icon,
    label,
    value,
    action,
    actionLabel,
}: {
    icon: any;
    label: string;
    value: string;
    action?: () => void;
    actionLabel?: string;
}) {
    return (
        <div className="bg-zinc-50 rounded-xl p-4">
            <div className="flex items-start gap-3">
                <div className="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <Icon className="w-4 h-4 text-emerald-600" />
                </div>
                <div className="flex-1 min-w-0">
                    <p className="text-[10px] font-semibold text-zinc-500 uppercase tracking-wider mb-0.5">
                        {label}
                    </p>
                    <p className="text-sm font-medium text-zinc-900 break-words">{value}</p>
                    {action && actionLabel && (
                        <button
                            onClick={action}
                            className="mt-2 inline-flex items-center gap-1 text-xs font-medium text-emerald-600 hover:text-emerald-700"
                        >
                            <Navigation className="w-3 h-3" />
                            {actionLabel}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}

function KpiCard({ icon: Icon, label, value, color }: any) {
    const colors: any = {
        emerald: 'bg-emerald-50 text-emerald-600',
        blue: 'bg-blue-50 text-blue-600',
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
            <p className="text-xl font-bold text-zinc-900 tabular-nums">{value}</p>
        </motion.div>
    );
}
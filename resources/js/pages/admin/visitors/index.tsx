import { Head } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    Eye,
    Users,
    Activity,
    Wifi,
    TrendingUp,
    Monitor,
    Smartphone,
    Tablet,
    Globe,
} from 'lucide-react';
import {
    AreaChart,
    Area,
    BarChart,
    Bar,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer,
    CartesianGrid,
    Legend,
} from 'recharts';
import AdminLayout from '@/components/layouts/AdminLayout';

interface Props {
    stats: {
        total_visits: number;
        total_unique: number;
        today_visits: number;
        today_unique: number;
        yesterday_visits: number;
        week_visits: number;
        week_unique: number;
        online_now: number;
    };
    visitsChart: Array<{
        date: string;
        label: string;
        visits: number;
        unique: number;
    }>;
    hoursChart: Array<{ hour: string; count: number }>;
    topPages: Array<{ path: string; visits: number }>;
    devices: Array<{ name: string; icon: string; count: number; percent: number }>;
    topCountries: Array<{ country: string; country_code: string; visits: number }>;
    browsers: Array<{ browser: string; count: number }>;
    osStats: Array<{ os: string; count: number }>;
    recentVisitors: Array<{
        id: number;
        path: string;
        ip: string;
        device: string;
        browser: string;
        country: string | null;
        user: { id: number; name: string; photo: string | null } | null;
        created_at_human: string;
    }>;
}

function countryCodeToFlag(code: string | number | null): string {
    if (!code) return '🌍';

    const codeStr = String(code).trim();

    if (/^\d+$/.test(codeStr)) return '📞';
    if (codeStr.length !== 2 || !/^[a-zA-Z]{2}$/.test(codeStr)) return '🌍';

    return codeStr
        .toUpperCase()
        .replace(/./g, (c) => String.fromCodePoint(127397 + c.charCodeAt(0)));
}

export default function Visitors({
    stats,
    visitsChart,
    hoursChart,
    topPages,
    devices,
    topCountries,
    browsers,
    osStats,
    recentVisitors,
}: Props) {
    // Évolution jour vs hier
    const dayGrowth =
        stats.yesterday_visits > 0
            ? Math.round(
                  ((stats.today_visits - stats.yesterday_visits) /
                      stats.yesterday_visits) *
                      100,
              )
            : 0;

    return (
        <AdminLayout title="Visiteurs">
            <Head title="Admin - Visiteurs" />

            <div className="space-y-6">
                {/* ============== KPIs principaux ============== */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard
                        icon={Eye}
                        label="Visites totales"
                        value={stats.total_visits}
                        sublabel={`${stats.total_unique.toLocaleString('fr-FR')} visiteurs uniques`}
                        color="emerald"
                    />
                    <StatCard
                        icon={Activity}
                        label="Aujourd'hui"
                        value={stats.today_visits}
                        sublabel={`${stats.today_unique} uniques`}
                        trend={dayGrowth}
                        color="blue"
                    />
                    <StatCard
                        icon={Users}
                        label="Cette semaine"
                        value={stats.week_visits}
                        sublabel={`${stats.week_unique} uniques`}
                        color="orange"
                    />
                    <StatCard
                        icon={Wifi}
                        label="En ligne maintenant"
                        value={stats.online_now}
                        sublabel="5 dernières minutes"
                        color="rose"
                        pulse
                    />
                </div>

                {/* ============== Graphique principal ============== */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5"
                >
                    <div className="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 className="font-semibold text-zinc-900">Visites & Uniques</h3>
                            <p className="text-xs text-zinc-500">30 derniers jours</p>
                        </div>
                        <div className="flex items-center gap-4 text-xs">
                            <span className="flex items-center gap-1.5">
                                <span className="w-2.5 h-2.5 bg-emerald-500 rounded-full" />
                                Visites
                            </span>
                            <span className="flex items-center gap-1.5">
                                <span className="w-2.5 h-2.5 bg-orange-500 rounded-full" />
                                Uniques
                            </span>
                        </div>
                    </div>

                    <ResponsiveContainer width="100%" height={280}>
                        <AreaChart data={visitsChart}>
                            <defs>
                                <linearGradient id="visitsGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stopColor="#10b981" stopOpacity={0.3} />
                                    <stop offset="100%" stopColor="#10b981" stopOpacity={0} />
                                </linearGradient>
                                <linearGradient id="uniqueGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stopColor="#f97316" stopOpacity={0.3} />
                                    <stop offset="100%" stopColor="#f97316" stopOpacity={0} />
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
                            />
                            <Area
                                type="monotone"
                                dataKey="visits"
                                stroke="#10b981"
                                strokeWidth={2}
                                fill="url(#visitsGrad)"
                                name="Visites"
                            />
                            <Area
                                type="monotone"
                                dataKey="unique"
                                stroke="#f97316"
                                strokeWidth={2}
                                fill="url(#uniqueGrad)"
                                name="Uniques"
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                </motion.div>

                {/* ============== Heures de pointe + Devices ============== */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {/* Heures de pointe */}
                    <div className="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                        <div className="mb-4">
                            <h3 className="font-semibold text-zinc-900">Heures de pointe</h3>
                            <p className="text-xs text-zinc-500">Moyenne sur 7 jours</p>
                        </div>

                        <ResponsiveContainer width="100%" height={220}>
                            <BarChart data={hoursChart}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#e4e4e7" vertical={false} />
                                <XAxis
                                    dataKey="hour"
                                    stroke="#a1a1aa"
                                    fontSize={10}
                                    tickLine={false}
                                    axisLine={false}
                                    interval={1}
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
                                />
                                <Bar dataKey="count" fill="#3b82f6" radius={[4, 4, 0, 0]} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Devices */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                        <div className="mb-4">
                            <h3 className="font-semibold text-zinc-900">Devices</h3>
                            <p className="text-xs text-zinc-500">Répartition</p>
                        </div>

                        <div className="space-y-4">
                            {devices.map((device) => {
                                const Icon =
                                    device.icon === 'monitor'
                                        ? Monitor
                                        : device.icon === 'smartphone'
                                        ? Smartphone
                                        : Tablet;

                                return (
                                    <div key={device.name}>
                                        <div className="flex items-center justify-between mb-1.5">
                                            <div className="flex items-center gap-2">
                                                <Icon className="w-4 h-4 text-zinc-500" />
                                                <span className="text-sm font-medium text-zinc-700">
                                                    {device.name}
                                                </span>
                                            </div>
                                            <span className="text-sm text-zinc-600 tabular-nums">
                                                {device.percent}%
                                            </span>
                                        </div>
                                        <div className="h-2 bg-zinc-100 rounded-full overflow-hidden">
                                            <motion.div
                                                initial={{ width: 0 }}
                                                animate={{ width: `${device.percent}%` }}
                                                transition={{ duration: 0.8, delay: 0.2 }}
                                                className={`h-full rounded-full ${
                                                    device.name === 'Desktop'
                                                        ? 'bg-emerald-500'
                                                        : device.name === 'Mobile'
                                                        ? 'bg-blue-500'
                                                        : 'bg-orange-500'
                                                }`}
                                            />
                                        </div>
                                        <p className="text-[10px] text-zinc-400 mt-0.5">
                                            {device.count.toLocaleString('fr-FR')} visites
                                        </p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* ============== Top pages + Top pays ============== */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Top pages */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                        <div className="px-5 py-4 border-b border-zinc-100">
                            <h3 className="font-semibold text-zinc-900">Top pages visitées</h3>
                            <p className="text-xs text-zinc-500">10 plus consultées</p>
                        </div>
                        <div className="divide-y divide-zinc-100">
                            {topPages.length === 0 && (
                                <p className="px-5 py-6 text-sm text-zinc-500 text-center">
                                    Aucune donnée
                                </p>
                            )}
                            {topPages.map((page, i) => {
                                const max = topPages[0]?.visits || 1;
                                const percent = (page.visits / max) * 100;

                                return (
                                    <div key={i} className="px-5 py-3">
                                        <div className="flex items-center justify-between mb-1.5">
                                            <code className="text-xs font-medium text-zinc-700 truncate flex-1 mr-2">
                                                {page.path}
                                            </code>
                                            <span className="text-xs font-semibold text-zinc-600 tabular-nums whitespace-nowrap">
                                                {page.visits.toLocaleString('fr-FR')}
                                            </span>
                                        </div>
                                        <div className="h-1.5 bg-zinc-100 rounded-full overflow-hidden">
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

                    {/* Top pays */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                        <div className="px-5 py-4 border-b border-zinc-100">
                            <h3 className="font-semibold text-zinc-900">Top pays</h3>
                            <p className="text-xs text-zinc-500">Origine des visiteurs</p>
                        </div>
                        <div className="divide-y divide-zinc-100">
                            {topCountries.length === 0 && (
                                <p className="px-5 py-6 text-sm text-zinc-500 text-center">
                                    Aucune donnée (connectez-vous pour activer le tracking pays)
                                </p>
                            )}
                            {topCountries.map((country, i) => {
                                const max = topCountries[0]?.visits || 1;
                                const percent = (country.visits / max) * 100;

                                return (
                                    <div key={i} className="px-5 py-3">
                                        <div className="flex items-center justify-between mb-1.5">
                                            <div className="flex items-center gap-2">
                                                <span className="text-lg">
                                                    {countryCodeToFlag(country.country_code)}
                                                </span>
                                                <span className="text-sm font-medium text-zinc-700">
                                                    {country.country}
                                                </span>
                                            </div>
                                            <span className="text-xs font-semibold text-zinc-600 tabular-nums">
                                                {country.visits.toLocaleString('fr-FR')}
                                            </span>
                                        </div>
                                        <div className="h-1.5 bg-zinc-100 rounded-full overflow-hidden">
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

                {/* ============== Browsers + OS + Visiteurs récents ============== */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {/* Browsers */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                        <h3 className="font-semibold text-zinc-900 mb-1">Navigateurs</h3>
                        <p className="text-xs text-zinc-500 mb-4">Top 5</p>
                        <div className="space-y-2.5">
                            {browsers.length === 0 && (
                                <p className="text-sm text-zinc-400 text-center py-4">
                                    Aucune donnée
                                </p>
                            )}
                            {browsers.map((b, i) => (
                                <div
                                    key={i}
                                    className="flex items-center justify-between text-sm"
                                >
                                    <span className="text-zinc-700">{b.browser}</span>
                                    <span className="font-medium text-zinc-900 tabular-nums">
                                        {b.count.toLocaleString('fr-FR')}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* OS */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                        <h3 className="font-semibold text-zinc-900 mb-1">Systèmes</h3>
                        <p className="text-xs text-zinc-500 mb-4">Top 5</p>
                        <div className="space-y-2.5">
                            {osStats.length === 0 && (
                                <p className="text-sm text-zinc-400 text-center py-4">
                                    Aucune donnée
                                </p>
                            )}
                            {osStats.map((o, i) => (
                                <div
                                    key={i}
                                    className="flex items-center justify-between text-sm"
                                >
                                    <span className="text-zinc-700">{o.os}</span>
                                    <span className="font-medium text-zinc-900 tabular-nums">
                                        {o.count.toLocaleString('fr-FR')}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Quick stats */}
                    <div className="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-2xl shadow-sm p-5">
                        <h3 className="font-semibold mb-1">Aperçu</h3>
                        <p className="text-xs text-emerald-50 mb-4">Performance globale</p>
                        <div className="space-y-3">
                            <div>
                                <p className="text-2xl font-bold tabular-nums">
                                    {stats.total_visits.toLocaleString('fr-FR')}
                                </p>
                                <p className="text-xs text-emerald-50">visites totales</p>
                            </div>
                            <div>
                                <p className="text-2xl font-bold tabular-nums">
                                    {stats.total_unique.toLocaleString('fr-FR')}
                                </p>
                                <p className="text-xs text-emerald-50">visiteurs uniques</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ============== Visiteurs récents ============== */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                    <div className="px-5 py-4 border-b border-zinc-100">
                        <h3 className="font-semibold text-zinc-900">Visiteurs récents</h3>
                        <p className="text-xs text-zinc-500">15 dernières visites</p>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th className="text-left px-5 py-3 font-medium">Visiteur</th>
                                    <th className="text-left px-5 py-3 font-medium">Page</th>
                                    <th className="text-left px-5 py-3 font-medium hidden md:table-cell">
                                        Device
                                    </th>
                                    <th className="text-left px-5 py-3 font-medium hidden lg:table-cell">
                                        Pays
                                    </th>
                                    <th className="text-right px-5 py-3 font-medium">Quand</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100">
                                {recentVisitors.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="text-center py-8 text-zinc-500"
                                        >
                                            Aucune visite enregistrée pour le moment
                                        </td>
                                    </tr>
                                )}
                                {recentVisitors.map((v) => (
                                    <tr key={v.id} className="hover:bg-zinc-50">
                                        <td className="px-5 py-3">
                                            {v.user ? (
                                                <div className="flex items-center gap-2">
                                                    <img
                                                        src={
                                                            v.user.photo ||
                                                            `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                                v.user.name,
                                                            )}&background=10b981&color=fff`
                                                        }
                                                        alt={v.user.name}
                                                        className="w-7 h-7 rounded-full object-cover"
                                                    />
                                                    <span className="font-medium text-zinc-900">
                                                        {v.user.name}
                                                    </span>
                                                </div>
                                            ) : (
                                                <div className="flex items-center gap-2 text-zinc-500">
                                                    <div className="w-7 h-7 rounded-full bg-zinc-200 flex items-center justify-center">
                                                        <Eye className="w-3.5 h-3.5" />
                                                    </div>
                                                    <span className="text-xs">
                                                        Anonyme · {v.ip}
                                                    </span>
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-5 py-3">
                                            <code className="text-xs text-zinc-700">
                                                {v.path}
                                            </code>
                                        </td>
                                        <td className="px-5 py-3 hidden md:table-cell">
                                            <span className="inline-flex items-center gap-1 text-xs text-zinc-600">
                                                {v.device === 'mobile' && (
                                                    <Smartphone className="w-3 h-3" />
                                                )}
                                                {v.device === 'desktop' && (
                                                    <Monitor className="w-3 h-3" />
                                                )}
                                                {v.device === 'tablet' && (
                                                    <Tablet className="w-3 h-3" />
                                                )}
                                                {v.browser}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3 hidden lg:table-cell">
                                            {v.country ? (
                                                <span className="text-xs text-zinc-600">
                                                    {v.country}
                                                </span>
                                            ) : (
                                                <span className="text-xs text-zinc-400">—</span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3 text-right text-xs text-zinc-500 whitespace-nowrap">
                                            {v.created_at_human}
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

/* ============================================================
 *  StatCard
 * ============================================================ */
interface StatCardProps {
    icon: any;
    label: string;
    value: number;
    sublabel: string;
    trend?: number;
    color: 'emerald' | 'blue' | 'orange' | 'rose';
    pulse?: boolean;
}

function StatCard({
    icon: Icon,
    label,
    value,
    sublabel,
    trend,
    color,
    pulse,
}: StatCardProps) {
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
            className="bg-white rounded-2xl p-5 shadow-sm border border-zinc-200 hover:shadow-md transition-shadow"
        >
            <div className="flex items-start justify-between mb-3">
                <div className="relative">
                    <div
                        className={`w-10 h-10 rounded-xl flex items-center justify-center ${colorClasses[color]}`}
                    >
                        <Icon className="w-5 h-5" />
                    </div>
                    {pulse && value > 0 && (
                        <span className="absolute top-0 right-0 flex h-2.5 w-2.5">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75" />
                            <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500" />
                        </span>
                    )}
                </div>
                {trend !== undefined && trend !== 0 && (
                    <span
                        className={`flex items-center gap-0.5 text-xs font-medium px-1.5 py-0.5 rounded ${
                            trend >= 0
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-red-50 text-red-700'
                        }`}
                    >
                        <TrendingUp
                            className={`w-3 h-3 ${trend < 0 ? 'rotate-180' : ''}`}
                        />
                        {Math.abs(trend)}%
                    </span>
                )}
            </div>
            <p className="text-xs font-medium text-zinc-500 mb-1">{label}</p>
            <p className="text-2xl font-bold text-zinc-900 tabular-nums">
                {value.toLocaleString('fr-FR')}
            </p>
            <p className="text-xs text-zinc-500 mt-1">{sublabel}</p>
        </motion.div>
    );
}
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    Users,
    FileText,
    Package,
    Heart,
    TrendingUp,
    TrendingDown,
    ArrowUpRight,
    Eye,
} from 'lucide-react';
import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer,
    BarChart,
    Bar,
    Legend,
    CartesianGrid,
} from 'recharts';
import AdminLayout from '@/components/layouts/AdminLayout';

interface Stats {
    users: {
        total: number;
        today: number;
        this_month: number;
        last_month: number;
    };
    publications: {
        total: number;
        today: number;
        this_month: number;
    };
    products: {
        total: number;
        active: number;
    };
    engagement: {
        likes: number;
        comments: number;
        today_likes: number;
        today_comments: number;
    };
}

interface ChartPoint {
    date: string;
    label: string;
    count?: number;
    likes?: number;
    comments?: number;
}

interface RecentUser {
    id: number;
    name: string;
    email: string;
    photo: string | null;
    country: string | null;
    created_at_human: string;
}

interface RecentPublication {
    id: number;
    text: string;
    user_name: string;
    created_at_human: string;
}

interface Props {
    stats: Stats;
    usersChart: ChartPoint[];
    activityChart: ChartPoint[];
    recentUsers: RecentUser[];
    recentPublications: RecentPublication[];
}

export default function AdminDashboard({
    stats,
    usersChart,
    activityChart,
    recentUsers,
    recentPublications,
}: Props) {
    // Calcul de l'évolution % mois en mois
    const growth =
        stats.users.last_month > 0
            ? Math.round(
                  ((stats.users.this_month - stats.users.last_month) / stats.users.last_month) *
                      100,
              )
            : 100;

    return (
        <AdminLayout title="Tableau de bord">
            <Head title="Admin - Tableau de bord" />

            <div className="space-y-6">
                {/* ============== Welcome ============== */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-700 rounded-2xl p-6 lg:p-8 text-white shadow-lg shadow-emerald-600/20"
                >
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 className="text-2xl lg:text-3xl font-bold mb-1">
                                Bienvenue 👋
                            </h2>
                            <p className="text-emerald-50 text-sm lg:text-base">
                                Voici les chiffres de votre plateforme Paradisia aujourd'hui.
                            </p>
                        </div>
                        <div className="flex gap-3">
                            <Link
                                href="/admin/visitors"
                                className="px-4 py-2 bg-white/15 hover:bg-white/25 backdrop-blur-sm rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"
                            >
                                <Eye className="w-4 h-4" />
                                Visiteurs
                            </Link>
                            <Link
                                href="/"
                                className="px-4 py-2 bg-white text-emerald-700 hover:bg-emerald-50 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"
                            >
                                <ArrowUpRight className="w-4 h-4" />
                                Voir le site
                            </Link>
                        </div>
                    </div>
                </motion.div>

                {/* ============== KPIs ============== */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard
                        icon={Users}
                        label="Utilisateurs"
                        value={stats.users.total}
                        sublabel={`+${stats.users.today} aujourd'hui`}
                        trend={growth}
                        color="emerald"
                    />
                    <StatCard
                        icon={FileText}
                        label="Publications"
                        value={stats.publications.total}
                        sublabel={`+${stats.publications.today} aujourd'hui`}
                        color="blue"
                    />
                    <StatCard
                        icon={Package}
                        label="Produits"
                        value={stats.products.total}
                        sublabel={`${stats.products.active} actifs`}
                        color="orange"
                    />
                    <StatCard
                        icon={Heart}
                        label="Likes"
                        value={stats.engagement.likes}
                        sublabel={`+${stats.engagement.today_likes} aujourd'hui`}
                        color="rose"
                    />
                </div>

                {/* ============== Charts ============== */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Users chart */}
                    <ChartCard
                        title="Nouvelles inscriptions"
                        subtitle="30 derniers jours"
                    >
                        <ResponsiveContainer width="100%" height={250}>
                            <LineChart data={usersChart}>
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    stroke="#e4e4e7"
                                    vertical={false}
                                />
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
                                <Line
                                    type="monotone"
                                    dataKey="count"
                                    stroke="#10b981"
                                    strokeWidth={2.5}
                                    dot={{ fill: '#10b981', r: 3 }}
                                    activeDot={{ r: 5 }}
                                />
                            </LineChart>
                        </ResponsiveContainer>
                    </ChartCard>

                    {/* Activity chart */}
                    <ChartCard title="Activité" subtitle="Likes & commentaires (30j)">
                        <ResponsiveContainer width="100%" height={250}>
                            <BarChart data={activityChart}>
                                <CartesianGrid
                                    strokeDasharray="3 3"
                                    stroke="#e4e4e7"
                                    vertical={false}
                                />
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
                                <Legend
                                    iconType="circle"
                                    wrapperStyle={{ fontSize: 12 }}
                                />
                                <Bar
                                    dataKey="likes"
                                    fill="#f43f5e"
                                    radius={[4, 4, 0, 0]}
                                    name="Likes"
                                />
                                <Bar
                                    dataKey="comments"
                                    fill="#3b82f6"
                                    radius={[4, 4, 0, 0]}
                                    name="Commentaires"
                                />
                            </BarChart>
                        </ResponsiveContainer>
                    </ChartCard>
                </div>

                {/* ============== Listes récentes ============== */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Derniers utilisateurs */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                        <div className="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                            <div>
                                <h3 className="font-semibold text-zinc-900">
                                    Nouveaux utilisateurs
                                </h3>
                                <p className="text-xs text-zinc-500">5 derniers inscrits</p>
                            </div>
                            <Link
                                href="/admin/users"
                                className="text-xs font-medium text-emerald-600 hover:text-emerald-700"
                            >
                                Voir tout →
                            </Link>
                        </div>
                        <div className="divide-y divide-zinc-100">
                            {recentUsers.length === 0 && (
                                <p className="px-5 py-6 text-sm text-zinc-500 text-center">
                                    Aucun utilisateur récent
                                </p>
                            )}
                            {recentUsers.map((user) => (
                                <div
                                    key={user.id}
                                    className="px-5 py-3 flex items-center gap-3 hover:bg-zinc-50 transition-colors"
                                >
                                    <img
                                        src={
                                            user.photo ||
                                            `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                user.name,
                                            )}&background=10b981&color=fff`
                                        }
                                        alt={user.name}
                                        className="w-9 h-9 rounded-full object-cover"
                                    />
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium text-zinc-900 truncate">
                                            {user.name}
                                        </p>
                                        <p className="text-xs text-zinc-500 truncate">
                                            {user.email}
                                        </p>
                                    </div>
                                    <span className="text-xs text-zinc-400 whitespace-nowrap">
                                        {user.created_at_human}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Dernières publications */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                        <div className="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                            <div>
                                <h3 className="font-semibold text-zinc-900">
                                    Dernières publications
                                </h3>
                                <p className="text-xs text-zinc-500">5 plus récentes</p>
                            </div>
                            <Link
                                href="/admin/publications"
                                className="text-xs font-medium text-emerald-600 hover:text-emerald-700"
                            >
                                Voir tout →
                            </Link>
                        </div>
                        <div className="divide-y divide-zinc-100">
                            {recentPublications.length === 0 && (
                                <p className="px-5 py-6 text-sm text-zinc-500 text-center">
                                    Aucune publication récente
                                </p>
                            )}
                            {recentPublications.map((pub) => (
                                <div
                                    key={pub.id}
                                    className="px-5 py-3 hover:bg-zinc-50 transition-colors"
                                >
                                    <div className="flex items-start justify-between gap-3 mb-1">
                                        <p className="text-sm font-medium text-zinc-900">
                                            {pub.user_name}
                                        </p>
                                        <span className="text-xs text-zinc-400 whitespace-nowrap">
                                            {pub.created_at_human}
                                        </span>
                                    </div>
                                    <p className="text-sm text-zinc-600 line-clamp-2">
                                        {pub.text || (
                                            <span className="italic text-zinc-400">
                                                Publication sans texte
                                            </span>
                                        )}
                                    </p>
                                </div>
                            ))}
                        </div>
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
}

function StatCard({ icon: Icon, label, value, sublabel, trend, color }: StatCardProps) {
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
                <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${colorClasses[color]}`}>
                    <Icon className="w-5 h-5" />
                </div>
                {trend !== undefined && (
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
            <p className="text-2xl font-bold text-zinc-900 tabular-nums">
                {value.toLocaleString('fr-FR')}
            </p>
            <p className="text-xs text-zinc-500 mt-1">{sublabel}</p>
        </motion.div>
    );
}

/* ============================================================
 *  ChartCard
 * ============================================================ */
function ChartCard({
    title,
    subtitle,
    children,
}: {
    title: string;
    subtitle: string;
    children: React.ReactNode;
}) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5"
        >
            <div className="mb-4">
                <h3 className="font-semibold text-zinc-900">{title}</h3>
                <p className="text-xs text-zinc-500">{subtitle}</p>
            </div>
            {children}
        </motion.div>
    );
}
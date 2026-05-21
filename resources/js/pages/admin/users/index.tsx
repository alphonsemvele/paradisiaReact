import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    Search,
    Users,
    UserCheck,
    UserX,
    Shield,
    Filter,
    Eye,
    Ban,
    CheckCircle,
    XCircle,
    Trash2,
    ChevronDown,
    Phone,
    Mail,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

interface UserItem {
    id: number;
    ref: string;
    name: string;
    email: string;
    phone: string | null;
    photo: string | null;
    role: string | null;
    country: string | null;
    country_code: string | number | null;
    valid: boolean;
    confirmed: boolean;
    status: string | null;
    is_blocked: boolean;
    publications_count: number;
    comments_count: number;
    last_active_human: string | null;
    created_at_human: string;
    created_at_date: string;
}

interface Stats {
    total: number;
    today: number;
    active: number;
    blocked: number;
    validated: number;
    admins: number;
}

interface PaginatedUsers {
    data: UserItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Filters {
    search: string | null;
    role: string | null;
    status: string | null;
    validated: string | null;
    sort_by: string;
    sort_dir: string;
}

interface Props {
    users: PaginatedUsers;
    stats: Stats;
    filters: Filters;
}

function countryCodeToFlag(code: string | number | null): string {
    if (!code) return '🌍';

    const codeStr = String(code).trim();

    // Si c'est un chiffre (indicatif comme "237") → pas de drapeau possible
    if (/^\d+$/.test(codeStr)) return '📞';

    // Si ce n'est pas un code ISO valide (2 lettres) → globe
    if (codeStr.length !== 2 || !/^[a-zA-Z]{2}$/.test(codeStr)) return '🌍';

    return codeStr
        .toUpperCase()
        .replace(/./g, (c) => String.fromCodePoint(127397 + c.charCodeAt(0)));
}

export default function UsersIndex({ users, stats, filters }: Props) {
    const [searchValue, setSearchValue] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const applyFilter = (key: string, value: string | null) => {
        router.get(
            '/admin/users',
            { ...filters, [key]: value || undefined },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilter('search', searchValue);
    };

    const handleToggleStatus = (userId: number) => {
        router.patch(
            `/admin/users/${userId}/toggle-status`,
            {},
            { preserveScroll: true },
        );
    };

    const handleToggleValidation = (userId: number) => {
        router.patch(
            `/admin/users/${userId}/toggle-validation`,
            {},
            { preserveScroll: true },
        );
    };

    const handleDelete = (userId: number) => {
        router.delete(`/admin/users/${userId}`, {
            onSuccess: () => setConfirmDelete(null),
        });
    };

    const hasActiveFilters =
        filters.search || filters.role || filters.status || filters.validated;

    return (
        <AdminLayout title="Utilisateurs">
            <Head title="Admin - Utilisateurs" />

            <div className="space-y-6">
                {/* ============ KPIs ============ */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard
                        icon={Users}
                        label="Total"
                        value={stats.total}
                        sublabel={`+${stats.today} aujourd'hui`}
                        color="emerald"
                    />
                    <StatCard
                        icon={UserCheck}
                        label="Actifs"
                        value={stats.active}
                        sublabel={`${stats.validated} validés`}
                        color="blue"
                    />
                    <StatCard
                        icon={UserX}
                        label="Bloqués"
                        value={stats.blocked}
                        sublabel="Comptes restreints"
                        color="rose"
                    />
                    <StatCard
                        icon={Shield}
                        label="Administrateurs"
                        value={stats.admins}
                        sublabel="Équipe admin"
                        color="orange"
                    />
                </div>

                {/* ============ Toolbar ============ */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-4">
                    <div className="flex flex-col lg:flex-row gap-3">
                        <form onSubmit={handleSearch} className="flex-1 relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                            <input
                                type="search"
                                value={searchValue}
                                onChange={(e) => setSearchValue(e.target.value)}
                                placeholder="Rechercher par nom, email, téléphone, pays..."
                                className="w-full pl-10 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
                            />
                        </form>

                        <button
                            onClick={() => setShowFilters(!showFilters)}
                            className={`flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                                hasActiveFilters
                                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                    : 'bg-zinc-50 text-zinc-700 border border-zinc-200 hover:bg-zinc-100'
                            }`}
                        >
                            <Filter className="w-4 h-4" />
                            Filtres
                            {hasActiveFilters && (
                                <span className="w-5 h-5 bg-emerald-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center">
                                    {[filters.role, filters.status, filters.validated].filter(
                                        Boolean,
                                    ).length}
                                </span>
                            )}
                            <ChevronDown
                                className={`w-4 h-4 transition-transform ${
                                    showFilters ? 'rotate-180' : ''
                                }`}
                            />
                        </button>
                    </div>

                    {showFilters && (
                        <motion.div
                            initial={{ opacity: 0, height: 0 }}
                            animate={{ opacity: 1, height: 'auto' }}
                            className="mt-4 pt-4 border-t border-zinc-100 grid grid-cols-1 sm:grid-cols-3 gap-3"
                        >
                            <FilterSelect
                                label="Rôle"
                                value={filters.role}
                                options={[
                                    { value: '', label: 'Tous' },
                                    { value: 'user', label: 'Utilisateur' },
                                    { value: 'admin', label: 'Administrateur' },
                                    { value: 'super-admin', label: 'Super Admin' },
                                ]}
                                onChange={(v) => applyFilter('role', v)}
                            />
                            <FilterSelect
                                label="Statut"
                                value={filters.status}
                                options={[
                                    { value: '', label: 'Tous' },
                                    { value: 'active', label: 'Actifs' },
                                    { value: 'blocked', label: 'Bloqués' },
                                ]}
                                onChange={(v) => applyFilter('status', v)}
                            />
                            <FilterSelect
                                label="Validation"
                                value={filters.validated}
                                options={[
                                    { value: '', label: 'Tous' },
                                    { value: 'yes', label: 'Validés' },
                                    { value: 'no', label: 'Non validés' },
                                ]}
                                onChange={(v) => applyFilter('validated', v)}
                            />
                        </motion.div>
                    )}
                </div>

                {/* ============ Table ============ */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th className="text-left px-5 py-3 font-medium">Utilisateur</th>
                                    <th className="text-left px-5 py-3 font-medium hidden md:table-cell">Contact</th>
                                    <th className="text-left px-5 py-3 font-medium hidden lg:table-cell">Activité</th>
                                    <th className="text-left px-5 py-3 font-medium hidden lg:table-cell">Statut</th>
                                    <th className="text-left px-5 py-3 font-medium hidden sm:table-cell">Inscrit</th>
                                    <th className="text-right px-5 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100">
                                {users.data.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="px-5 py-12 text-center text-zinc-500"
                                        >
                                            <Users className="w-10 h-10 mx-auto mb-2 text-zinc-300" />
                                            Aucun utilisateur trouvé
                                        </td>
                                    </tr>
                                )}
                                {users.data.map((user) => (
                                    <tr key={user.id} className="hover:bg-zinc-50">
                                        <td className="px-5 py-3">
                                            <Link
                                                href={`/admin/users/${user.id}`}
                                                className="flex items-center gap-3 group"
                                            >
                                                <img
                                                    src={
                                                        user.photo ||
                                                        `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                            user.name,
                                                        )}&background=10b981&color=fff`
                                                    }
                                                    alt={user.name}
                                                    className="w-9 h-9 rounded-full object-cover flex-shrink-0"
                                                />
                                                <div className="min-w-0">
                                                    <p className="font-medium text-zinc-900 group-hover:text-emerald-600 transition-colors truncate">
                                                        {user.name}
                                                    </p>
                                                    <p className="text-xs text-zinc-500 truncate flex items-center gap-1">
                                                        {user.country && (
                                                            <span>
                                                                {countryCodeToFlag(user.country_code)}
                                                            </span>
                                                        )}
                                                        {user.role && user.role !== 'user' && (
                                                            <span className="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded uppercase">
                                                                {user.role}
                                                            </span>
                                                        )}
                                                    </p>
                                                </div>
                                            </Link>
                                        </td>

                                        <td className="px-5 py-3 hidden md:table-cell">
                                            <div className="space-y-0.5">
                                                <p className="text-xs text-zinc-700 flex items-center gap-1 truncate max-w-[200px]">
                                                    <Mail className="w-3 h-3 text-zinc-400" />
                                                    {user.email}
                                                </p>
                                                {user.phone && (
                                                    <p className="text-xs text-zinc-500 flex items-center gap-1">
                                                        <Phone className="w-3 h-3 text-zinc-400" />
                                                        {user.phone}
                                                    </p>
                                                )}
                                            </div>
                                        </td>

                                        <td className="px-5 py-3 hidden lg:table-cell">
                                            <div className="text-xs text-zinc-600">
                                                <p>
                                                    <span className="font-semibold">
                                                        {user.publications_count}
                                                    </span>{' '}
                                                    pubs
                                                </p>
                                                <p>
                                                    <span className="font-semibold">
                                                        {user.comments_count}
                                                    </span>{' '}
                                                    coms
                                                </p>
                                            </div>
                                        </td>

                                        <td className="px-5 py-3 hidden lg:table-cell">
                                            <div className="flex flex-col gap-1">
                                                {user.is_blocked ? (
                                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-700 text-[10px] font-semibold rounded uppercase w-fit">
                                                        <Ban className="w-3 h-3" />
                                                        Bloqué
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-semibold rounded uppercase w-fit">
                                                        <CheckCircle className="w-3 h-3" />
                                                        Actif
                                                    </span>
                                                )}
                                                {user.valid && (
                                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-semibold rounded uppercase w-fit">
                                                        ✓ Validé
                                                    </span>
                                                )}
                                            </div>
                                        </td>

                                        <td className="px-5 py-3 hidden sm:table-cell text-xs text-zinc-500">
                                            <p>{user.created_at_date}</p>
                                            <p className="text-zinc-400">
                                                {user.created_at_human}
                                            </p>
                                        </td>

                                        <td className="px-5 py-3">
                                            <div className="flex items-center justify-end gap-1">
                                                <Link
                                                    href={`/admin/users/${user.id}`}
                                                    className="p-1.5 hover:bg-zinc-100 rounded-lg text-zinc-600 hover:text-emerald-600 transition-colors"
                                                    title="Voir le détail"
                                                >
                                                    <Eye className="w-4 h-4" />
                                                </Link>
                                                <button
                                                    onClick={() => handleToggleValidation(user.id)}
                                                    className="p-1.5 hover:bg-zinc-100 rounded-lg text-zinc-600 hover:text-blue-600 transition-colors"
                                                    title={
                                                        user.valid
                                                            ? 'Retirer la validation'
                                                            : 'Valider le compte'
                                                    }
                                                >
                                                    {user.valid ? (
                                                        <XCircle className="w-4 h-4" />
                                                    ) : (
                                                        <CheckCircle className="w-4 h-4" />
                                                    )}
                                                </button>
                                                <button
                                                    onClick={() => handleToggleStatus(user.id)}
                                                    className="p-1.5 hover:bg-zinc-100 rounded-lg text-zinc-600 hover:text-orange-600 transition-colors"
                                                    title={user.is_blocked ? 'Débloquer' : 'Bloquer'}
                                                >
                                                    <Ban className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={() => setConfirmDelete(user.id)}
                                                    className="p-1.5 hover:bg-red-50 rounded-lg text-zinc-600 hover:text-red-600 transition-colors"
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

                    {users.last_page > 1 && (
                        <div className="px-5 py-3 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-2">
                            <p className="text-xs text-zinc-500">
                                Page <strong>{users.current_page}</strong> sur{' '}
                                <strong>{users.last_page}</strong> ·{' '}
                                <strong>{users.total}</strong> utilisateurs
                            </p>
                            <div className="flex gap-1 flex-wrap">
                                {users.links.map((link, i) => {
                                    if (!link.url) {
                                        return (
                                            <span
                                                key={i}
                                                className="px-3 py-1.5 text-xs text-zinc-400"
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        );
                                    }
                                    return (
                                        <Link
                                            key={i}
                                            href={link.url}
                                            preserveScroll
                                            className={`px-3 py-1.5 text-xs rounded-lg transition-colors ${
                                                link.active
                                                    ? 'bg-emerald-500 text-white font-semibold'
                                                    : 'text-zinc-600 hover:bg-zinc-100'
                                            }`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* ============ Modal de confirmation ============ */}
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
                            Supprimer cet utilisateur ?
                        </h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">
                            Cette action est irréversible. Toutes les données associées seront
                            perdues.
                        </p>
                        <div className="flex gap-3">
                            <button
                                onClick={() => setConfirmDelete(null)}
                                className="flex-1 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg transition-colors"
                            >
                                Annuler
                            </button>
                            <button
                                onClick={() => handleDelete(confirmDelete)}
                                className="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors"
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

/* ===================== StatCard ===================== */
function StatCard({
    icon: Icon,
    label,
    value,
    sublabel,
    color,
}: {
    icon: any;
    label: string;
    value: number;
    sublabel: string;
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
            <div
                className={`w-10 h-10 rounded-xl flex items-center justify-center mb-3 ${colorClasses[color]}`}
            >
                <Icon className="w-5 h-5" />
            </div>
            <p className="text-xs font-medium text-zinc-500 mb-1">{label}</p>
            <p className="text-2xl font-bold text-zinc-900 tabular-nums">
                {value.toLocaleString('fr-FR')}
            </p>
            <p className="text-xs text-zinc-500 mt-1">{sublabel}</p>
        </motion.div>
    );
}

/* ===================== FilterSelect ===================== */
function FilterSelect({
    label,
    value,
    options,
    onChange,
}: {
    label: string;
    value: string | null;
    options: Array<{ value: string; label: string }>;
    onChange: (v: string | null) => void;
}) {
    return (
        <div>
            <label className="block text-xs font-medium text-zinc-500 mb-1.5">{label}</label>
            <select
                value={value || ''}
                onChange={(e) => onChange(e.target.value || null)}
                className="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                {options.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                        {opt.label}
                    </option>
                ))}
            </select>
        </div>
    );
}
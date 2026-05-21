import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    Search,
    FileText,
    Filter,
    ChevronDown,
    Heart,
    MessageSquare,
    Eye,
    Trash2,
    Image as ImageIcon,
    Video,
    FileType,
    Calendar,
    TrendingUp,
    Clock,
    XCircle,
    CheckCircle,
    AlertCircle,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

const STATUS_CONFIG: any = {
    Success: { label: 'Publié', color: 'emerald', icon: CheckCircle, bg: 'bg-emerald-100', text: 'text-emerald-700' },
    pending: { label: 'En attente', color: 'orange', icon: Clock, bg: 'bg-orange-100', text: 'text-orange-700' },
    waiting: { label: 'En cours', color: 'blue', icon: AlertCircle, bg: 'bg-blue-100', text: 'text-blue-700' },
    failed: { label: 'Échoué', color: 'red', icon: XCircle, bg: 'bg-red-100', text: 'text-red-700' },
    deleted: { label: 'Supprimé', color: 'zinc', icon: Trash2, bg: 'bg-zinc-200', text: 'text-zinc-700' },
};

export default function PublicationsIndex({ publications, stats, filters, topAuthors }: any) {
    const [search, setSearch] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    const applyFilter = (key: string, value: string | null) => {
        router.get(
            '/admin/publications',
            { ...filters, [key]: value || undefined },
            { preserveState: true },
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilter('search', search);
    };

    const handleChangeStatus = (id: number, status: string) => {
        router.patch(
            `/admin/publications/${id}/status`,
            { status },
            { preserveScroll: true },
        );
    };

    const handleDelete = (id: number) => {
        router.delete(`/admin/publications/${id}`, {
            onSuccess: () => setConfirmDelete(null),
        });
    };

    const hasActiveFilters = filters.status || filters.user || filters.media || filters.period;

    return (
        <AdminLayout title="Publications">
            <Head title="Admin - Publications" />

            <div className="space-y-6">
                {/* ============ KPIs par statut ============ */}
                <div className="grid grid-cols-2 lg:grid-cols-6 gap-4">
                    <StatCard
                        icon={FileText}
                        label="Total"
                        value={stats.total}
                        sublabel={`+${stats.today} aujourd'hui`}
                        color="zinc"
                    />
                    <StatusStatCard
                        status="Success"
                        value={stats.success}
                        onClick={() => applyFilter('status', 'Success')}
                        active={filters.status === 'Success'}
                    />
                    <StatusStatCard
                        status="pending"
                        value={stats.pending}
                        onClick={() => applyFilter('status', 'pending')}
                        active={filters.status === 'pending'}
                    />
                    <StatusStatCard
                        status="waiting"
                        value={stats.waiting}
                        onClick={() => applyFilter('status', 'waiting')}
                        active={filters.status === 'waiting'}
                    />
                    <StatusStatCard
                        status="failed"
                        value={stats.failed}
                        onClick={() => applyFilter('status', 'failed')}
                        active={filters.status === 'failed'}
                    />
                    <StatusStatCard
                        status="deleted"
                        value={stats.deleted}
                        onClick={() => applyFilter('status', 'deleted')}
                        active={filters.status === 'deleted'}
                    />
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    {/* ============ Liste ============ */}
                    <div className="lg:col-span-3 space-y-4">
                        {/* Toolbar */}
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-4">
                            <div className="flex flex-col sm:flex-row gap-3">
                                <form onSubmit={handleSearch} className="flex-1 relative">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                                    <input
                                        type="search"
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        placeholder="Rechercher dans le texte..."
                                        className="w-full pl-10 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
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
                                            {[filters.status, filters.user, filters.media, filters.period].filter(Boolean).length}
                                        </span>
                                    )}
                                    <ChevronDown
                                        className={`w-4 h-4 transition-transform ${showFilters ? 'rotate-180' : ''}`}
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
                                        label="Statut"
                                        value={filters.status}
                                        options={[
                                            { value: '', label: 'Tous' },
                                            { value: 'Success', label: 'Publié' },
                                            { value: 'pending', label: 'En attente' },
                                            { value: 'waiting', label: 'En cours' },
                                            { value: 'failed', label: 'Échoué' },
                                            { value: 'deleted', label: 'Supprimé' },
                                        ]}
                                        onChange={(v) => applyFilter('status', v)}
                                    />
                                    <FilterSelect
                                        label="Type"
                                        value={filters.media}
                                        options={[
                                            { value: '', label: 'Tous' },
                                            { value: 'text', label: 'Texte seul' },
                                            { value: 'image', label: 'Avec image' },
                                            { value: 'video', label: 'Avec vidéo' },
                                        ]}
                                        onChange={(v) => applyFilter('media', v)}
                                    />
                                    <FilterSelect
                                        label="Période"
                                        value={filters.period}
                                        options={[
                                            { value: '', label: 'Toutes' },
                                            { value: 'today', label: "Aujourd'hui" },
                                            { value: 'week', label: 'Cette semaine' },
                                            { value: 'month', label: 'Ce mois' },
                                        ]}
                                        onChange={(v) => applyFilter('period', v)}
                                    />
                                </motion.div>
                            )}
                        </div>

                        {/* Grille publications */}
                        {publications.data.length === 0 ? (
                            <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-12 text-center">
                                <FileText className="w-12 h-12 mx-auto mb-3 text-zinc-300" />
                                <p className="text-zinc-500">Aucune publication trouvée</p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {publications.data.map((pub: any) => (
                                    <PublicationCard
                                        key={pub.id}
                                        pub={pub}
                                        onChangeStatus={(status: string) => handleChangeStatus(pub.id, status)}
                                        onDelete={() => setConfirmDelete(pub.id)}
                                    />
                                ))}
                            </div>
                        )}

                        {/* Pagination */}
                        {publications.last_page > 1 && (
                            <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 px-5 py-3 flex flex-col sm:flex-row items-center justify-between gap-2">
                                <p className="text-xs text-zinc-500">
                                    Page <strong>{publications.current_page}</strong> sur{' '}
                                    <strong>{publications.last_page}</strong> ·{' '}
                                    <strong>{publications.total}</strong> publications
                                </p>
                                <div className="flex gap-1 flex-wrap">
                                    {publications.links.map((link: any, i: number) =>
                                        link.url ? (
                                            <Link
                                                key={i}
                                                href={link.url}
                                                preserveScroll
                                                className={`px-3 py-1.5 text-xs rounded-lg ${
                                                    link.active
                                                        ? 'bg-emerald-500 text-white'
                                                        : 'text-zinc-600 hover:bg-zinc-100'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ) : (
                                            <span
                                                key={i}
                                                className="px-3 py-1.5 text-xs text-zinc-400"
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ),
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Sidebar : Top auteurs */}
                    <div className="lg:col-span-1">
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5 sticky top-20">
                            <div className="flex items-center gap-2 mb-4">
                                <TrendingUp className="w-4 h-4 text-emerald-600" />
                                <h3 className="font-semibold text-zinc-900">Top créateurs</h3>
                            </div>
                            <div className="space-y-3">
                                {topAuthors.length === 0 && (
                                    <p className="text-sm text-zinc-400 text-center py-4">
                                        Aucun auteur
                                    </p>
                                )}
                                {topAuthors.map((author: any, i: number) => (
                                    <Link
                                        key={author.id}
                                        href={`/admin/users/${author.id}`}
                                        className="flex items-center gap-3 group"
                                    >
                                        <span className="text-xs font-bold text-zinc-400 w-4">
                                            #{i + 1}
                                        </span>
                                        <img
                                            src={
                                                author.photo ||
                                                `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                    author.name,
                                                )}&background=10b981&color=fff`
                                            }
                                            alt={author.name}
                                            className="w-8 h-8 rounded-full object-cover"
                                        />
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-medium text-zinc-900 group-hover:text-emerald-600 truncate">
                                                {author.name}
                                            </p>
                                            <p className="text-xs text-zinc-500">
                                                {author.count} publication{author.count > 1 ? 's' : ''}
                                            </p>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                        <h3 className="text-lg font-semibold text-zinc-900 text-center mb-2">
                            Supprimer définitivement cette publication ?
                        </h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">
                            Cette action est irréversible (médias, commentaires, likes et partages
                            inclus).
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

/* ============ Publication Card ============ */
function PublicationCard({
    pub,
    onChangeStatus,
    onDelete,
}: {
    pub: any;
    onChangeStatus: (status: string) => void;
    onDelete: () => void;
}) {
    const [statusOpen, setStatusOpen] = useState(false);
    const statusConfig = STATUS_CONFIG[pub.status] || STATUS_CONFIG.pending;
    const StatusIcon = statusConfig.icon;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden hover:shadow-md transition-shadow"
        >
            {/* Image preview */}
            {pub.has_image && pub.img_1 && (
                <Link href={`/admin/publications/${pub.id}`}>
                    <div className="aspect-video bg-zinc-100 relative">
                        <img
                            src={pub.img_1}
                            alt=""
                            className="w-full h-full object-cover"
                            loading="lazy"
                        />
                        {pub.has_video && (
                            <div className="absolute inset-0 flex items-center justify-center bg-black/40">
                                <div className="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center">
                                    <Video className="w-6 h-6 text-zinc-900" />
                                </div>
                            </div>
                        )}
                    </div>
                </Link>
            )}

            {!pub.has_image && pub.has_video && (
                <div className="aspect-video bg-zinc-900 flex items-center justify-center">
                    <Video className="w-12 h-12 text-white/60" />
                </div>
            )}

            {!pub.has_image && !pub.has_video && (
                <div className="aspect-video bg-gradient-to-br from-zinc-50 to-zinc-100 flex items-center justify-center">
                    <FileType className="w-12 h-12 text-zinc-300" />
                </div>
            )}

            {/* Content */}
            <div className="p-4">
                {/* User + Status */}
                <div className="flex items-center justify-between gap-2 mb-3">
                    {pub.user ? (
                        <div className="flex items-center gap-2 flex-1 min-w-0">
                            <img
                                src={
                                    pub.user.photo ||
                                    `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                        pub.user.name,
                                    )}&background=10b981&color=fff`
                                }
                                alt=""
                                className="w-7 h-7 rounded-full object-cover"
                            />
                            <div className="flex-1 min-w-0">
                                <p className="text-xs font-medium text-zinc-900 truncate">
                                    {pub.user.name}
                                </p>
                                <p className="text-[10px] text-zinc-500">{pub.created_at_human}</p>
                            </div>
                        </div>
                    ) : (
                        <p className="text-xs text-zinc-400 italic flex-1">Auteur supprimé</p>
                    )}

                    {/* Badge statut cliquable */}
                    <div className="relative">
                        <button
                            onClick={() => setStatusOpen(!statusOpen)}
                            className={`inline-flex items-center gap-1 px-2 py-1 ${statusConfig.bg} ${statusConfig.text} text-[10px] font-semibold rounded uppercase hover:opacity-80 transition-opacity`}
                        >
                            <StatusIcon className="w-3 h-3" />
                            {statusConfig.label}
                            <ChevronDown className="w-3 h-3" />
                        </button>

                        {statusOpen && (
                            <>
                                <div
                                    className="fixed inset-0 z-40"
                                    onClick={() => setStatusOpen(false)}
                                />
                                <div className="absolute right-0 top-full mt-1 w-36 bg-white rounded-lg shadow-lg border border-zinc-200 overflow-hidden z-50">
                                    {Object.entries(STATUS_CONFIG).map(([key, cfg]: any) => {
                                        const Icon = cfg.icon;
                                        return (
                                            <button
                                                key={key}
                                                onClick={() => {
                                                    onChangeStatus(key);
                                                    setStatusOpen(false);
                                                }}
                                                className={`w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-zinc-50 ${
                                                    pub.status === key
                                                        ? 'bg-zinc-50 font-semibold'
                                                        : ''
                                                }`}
                                            >
                                                <Icon className={`w-3.5 h-3.5 ${cfg.text}`} />
                                                {cfg.label}
                                            </button>
                                        );
                                    })}
                                </div>
                            </>
                        )}
                    </div>
                </div>

                {/* Text */}
                <Link href={`/admin/publications/${pub.id}`}>
                    <p className="text-sm text-zinc-700 line-clamp-3 mb-3 min-h-[3em] hover:text-emerald-600 transition-colors">
                        {pub.text || (
                            <span className="italic text-zinc-400">Publication sans texte</span>
                        )}
                        {pub.text_full_length > 150 && (
                            <span className="text-emerald-600"> ...</span>
                        )}
                    </p>
                </Link>

                {/* Stats */}
                <div className="flex items-center gap-4 text-xs text-zinc-500 mb-3">
                    <span className="flex items-center gap-1">
                        <Heart className="w-3.5 h-3.5" />
                        {pub.likes_count}
                    </span>
                    <span className="flex items-center gap-1">
                        <MessageSquare className="w-3.5 h-3.5" />
                        {pub.comments_count}
                    </span>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-1 pt-3 border-t border-zinc-100">
                    <Link
                        href={`/admin/publications/${pub.id}`}
                        className="flex-1 flex items-center justify-center gap-1 py-1.5 bg-zinc-50 hover:bg-zinc-100 rounded text-xs font-medium text-zinc-700"
                    >
                        <Eye className="w-3 h-3" />
                        Voir détail
                    </Link>
                    <button
                        onClick={onDelete}
                        className="p-1.5 hover:bg-red-50 rounded text-zinc-600 hover:text-red-600"
                        title="Supprimer définitivement"
                    >
                        <Trash2 className="w-4 h-4" />
                    </button>
                </div>
            </div>
        </motion.div>
    );
}

/* ============ Status StatCard (cliquable pour filtrer) ============ */
function StatusStatCard({
    status,
    value,
    onClick,
    active,
}: {
    status: string;
    value: number;
    onClick: () => void;
    active: boolean;
}) {
    const cfg = STATUS_CONFIG[status];
    const Icon = cfg.icon;

    return (
        <motion.button
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            onClick={onClick}
            className={`bg-white rounded-2xl p-5 shadow-sm border-2 text-left transition-all hover:shadow-md ${
                active ? 'border-emerald-500 ring-2 ring-emerald-100' : 'border-zinc-200'
            }`}
        >
            <div className={`w-10 h-10 rounded-xl ${cfg.bg} flex items-center justify-center mb-3`}>
                <Icon className={`w-5 h-5 ${cfg.text}`} />
            </div>
            <p className="text-xs font-medium text-zinc-500 mb-1">{cfg.label}</p>
            <p className="text-2xl font-bold text-zinc-900 tabular-nums">
                {value.toLocaleString('fr-FR')}
            </p>
        </motion.button>
    );
}

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
    color: string;
}) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-white rounded-2xl p-5 shadow-sm border border-zinc-200"
        >
            <div className="w-10 h-10 rounded-xl bg-zinc-100 text-zinc-700 flex items-center justify-center mb-3">
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
import { Head, Link, router, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    ArrowLeft,
    Heart,
    MessageSquare,
    Share2,
    Trash2,
    Edit3,
    Save,
    Calendar,
    Hash,
    User as UserIcon,
    Clock,
    XCircle,
    CheckCircle,
    AlertCircle,
    ChevronDown,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';
import PublicationInsights from '@/components/admin/PublicationInsights';

const STATUS_CONFIG: any = {
    Success: {
        label: 'Publié',
        icon: CheckCircle,
        bg: 'bg-emerald-100',
        text: 'text-emerald-700',
    },
    pending: {
        label: 'En attente',
        icon: Clock,
        bg: 'bg-orange-100',
        text: 'text-orange-700',
    },
    waiting: {
        label: 'En cours',
        icon: AlertCircle,
        bg: 'bg-blue-100',
        text: 'text-blue-700',
    },
    failed: {
        label: 'Échoué',
        icon: XCircle,
        bg: 'bg-red-100',
        text: 'text-red-700',
    },
    deleted: {
        label: 'Supprimé',
        icon: Trash2,
        bg: 'bg-zinc-200',
        text: 'text-zinc-700',
    },
};

export default function PublicationShow({ publication, likers, stats, viewers }: any) {
    const [editMode, setEditMode] = useState(false);
    const [statusMenuOpen, setStatusMenuOpen] = useState(false);
    const [confirmDelete, setConfirmDelete] = useState(false);
    const [confirmDeleteComment, setConfirmDeleteComment] = useState<number | null>(null);

    const { data, setData, patch, processing } = useForm({
        text: publication.text || '',
    });

    const submitEdit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(`/admin/publications/${publication.id}`, {
            onSuccess: () => setEditMode(false),
            preserveScroll: true,
        });
    };

    const handleChangeStatus = (status: string) => {
        router.patch(
            `/admin/publications/${publication.id}/status`,
            { status },
            {
                preserveScroll: true,
                onSuccess: () => setStatusMenuOpen(false),
            },
        );
    };

    const handleDelete = () => {
        router.delete(`/admin/publications/${publication.id}`);
    };

    const handleDeleteComment = (commentId: number) => {
        router.delete(`/admin/publications/${publication.id}/comments/${commentId}`, {
            preserveScroll: true,
            onSuccess: () => setConfirmDeleteComment(null),
        });
    };

    const currentStatus = STATUS_CONFIG[publication.status] || STATUS_CONFIG.pending;
    const CurrentStatusIcon = currentStatus.icon;

    return (
        <AdminLayout title="Publication">
            <Head title={`Admin - Publication #${publication.id}`} />

            <div className="space-y-6">
                {/* Actions */}
                <div className="flex items-center justify-between flex-wrap gap-3">
                    <Link
                        href="/admin/publications"
                        className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Retour aux publications
                    </Link>

                    <div className="flex gap-2 flex-wrap">
                        {!editMode && (
                            <button
                                onClick={() => setEditMode(true)}
                                className="flex items-center gap-1.5 px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-700 hover:bg-zinc-50"
                            >
                                <Edit3 className="w-4 h-4" />
                                Modifier
                            </button>
                        )}

                        {/* Dropdown statut */}
                        <div className="relative">
                            <button
                                onClick={() => setStatusMenuOpen(!statusMenuOpen)}
                                className={`flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition-colors ${currentStatus.bg} ${currentStatus.text}`}
                            >
                                <CurrentStatusIcon className="w-4 h-4" />
                                Statut: {currentStatus.label}
                                <ChevronDown className="w-4 h-4" />
                            </button>

                            {statusMenuOpen && (
                                <>
                                    <div
                                        className="fixed inset-0 z-40"
                                        onClick={() => setStatusMenuOpen(false)}
                                    />
                                    <div className="absolute right-0 top-full mt-2 w-44 bg-white rounded-lg shadow-lg border border-zinc-200 overflow-hidden z-50">
                                        {Object.entries(STATUS_CONFIG).map(([key, cfg]: any) => {
                                            const Icon = cfg.icon;
                                            return (
                                                <button
                                                    key={key}
                                                    onClick={() => handleChangeStatus(key)}
                                                    className={`w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 ${
                                                        publication.status === key
                                                            ? 'bg-zinc-50 font-semibold'
                                                            : ''
                                                    }`}
                                                >
                                                    <Icon className={`w-4 h-4 ${cfg.text}`} />
                                                    {cfg.label}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </>
                            )}
                        </div>

                        <button
                            onClick={() => setConfirmDelete(true)}
                            className="flex items-center gap-1.5 px-3 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600"
                        >
                            <Trash2 className="w-4 h-4" />
                            Supprimer
                        </button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* ============ Colonne principale ============ */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Publication */}
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                            {/* Header user */}
                            <div className="p-5 flex items-center justify-between border-b border-zinc-100">
                                {publication.user ? (
                                    <Link
                                        href={`/admin/users/${publication.user.id}`}
                                        className="flex items-center gap-3 group"
                                    >
                                        <img
                                            src={
                                                publication.user.photo ||
                                                `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                    publication.user.name,
                                                )}&background=10b981&color=fff`
                                            }
                                            alt={publication.user.name}
                                            className="w-11 h-11 rounded-full object-cover"
                                        />
                                        <div>
                                            <p className="font-semibold text-zinc-900 group-hover:text-emerald-600">
                                                {publication.user.name}
                                            </p>
                                            <p className="text-xs text-zinc-500 flex items-center gap-1.5">
                                                <Calendar className="w-3 h-3" />
                                                {publication.created_at}
                                            </p>
                                        </div>
                                    </Link>
                                ) : (
                                    <p className="text-zinc-500 italic">Auteur supprimé</p>
                                )}

                                {publication.status && publication.status !== 'Success' && (
                                    <span
                                        className={`inline-flex items-center gap-1 px-2 py-1 ${currentStatus.bg} ${currentStatus.text} text-xs font-semibold rounded uppercase`}
                                    >
                                        <CurrentStatusIcon className="w-3 h-3" />
                                        {currentStatus.label}
                                    </span>
                                )}
                            </div>

                            {/* Text */}
                            <div className="p-5">
                                {editMode ? (
                                    <form onSubmit={submitEdit} className="space-y-3">
                                        <textarea
                                            value={data.text}
                                            onChange={(e) => setData('text', e.target.value)}
                                            rows={6}
                                            className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                                        />
                                        <div className="flex gap-2 justify-end">
                                            <button
                                                type="button"
                                                onClick={() => setEditMode(false)}
                                                className="px-3 py-1.5 bg-zinc-100 text-zinc-700 text-sm rounded-lg"
                                            >
                                                Annuler
                                            </button>
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 text-white text-sm rounded-lg disabled:bg-emerald-300"
                                            >
                                                <Save className="w-3.5 h-3.5" />
                                                Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                ) : (
                                    <p className="text-zinc-800 whitespace-pre-wrap">
                                        {publication.text || (
                                            <span className="italic text-zinc-400">
                                                Publication sans texte
                                            </span>
                                        )}
                                    </p>
                                )}
                            </div>

                            {/* Médias */}
                            {publication.images && publication.images.length > 0 && (
                                <div
                                    className={`grid gap-1 ${
                                        publication.images.length === 1
                                            ? 'grid-cols-1'
                                            : 'grid-cols-2'
                                    }`}
                                >
                                    {publication.images.map((img: string, i: number) => (
                                        <button
                                            key={i}
                                            type="button"
                                            onClick={() => window.open(img, '_blank')}
                                            className="aspect-square bg-zinc-100 hover:opacity-90 transition-opacity overflow-hidden"
                                        >
                                            <img
                                                src={img}
                                                alt=""
                                                className="w-full h-full object-cover"
                                            />
                                        </button>
                                    ))}
                                </div>
                            )}

                            {publication.video && (
                                <video
                                    src={publication.video}
                                    controls
                                    className="w-full bg-black"
                                />
                            )}

                            {/* Stats */}
                            <div className="px-5 py-3 border-t border-zinc-100 flex items-center gap-6 text-sm">
                                <span className="flex items-center gap-1.5 text-zinc-600">
                                    <Heart className="w-4 h-4 text-rose-500" />
                                    <strong>{publication.likes_count}</strong> likes
                                </span>
                                <span className="flex items-center gap-1.5 text-zinc-600">
                                    <MessageSquare className="w-4 h-4 text-blue-500" />
                                    <strong>{publication.comments_count}</strong> commentaires
                                </span>
                                <span className="flex items-center gap-1.5 text-zinc-600">
                                    <Share2 className="w-4 h-4 text-orange-500" />
                                    <strong>{publication.shares_count}</strong> partages
                                </span>
                            </div>
                        </div>

                        {/* Commentaires */}
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                            <div className="px-5 py-4 border-b border-zinc-100">
                                <h3 className="font-semibold text-zinc-900">
                                    Commentaires ({publication.comments?.length || 0})
                                </h3>
                            </div>

                            <div className="divide-y divide-zinc-100">
                                {(!publication.comments ||
                                    publication.comments.length === 0) && (
                                    <p className="px-5 py-8 text-sm text-zinc-500 text-center">
                                        Aucun commentaire
                                    </p>
                                )}
                                {publication.comments?.map((comment: any) => (
                                    <div key={comment.id} className="px-5 py-4">
                                        <CommentBlock
                                            comment={comment}
                                            onDelete={() =>
                                                setConfirmDeleteComment(comment.id)
                                            }
                                        />

                                        {comment.replies && comment.replies.length > 0 && (
                                            <div className="ml-11 mt-3 space-y-3 border-l-2 border-zinc-100 pl-4">
                                                {comment.replies.map((reply: any) => (
                                                    <CommentBlock
                                                        key={reply.id}
                                                        comment={reply}
                                                        onDelete={() =>
                                                            setConfirmDeleteComment(reply.id)
                                                        }
                                                        isReply
                                                    />
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* ============ Sidebar ============ */}
                    <div className="lg:col-span-1 space-y-4">
                        {/* Infos */}
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                            <h3 className="font-semibold text-zinc-900 mb-4">Informations</h3>
                            <div className="space-y-3 text-sm">
                                <InfoRow
                                    icon={Hash}
                                    label="Référence"
                                    value={publication.ref || `#${publication.id}`}
                                />
                                <InfoRow
                                    icon={Calendar}
                                    label="Créée"
                                    value={publication.created_at_human}
                                />
                                {publication.updated_at_human !==
                                    publication.created_at_human && (
                                    <InfoRow
                                        icon={Calendar}
                                        label="Modifiée"
                                        value={publication.updated_at_human}
                                    />
                                )}
                                {publication.user && (
                                    <InfoRow
                                        icon={UserIcon}
                                        label="Email auteur"
                                        value={publication.user.email}
                                    />
                                )}
                                <div>
                                    <p className="text-xs text-zinc-500 mb-1 flex items-center gap-1.5">
                                        <CurrentStatusIcon className="w-3 h-3" />
                                        Statut actuel
                                    </p>
                                    <span
                                        className={`inline-flex items-center gap-1 px-2 py-1 ${currentStatus.bg} ${currentStatus.text} text-xs font-semibold rounded uppercase`}
                                    >
                                        <CurrentStatusIcon className="w-3 h-3" />
                                        {currentStatus.label}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Statistiques et audience */}
                        {stats && (
                            <PublicationInsights stats={stats} viewers={viewers ?? []} />
                        )}

                        {/* Likers */}
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                            <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                                <Heart className="w-4 h-4 text-rose-500" />
                                <h3 className="font-semibold text-zinc-900">
                                    Derniers likes ({likers?.length || 0})
                                </h3>
                            </div>
                            <div className="divide-y divide-zinc-100 max-h-96 overflow-y-auto">
                                {(!likers || likers.length === 0) && (
                                    <p className="px-5 py-6 text-sm text-zinc-500 text-center">
                                        Aucun like
                                    </p>
                                )}
                                {likers?.map((liker: any) => (
                                    <div
                                        key={liker.id}
                                        className="px-5 py-2.5 flex items-center gap-3"
                                    >
                                        {liker.user ? (
                                            <>
                                                <img
                                                    src={
                                                        liker.user.photo ||
                                                        `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                            liker.user.name,
                                                        )}&background=10b981&color=fff`
                                                    }
                                                    alt={liker.user.name}
                                                    className="w-7 h-7 rounded-full object-cover"
                                                />
                                                <div className="flex-1 min-w-0">
                                                    <Link
                                                        href={`/admin/users/${liker.user.id}`}
                                                        className="text-sm font-medium text-zinc-900 hover:text-emerald-600 truncate block"
                                                    >
                                                        {liker.user.name}
                                                    </Link>
                                                    <p className="text-[10px] text-zinc-500">
                                                        {liker.created_at_human}
                                                    </p>
                                                </div>
                                            </>
                                        ) : (
                                            <p className="text-xs text-zinc-400 italic">
                                                Utilisateur supprimé
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal Delete Publication */}
            {confirmDelete && (
                <Modal onClose={() => setConfirmDelete(false)}>
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
                            onClick={() => setConfirmDelete(false)}
                            className="flex-1 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg"
                        >
                            Annuler
                        </button>
                        <button
                            onClick={handleDelete}
                            className="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg"
                        >
                            Supprimer
                        </button>
                    </div>
                </Modal>
            )}

            {/* Modal Delete Comment */}
            {confirmDeleteComment !== null && (
                <Modal onClose={() => setConfirmDeleteComment(null)}>
                    <div className="w-12 h-12 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                        <Trash2 className="w-6 h-6 text-red-600" />
                    </div>
                    <h3 className="text-lg font-semibold text-zinc-900 text-center mb-2">
                        Supprimer ce commentaire ?
                    </h3>
                    <p className="text-sm text-zinc-600 text-center mb-6">
                        Les réponses à ce commentaire seront aussi supprimées.
                    </p>
                    <div className="flex gap-3">
                        <button
                            onClick={() => setConfirmDeleteComment(null)}
                            className="flex-1 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg"
                        >
                            Annuler
                        </button>
                        <button
                            onClick={() => handleDeleteComment(confirmDeleteComment)}
                            className="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg"
                        >
                            Supprimer
                        </button>
                    </div>
                </Modal>
            )}
        </AdminLayout>
    );
}

/* ============ CommentBlock ============ */
function CommentBlock({
    comment,
    onDelete,
    isReply = false,
}: {
    comment: any;
    onDelete: () => void;
    isReply?: boolean;
}) {
    return (
        <div className="flex items-start gap-3 group">
            {comment.user ? (
                <img
                    src={
                        comment.user.photo ||
                        `https://ui-avatars.com/api/?name=${encodeURIComponent(
                            comment.user.name,
                        )}&background=10b981&color=fff`
                    }
                    alt=""
                    className={`${
                        isReply ? 'w-7 h-7' : 'w-9 h-9'
                    } rounded-full object-cover flex-shrink-0`}
                />
            ) : (
                <div
                    className={`${
                        isReply ? 'w-7 h-7' : 'w-9 h-9'
                    } rounded-full bg-zinc-200 flex-shrink-0`}
                />
            )}
            <div className="flex-1 min-w-0">
                <div className="bg-zinc-50 rounded-2xl px-4 py-2">
                    <p className="text-xs font-semibold text-zinc-900 mb-0.5">
                        {comment.user ? (
                            <Link
                                href={`/admin/users/${comment.user.id}`}
                                className="hover:text-emerald-600"
                            >
                                {comment.user.name}
                            </Link>
                        ) : (
                            <span className="italic text-zinc-400">Utilisateur supprimé</span>
                        )}
                    </p>
                    <p className="text-sm text-zinc-700 whitespace-pre-wrap">{comment.body}</p>
                </div>
                <p className="text-[10px] text-zinc-400 mt-1 ml-3">
                    {comment.created_at_human}
                </p>
            </div>
            <button
                onClick={onDelete}
                className="opacity-0 group-hover:opacity-100 p-1 hover:bg-red-50 rounded text-zinc-400 hover:text-red-600 transition-all flex-shrink-0"
                title="Supprimer"
            >
                <Trash2 className="w-3.5 h-3.5" />
            </button>
        </div>
    );
}

function InfoRow({
    icon: Icon,
    label,
    value,
}: {
    icon: any;
    label: string;
    value: string;
}) {
    return (
        <div>
            <p className="text-xs text-zinc-500 mb-0.5 flex items-center gap-1.5">
                <Icon className="w-3 h-3" />
                {label}
            </p>
            <p className="text-sm text-zinc-900 break-words">{value}</p>
        </div>
    );
}

function Modal({
    children,
    onClose,
}: {
    children: React.ReactNode;
    onClose: () => void;
}) {
    return (
        <div
            className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            onClick={onClose}
        >
            <motion.div
                initial={{ scale: 0.9, opacity: 0 }}
                animate={{ scale: 1, opacity: 1 }}
                onClick={(e) => e.stopPropagation()}
                className="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full"
            >
                {children}
            </motion.div>
        </div>
    );
}
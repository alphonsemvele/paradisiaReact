import { useState, useEffect, useRef } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { MoreHorizontal, ThumbsUp, MessageCircle, Share2, Edit2, Trash2, X, Eye, TrendingUp } from 'lucide-react';
import PublicationMedia from './PublicationMedia';
import { TexteLie, ApercuYoutube, extraireYoutube } from './PostText';
import StatsModal from './StatsModal';
import CommentsSection from './CommentsSection';
import AuthPrompt from './AuthPrompt';
import type { Publication, User } from '@/types';

interface Props {
    publication: Publication;
    currentUser: User | null;
    onShare: () => void;
    /** Dans le fil : ouvre la publication en modale (façon Facebook) au clic « Commenter ». */
    onComment?: () => void;
    /** Dans la modale : commentaires ouverts par défaut. */
    defaultShowComments?: boolean;
}

export default function PublicationCard({ publication, currentUser, onShare, onComment, defaultShowComments = false }: Props) {
    const [showComments, setShowComments] = useState(defaultShowComments);

    // « Commenter » : dans le fil → ouvre la modale ; dans la modale → affiche
    // la section commentaires.
    const ouvrirCommentaires = () => {
        if (onComment) onComment();
        else setShowComments(true);
    };
    const [showMenu, setShowMenu] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [showStats, setShowStats] = useState(false);
    const menuRef = useRef<HTMLDivElement>(null);

    // État local du like : mis à jour instantanément, la requête part en fond.
    const [liked, setLiked] = useState(!!publication.has_liked);
    const [likesCount, setLikesCount] = useState(publication.likes_count ?? 0);

    // Compteur de commentaires ajustable quand on en poste un (optimiste).
    const [commentsCount, setCommentsCount] = useState(publication.comments_count ?? 0);

    // « Voir plus » : le texte long est replié par défaut.
    const [texteDeplie, setTexteDeplie] = useState(false);
    const SEUIL_TEXTE = 280;

    // Invite à se connecter (visiteur non connecté qui tente une action).
    const [authPrompt, setAuthPrompt] = useState<'like' | 'comment' | 'share' | null>(null);

    const userName = publication.user?.name ?? 'Utilisateur supprimé';
    const userPhoto =
        publication.user?.photo ??
        `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=10b981&color=fff&size=64`;

    // Fermer le menu au clic extérieur
    useEffect(() => {
        const handleClickOutside = (e: MouseEvent) => {
            if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
                setShowMenu(false);
            }
        };
        if (showMenu) {
            document.addEventListener('mousedown', handleClickOutside);
            return () => document.removeEventListener('mousedown', handleClickOutside);
        }
    }, [showMenu]);

    const handleLike = () => {
        if (!currentUser) {
            // Instantané : on ouvre l'invite, aucune navigation.
            setAuthPrompt('like');
            return;
        }

        // Réaction immédiate à l'écran (pouce bleu).
        const nouveau = !liked;
        setLiked(nouveau);
        setLikesCount((n) => n + (nouveau ? 1 : -1));

        // Envoi via Inertia : CSRF géré automatiquement (fiable). preserveState
        // garde l'état optimiste ; le seed stable évite le re-mélange du fil.
        router.post(
            `/publications/${publication.id}/like`,
            {},
            { preserveScroll: true, preserveState: true }
        );
    };

    const handleDelete = () => {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')) return;
        router.delete(`/publications/${publication.id}`, { preserveScroll: true });
    };

    return (
        <div className="group bg-white rounded-3xl overflow-hidden border border-zinc-100 shadow-sm hover:shadow-[0_14px_40px_-16px_rgba(16,185,129,0.35)] hover:-translate-y-0.5 transition-all duration-300">
            {/* Header */}
            <div className="p-4 pb-0">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        {publication.user?.id ? (
                            <Link href={`/u/${publication.user.id}`} className="flex items-center gap-3 group">
                                <span className="w-11 h-11 rounded-full p-[2px] bg-gradient-to-br from-emerald-400 via-lime-400 to-amber-300 flex-shrink-0 inline-flex"><img src={userPhoto} alt={userName} className="w-full h-full rounded-full object-cover border-2 border-white" /></span>
                                <div>
                                    <h4 className="font-bold text-gray-900 text-sm group-hover:underline">{userName}</h4>
                                    <p className="text-xs text-gray-500 flex items-center gap-1.5">{publication.created_at_human} · 🌍</p>
                                </div>
                            </Link>
                        ) : (
                            <>
                                <span className="w-11 h-11 rounded-full p-[2px] bg-gradient-to-br from-emerald-400 via-lime-400 to-amber-300 flex-shrink-0 inline-flex"><img src={userPhoto} alt={userName} className="w-full h-full rounded-full object-cover border-2 border-white" /></span>
                                <div>
                                    <h4 className="font-bold text-gray-900 text-sm">{userName}</h4>
                                    <p className="text-xs text-gray-500 flex items-center gap-1.5">{publication.created_at_human} · 🌍</p>
                                </div>
                            </>
                        )}
                    </div>

                    {/* Menu modifier/supprimer */}
                    {publication.is_owner && (
                        <div className="relative" ref={menuRef}>
                            <button
                                onClick={() => setShowMenu(!showMenu)}
                                className="p-2 hover:bg-zinc-100 rounded-full transition-all"
                            >
                                <MoreHorizontal className="w-5 h-5 text-gray-500" />
                            </button>

                            {showMenu && (
                                <div className="absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-20 min-w-[180px]">
                                    <button
                                        onClick={() => {
                                            setShowStats(true);
                                            setShowMenu(false);
                                        }}
                                        className="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-700 transition-colors"
                                    >
                                        <TrendingUp className="w-4 h-4" />
                                        Voir les statistiques
                                    </button>
                                    <button
                                        onClick={() => {
                                            setIsEditing(true);
                                            setShowMenu(false);
                                        }}
                                        className="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-700 transition-colors"
                                    >
                                        <Edit2 className="w-4 h-4" />
                                        Modifier
                                    </button>
                                    <button
                                        onClick={() => {
                                            handleDelete();
                                            setShowMenu(false);
                                        }}
                                        className="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-red-50 text-sm text-red-600 transition-colors"
                                    >
                                        <Trash2 className="w-4 h-4" />
                                        Supprimer
                                    </button>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Content - Edit mode */}
            {isEditing ? (
                <EditPublicationForm
                    publication={publication}
                    onCancel={() => setIsEditing(false)}
                    onSuccess={() => setIsEditing(false)}
                />
            ) : (
                publication.text && (
                    <div className="px-4 py-3">
                        <TexteLie
                            texte={
                                texteDeplie || publication.text.length <= SEUIL_TEXTE
                                    ? publication.text
                                    : publication.text.slice(0, SEUIL_TEXTE).trimEnd() + '…'
                            }
                        />
                        {publication.text.length > SEUIL_TEXTE && (
                            <button
                                onClick={() => setTexteDeplie((v) => !v)}
                                className="mt-1 text-sm font-semibold text-emerald-600 hover:underline"
                            >
                                {texteDeplie ? 'Voir moins' : 'Voir plus'}
                            </button>
                        )}

                        {/* Prévisualisation vidéo (YouTube) détectée dans le texte */}
                        {(() => {
                            const yt = extraireYoutube(publication.text || '');
                            return yt ? <ApercuYoutube id={yt.id} url={yt.url} /> : null;
                        })()}
                    </div>
                )
            )}

            {/* Media */}
            <PublicationMedia publication={publication} />

            {/* Stats */}
            <div className="px-4 py-2 flex items-center justify-between text-xs text-gray-500 border-b border-zinc-100">
                <div className="flex items-center gap-1">
                    {likesCount > 0 && (
                        <div className="flex items-center">
                            <span className="bg-blue-500 text-white rounded-full p-1 -mr-1 z-10">
                                <ThumbsUp className="w-3 h-3 fill-current" />
                            </span>
                            <span className="bg-red-500 text-white rounded-full p-1">
                                <svg
                                    className="w-3 h-3"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </span>
                            <span className="ml-2 hover:underline cursor-pointer">
                                {liked && likesCount === 1
                                    ? 'Vous'
                                    : liked && likesCount > 1
                                    ? `Vous et ${likesCount - 1} autre${likesCount > 2 ? 's' : ''}`
                                    : likesCount}
                            </span>
                        </div>
                    )}
                </div>
                <div className="flex items-center gap-3">
                    {commentsCount > 0 && (
                        <span
                            onClick={ouvrirCommentaires}
                            className="hover:underline cursor-pointer"
                        >
                            {commentsCount} commentaire
                            {commentsCount > 1 ? 's' : ''}
                        </span>
                    )}
                    {/* Nombre de vues : visible par tous. L'auteur peut cliquer
                        pour ouvrir le détail des statistiques. */}
                    <button
                        onClick={() => publication.is_owner && setShowStats(true)}
                        className={`inline-flex items-center gap-1 ${
                            publication.is_owner ? 'font-medium text-emerald-600 hover:underline cursor-pointer' : 'text-gray-500 cursor-default'
                        }`}
                    >
                        <Eye className="w-3.5 h-3.5" />
                        {new Intl.NumberFormat('fr-FR').format(publication.views_count)} vue
                        {publication.views_count > 1 ? 's' : ''}
                    </button>
                    {publication.shares_count > 0 && (
                        <span className="hover:underline cursor-pointer">
                            {publication.shares_count} partage
                            {publication.shares_count > 1 ? 's' : ''}
                        </span>
                    )}
                </div>
            </div>

            {/* Actions */}
            <div className="px-2 py-1 flex items-center justify-around border-b border-zinc-100">
                <button
                    onClick={handleLike}
                    className={`flex-1 flex items-center justify-center gap-2 py-2 rounded-xl transition-all active:scale-95 ${
                        liked ? 'text-emerald-600 bg-emerald-50' : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-600'
                    }`}
                >
                    <ThumbsUp className={`w-5 h-5 transition-transform ${liked ? 'fill-current scale-110' : ''}`} />
                    <span className="font-semibold text-sm">J'aime</span>
                </button>

                <button
                    onClick={ouvrirCommentaires}
                    className="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl text-gray-600 hover:bg-sky-50 hover:text-sky-600 transition-all"
                >
                    <MessageCircle className="w-5 h-5" />
                    <span className="font-semibold text-sm">Commenter</span>
                    {commentsCount > 0 && (
                        <span className="text-xs bg-gray-200 px-1.5 py-0.5 rounded-full">
                            {commentsCount}
                        </span>
                    )}
                </button>

                <button
                    onClick={() => (currentUser ? onShare() : setAuthPrompt('share'))}
                    className="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl text-gray-600 hover:bg-amber-50 hover:text-amber-600 transition-all"
                >
                    <Share2 className="w-5 h-5" />
                    <span className="font-semibold text-sm">Partager</span>
                    {publication.shares_count > 0 && (
                        <span className="text-xs bg-gray-200 px-1.5 py-0.5 rounded-full">
                            {publication.shares_count}
                        </span>
                    )}
                </button>
            </div>

            {/* Comments */}
            {showComments && (
                <CommentsSection
                    publication={publication}
                    currentUser={currentUser}
                    onCommentAdded={() => setCommentsCount((n) => n + 1)}
                    onRequireAuth={() => setAuthPrompt('comment')}
                />
            )}

            {/* Statistiques (auteur uniquement) */}
            {showStats && (
                <StatsModal
                    publicationId={publication.id}
                    onClose={() => setShowStats(false)}
                />
            )}

            {/* Invite à se connecter (visiteur) */}
            <AuthPrompt open={authPrompt !== null} action={authPrompt} onClose={() => setAuthPrompt(null)} />
        </div>
    );
}

// 🆕 Formulaire d'édition inline
function EditPublicationForm({
    publication,
    onCancel,
    onSuccess,
}: {
    publication: Publication;
    onCancel: () => void;
    onSuccess: () => void;
}) {
    const { data, setData, patch, processing } = useForm({
        content: publication.text,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(`/publications/${publication.id}`, {
            preserveScroll: true,
            onSuccess,
        });
    };

    return (
        <form onSubmit={handleSubmit} className="px-4 py-3">
            <div className="bg-emerald-50 border-2 border-emerald-300 rounded-xl p-3">
                <div className="flex items-center justify-between mb-2">
                    <span className="text-xs font-semibold text-emerald-700 uppercase tracking-wide">
                        Modifier la publication
                    </span>
                    <button
                        type="button"
                        onClick={onCancel}
                        className="p-1 hover:bg-emerald-100 rounded transition-colors"
                    >
                        <X className="w-4 h-4 text-emerald-700" />
                    </button>
                </div>
                <textarea
                    value={data.content}
                    onChange={(e) => setData('content', e.target.value)}
                    rows={4}
                    className="w-full bg-transparent text-sm focus:outline-none resize-none text-gray-800"
                    autoFocus
                />
                <div className="flex justify-end gap-2 mt-2">
                    <button
                        type="button"
                        onClick={onCancel}
                        className="text-xs text-gray-600 hover:underline px-3 py-1.5"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        disabled={processing || !data.content.trim()}
                        className="text-xs bg-emerald-500 hover:bg-emerald-600 disabled:bg-gray-300 text-white font-semibold px-4 py-1.5 rounded-lg transition-colors"
                    >
                        {processing ? 'Enregistrement...' : 'Enregistrer'}
                    </button>
                </div>
            </div>
        </form>
    );
}
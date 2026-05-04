import { useState, useEffect, useRef } from 'react';
import { router, useForm } from '@inertiajs/react';
import { MoreHorizontal, ThumbsUp, MessageCircle, Share2, Edit2, Trash2, X } from 'lucide-react';
import PublicationMedia from './PublicationMedia';
import CommentsSection from './CommentsSection';
import type { Publication, User } from '@/types';

interface Props {
    publication: Publication;
    currentUser: User | null;
    onShare: () => void;
}

export default function PublicationCard({ publication, currentUser, onShare }: Props) {
    const [showComments, setShowComments] = useState(false);
    const [showMenu, setShowMenu] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const menuRef = useRef<HTMLDivElement>(null);

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
            router.visit('/login');
            return;
        }
        router.post(`/publications/${publication.id}/like`, {}, { preserveScroll: true });
    };

    const handleDelete = () => {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')) return;
        router.delete(`/publications/${publication.id}`, { preserveScroll: true });
    };

    return (
        <div className="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300">
            {/* Header */}
            <div className="p-4 pb-0">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <img
                            src={userPhoto}
                            alt={userName}
                            className="w-11 h-11 rounded-full border-2 border-emerald-400 object-cover"
                        />
                        <div>
                            <h4 className="font-bold text-gray-900 text-sm hover:underline cursor-pointer">
                                {userName}
                            </h4>
                            <p className="text-xs text-gray-500">
                                {publication.created_at_human} · 🌍
                            </p>
                        </div>
                    </div>

                    {/* Menu modifier/supprimer */}
                    {publication.is_owner && (
                        <div className="relative" ref={menuRef}>
                            <button
                                onClick={() => setShowMenu(!showMenu)}
                                className="p-2 hover:bg-gray-100 rounded-full transition-all"
                            >
                                <MoreHorizontal className="w-5 h-5 text-gray-500" />
                            </button>

                            {showMenu && (
                                <div className="absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-20 min-w-[180px]">
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
                        <p className="text-gray-800 text-sm leading-relaxed whitespace-pre-line">
                            {publication.text}
                        </p>
                    </div>
                )
            )}

            {/* Media */}
            <PublicationMedia publication={publication} />

            {/* Stats */}
            <div className="px-4 py-2 flex items-center justify-between text-xs text-gray-500 border-b border-gray-100">
                <div className="flex items-center gap-1">
                    {publication.likes_count > 0 && (
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
                                {publication.has_liked && publication.likes_count === 1
                                    ? 'Vous'
                                    : publication.has_liked && publication.likes_count > 1
                                    ? `Vous et ${publication.likes_count - 1} autre${
                                          publication.likes_count > 2 ? 's' : ''
                                      }`
                                    : publication.likes_count}
                            </span>
                        </div>
                    )}
                </div>
                <div className="flex items-center gap-3">
                    {publication.comments_count > 0 && (
                        <span
                            onClick={() => setShowComments(!showComments)}
                            className="hover:underline cursor-pointer"
                        >
                            {publication.comments_count} commentaire
                            {publication.comments_count > 1 ? 's' : ''}
                        </span>
                    )}
                    {publication.shares_count > 0 && (
                        <span className="hover:underline cursor-pointer">
                            {publication.shares_count} partage
                            {publication.shares_count > 1 ? 's' : ''}
                        </span>
                    )}
                </div>
            </div>

            {/* Actions */}
            <div className="px-2 py-1 flex items-center justify-around border-b border-gray-100">
                <button
                    onClick={handleLike}
                    className={`flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-100 transition-all ${
                        publication.has_liked ? 'text-blue-600' : 'text-gray-600'
                    }`}
                >
                    <ThumbsUp
                        className={`w-5 h-5 ${publication.has_liked ? 'fill-current' : ''}`}
                    />
                    <span className="font-semibold text-sm">J'aime</span>
                </button>

                <button
                    onClick={() => setShowComments(!showComments)}
                    className="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-all"
                >
                    <MessageCircle className="w-5 h-5" />
                    <span className="font-semibold text-sm">Commenter</span>
                    {publication.comments_count > 0 && (
                        <span className="text-xs bg-gray-200 px-1.5 py-0.5 rounded-full">
                            {publication.comments_count}
                        </span>
                    )}
                </button>

                <button
                    onClick={onShare}
                    className="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-all"
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
                <CommentsSection publication={publication} currentUser={currentUser} />
            )}
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
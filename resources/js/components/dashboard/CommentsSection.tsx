import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Smile, Send, Heart } from 'lucide-react';
import type { Publication, Comment, User } from '@/types';

interface Props {
    publication: Publication;
    currentUser: User | null;
    onCommentAdded?: () => void;
}

const EMOJIS = ['😀', '😂', '😍', '🥰', '😊', '🤔', '😢', '😮', '👍', '❤️', '🔥', '🎉', '👏', '💯', '🙏', '😎'];

const csrf = () =>
    document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

export default function CommentsSection({ publication, currentUser, onCommentAdded }: Props) {
    const [commentText, setCommentText] = useState('');
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);
    // Liste locale : le commentaire apparaît immédiatement, l'envoi suit en fond.
    const [comments, setComments] = useState<Comment[]>(publication.comments ?? []);
    const [envoi, setEnvoi] = useState(false);

    const handleAddComment = () => {
        if (!currentUser) {
            router.visit('/login');
            return;
        }
        const texte = commentText.trim();
        if (!texte || envoi) return;

        // 1) Affichage immédiat d'un commentaire provisoire
        const tempId = -Date.now();
        const optimiste: Comment = {
            id: tempId,
            body: texte,
            created_at_human: "À l'instant",
            likes_count: 0,
            has_liked: false,
            is_owner: true,
            replies: [],
            user: {
                id: currentUser.id,
                name: currentUser.name,
                photo: currentUser.photo ?? null,
            },
        } as unknown as Comment;

        setComments((c) => [...c, optimiste]);
        setCommentText('');
        setEnvoi(true);
        onCommentAdded?.();

        // 2) Envoi en arrière-plan, sans recharger le fil
        fetch(`/publications/${publication.id}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ body: texte }),
        })
            .then((r) => (r.ok ? r.json() : Promise.reject(r)))
            .then((d) => {
                // On remplace le provisoire par le commentaire réel
                if (d.comment) {
                    setComments((c) => c.map((x) => (x.id === tempId ? d.comment : x)));
                }
            })
            .catch(() => {
                // Échec : on retire le provisoire et on restaure le texte
                setComments((c) => c.filter((x) => x.id !== tempId));
                setCommentText(texte);
            })
            .finally(() => setEnvoi(false));
    };

    const insertEmoji = (emoji: string) => {
        setCommentText((prev) => prev + emoji);
        setShowEmojiPicker(false);
    };

    const userAvatar = currentUser
        ? currentUser.photo ||
          `https://ui-avatars.com/api/?name=${encodeURIComponent(currentUser.name)}&background=10b981&color=fff&size=64`
        : 'https://ui-avatars.com/api/?name=Guest&background=gray&color=fff&size=64';

    return (
        <div className="p-4 bg-gray-50">
            {/* Add Comment */}
            <div className="flex items-start gap-2 mb-4">
                <img
                    src={userAvatar}
                    alt="Your avatar"
                    className="w-8 h-8 rounded-full object-cover flex-shrink-0"
                />

                <div className="flex-1 relative">
                    <div className="bg-white rounded-2xl border border-gray-200 flex items-center pr-2">
                        <input
                            type="text"
                            value={commentText}
                            onChange={(e) => setCommentText(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && handleAddComment()}
                            placeholder="Écrire un commentaire..."
                            className="flex-1 bg-transparent px-4 py-2 text-sm focus:outline-none rounded-2xl"
                        />

                        <button
                            type="button"
                            onClick={() => setShowEmojiPicker(!showEmojiPicker)}
                            className="p-1 hover:bg-gray-100 rounded-full text-gray-500"
                        >
                            <Smile className="w-5 h-5" />
                        </button>

                        <button
                            onClick={handleAddComment}
                            className="p-1 hover:bg-gray-100 rounded-full text-emerald-500"
                        >
                            <Send className="w-5 h-5" />
                        </button>
                    </div>

                    {showEmojiPicker && (
                        <div
                            className="absolute bottom-full left-0 mb-2 bg-white rounded-lg shadow-lg border border-gray-200 p-2 z-50"
                            onMouseLeave={() => setShowEmojiPicker(false)}
                        >
                            <div className="grid grid-cols-8 gap-1">
                                {EMOJIS.map((emoji) => (
                                    <button
                                        key={emoji}
                                        type="button"
                                        onClick={() => insertEmoji(emoji)}
                                        className="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded text-lg"
                                    >
                                        {emoji}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Comments List */}
            {comments.length > 0 ? (
                comments.map((comment) => (
                    <CommentItem
                        key={comment.id}
                        comment={comment}
                        publicationId={publication.id}
                        currentUser={currentUser}
                    />
                ))
            ) : (
                <p className="text-center text-gray-500 text-sm py-4">
                    Soyez le premier à commenter ! 💬
                </p>
            )}
        </div>
    );
}

interface CommentItemProps {
    comment: Comment;
    publicationId: number;
    currentUser: User | null;
}

function CommentItem({ comment, publicationId, currentUser }: CommentItemProps) {
    const [isEditing, setIsEditing] = useState(false);
    const [editText, setEditText] = useState(comment.body);
    const [showReplies, setShowReplies] = useState(false);
    const [replyText, setReplyText] = useState('');

    const userName = comment.user?.name ?? 'Utilisateur supprimé';
    const userPhoto =
        comment.user?.photo ??
        `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=6b7280&color=fff&size=64`;

    const handleLike = () => {
        if (!currentUser) {
            router.visit('/login');
            return;
        }
        router.post(`/comments/${comment.id}/like`, {}, { preserveScroll: true });
    };

    const handleUpdate = () => {
        if (!editText.trim()) return;
        router.patch(
            `/comments/${comment.id}`,
            { body: editText.trim() },
            {
                preserveScroll: true,
                onSuccess: () => setIsEditing(false),
            }
        );
    };

    const handleDelete = () => {
        if (!confirm('Supprimer ce commentaire ?')) return;
        router.delete(`/comments/${comment.id}`, { preserveScroll: true });
    };

    const handleReply = () => {
        if (!replyText.trim()) return;
        router.post(
            `/publications/${publicationId}/comment`,
            { body: replyText.trim(), parent_id: comment.id },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setReplyText('');
                    setShowReplies(true);
                },
            }
        );
    };

    return (
        <div className="mb-3">
            <div className="flex items-start gap-2">
                <img
                    src={userPhoto}
                    alt={userName}
                    className="w-8 h-8 rounded-full object-cover flex-shrink-0"
                />

                <div className="flex-1">
                    {isEditing ? (
                        <div className="bg-white rounded-2xl border border-emerald-300 p-2">
                            <input
                                type="text"
                                value={editText}
                                onChange={(e) => setEditText(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') handleUpdate();
                                    if (e.key === 'Escape') setIsEditing(false);
                                }}
                                className="w-full bg-transparent text-sm focus:outline-none"
                                autoFocus
                            />
                            <div className="flex justify-end gap-2 mt-2">
                                <button
                                    onClick={() => setIsEditing(false)}
                                    className="text-xs text-gray-500 hover:underline"
                                >
                                    Annuler
                                </button>
                                <button
                                    onClick={handleUpdate}
                                    className="text-xs text-emerald-600 hover:underline font-semibold"
                                >
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    ) : (
                        <>
                            <div className="relative inline-block max-w-full">
                                <div className="bg-gray-200 rounded-2xl px-3 py-2">
                                    <p className="font-semibold text-xs text-gray-900">
                                        {userName}
                                    </p>
                                    <p className="text-sm text-gray-800">{comment.body}</p>
                                </div>

                                {/* Badge likes count sur le commentaire */}
                                {comment.likes_count > 0 && (
                                    <div className="absolute -bottom-2 -right-1 flex items-center gap-1 bg-white border border-gray-200 rounded-full px-2 py-0.5 shadow-sm">
                                        <Heart className="w-3 h-3 fill-red-500 text-red-500" />
                                        <span className="text-xs font-medium text-gray-700">
                                            {comment.likes_count}
                                        </span>
                                    </div>
                                )}
                            </div>

                            <div className="flex items-center gap-3 mt-2 ml-2 text-xs">
                                <span className="text-gray-500">{comment.created_at_human}</span>
                                <button
                                    onClick={handleLike}
                                    className={`font-semibold hover:underline transition-colors ${
                                        comment.has_liked
                                            ? 'text-red-500'
                                            : 'text-gray-600 hover:text-red-500'
                                    }`}
                                >
                                    J'aime
                                </button>
                                <button
                                    onClick={() => setShowReplies(!showReplies)}
                                    className="font-semibold text-gray-600 hover:underline"
                                >
                                    Répondre
                                </button>
                                {comment.is_owner && (
                                    <>
                                        <button
                                            onClick={() => setIsEditing(true)}
                                            className="font-semibold text-gray-600 hover:underline"
                                        >
                                            Modifier
                                        </button>
                                        <button
                                            onClick={handleDelete}
                                            className="font-semibold text-red-500 hover:underline"
                                        >
                                            Supprimer
                                        </button>
                                    </>
                                )}
                            </div>
                        </>
                    )}

                    {/* Replies */}
                    {comment.replies && comment.replies.length > 0 && (
                        <div className="mt-3 ml-4 space-y-2">
                            {!showReplies && comment.replies.length > 1 && (
                                <button
                                    onClick={() => setShowReplies(true)}
                                    className="text-xs font-semibold text-gray-600 hover:underline"
                                >
                                    Voir {comment.replies.length} réponse
                                    {comment.replies.length > 1 ? 's' : ''}
                                </button>
                            )}

                            {(showReplies ? comment.replies : comment.replies.slice(0, 1)).map(
                                (reply) => (
                                    <ReplyItem
                                        key={reply.id}
                                        reply={reply}
                                        currentUser={currentUser}
                                    />
                                )
                            )}
                        </div>
                    )}

                    {/* Reply Input */}
                    {showReplies && currentUser && (
                        <div className="mt-2 ml-4 flex items-start gap-2">
                            <img
                                src={
                                    currentUser.photo ||
                                    `https://ui-avatars.com/api/?name=${encodeURIComponent(currentUser.name)}&background=10b981&color=fff&size=64`
                                }
                                alt="Your avatar"
                                className="w-6 h-6 rounded-full object-cover flex-shrink-0"
                            />
                            <div className="flex-1 bg-white rounded-2xl border border-gray-200 flex items-center pr-2">
                                <input
                                    type="text"
                                    value={replyText}
                                    onChange={(e) => setReplyText(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && handleReply()}
                                    placeholder={`Répondre à ${userName}...`}
                                    className="flex-1 bg-transparent px-3 py-1.5 text-sm focus:outline-none rounded-2xl"
                                />
                                <button
                                    onClick={handleReply}
                                    className="p-1 hover:bg-gray-100 rounded-full text-emerald-500"
                                >
                                    <Send className="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

function ReplyItem({ reply, currentUser }: { reply: Comment; currentUser: User | null }) {
    const userName = reply.user?.name ?? 'Utilisateur supprimé';
    const userPhoto =
        reply.user?.photo ??
        `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=9ca3af&color=fff&size=64`;

    const handleLike = () => {
        if (!currentUser) {
            router.visit('/login');
            return;
        }
        router.post(`/comments/${reply.id}/like`, {}, { preserveScroll: true });
    };

    const handleDelete = () => {
        if (!confirm('Supprimer cette réponse ?')) return;
        router.delete(`/comments/${reply.id}`, { preserveScroll: true });
    };

    return (
        <div className="flex items-start gap-2">
            <img
                src={userPhoto}
                alt={userName}
                className="w-6 h-6 rounded-full object-cover flex-shrink-0"
            />
            <div className="flex-1">
                <div className="relative inline-block max-w-full">
                    <div className="bg-gray-200 rounded-2xl px-3 py-2">
                        <p className="font-semibold text-xs text-gray-900">{userName}</p>
                        <p className="text-sm text-gray-800">{reply.body}</p>
                    </div>

                    {reply.likes_count > 0 && (
                        <div className="absolute -bottom-2 -right-1 flex items-center gap-1 bg-white border border-gray-200 rounded-full px-2 py-0.5 shadow-sm">
                            <Heart className="w-3 h-3 fill-red-500 text-red-500" />
                            <span className="text-xs font-medium text-gray-700">
                                {reply.likes_count}
                            </span>
                        </div>
                    )}
                </div>

                <div className="flex items-center gap-3 mt-2 ml-2 text-xs">
                    <span className="text-gray-500">{reply.created_at_human}</span>
                    <button
                        onClick={handleLike}
                        className={`font-semibold hover:underline transition-colors ${
                            reply.has_liked ? 'text-red-500' : 'text-gray-600 hover:text-red-500'
                        }`}
                    >
                        J'aime
                    </button>
                    {reply.is_owner && (
                        <button
                            onClick={handleDelete}
                            className="font-semibold text-red-500 hover:underline"
                        >
                            Supprimer
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}
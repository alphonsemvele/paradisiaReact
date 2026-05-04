import { useState, FormEvent, ChangeEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { X, Image as ImageIcon, Video as VideoIcon } from 'lucide-react';
import type { User } from '@/types';

interface Props {
    user: User | null;
    onClose: () => void;
}

interface FormData {
    content: string;
    image: File | null;
    video: File | null;
    visibility: 'public' | 'friends' | 'private';
    [key: string]: any;
}

export default function CreatePostModal({ user, onClose }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm<FormData>({
        content: '',
        image: null,
        video: null,
        visibility: 'public',
    });

    const [imagePreview, setImagePreview] = useState<string | null>(null);
    const [videoPreview, setVideoPreview] = useState<string | null>(null);

    const handleImageChange = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('image', file);
            setImagePreview(URL.createObjectURL(file));
            // Reset video si une image est ajoutée
            setData('video', null);
            setVideoPreview(null);
        }
    };

    const handleVideoChange = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('video', file);
            setVideoPreview(URL.createObjectURL(file));
            // Reset image si une vidéo est ajoutée
            setData('image', null);
            setImagePreview(null);
        }
    };

    const removeImage = () => {
        setData('image', null);
        setImagePreview(null);
    };

    const removeVideo = () => {
        setData('video', null);
        setVideoPreview(null);
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/publications', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setImagePreview(null);
                setVideoPreview(null);
                onClose();
            },
        });
    };

    const userAvatar = user
        ? user.photo ||
          `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=10b981&color=fff&size=64`
        : 'https://ui-avatars.com/api/?name=Guest&background=10b981&color=fff&size=64';

    return (
        <div
            className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 animate-fade-in"
            onClick={(e) => e.target === e.currentTarget && onClose()}
        >
            <div className="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto animate-scale-in">
                {/* Header */}
                <div className="sticky top-0 bg-white border-b border-gray-200 p-4 rounded-t-2xl z-10">
                    <div className="flex items-center justify-between">
                        <h3 className="text-xl font-bold text-gray-800">
                            Créer une publication
                        </h3>
                        <button
                            onClick={onClose}
                            className="p-2 hover:bg-gray-100 rounded-full transition-all"
                        >
                            <X className="w-5 h-5 text-gray-500" />
                        </button>
                    </div>
                </div>

                {/* Content */}
                <div className="p-4">
                    <form onSubmit={handleSubmit}>
                        {/* User info */}
                        {user && (
                            <div className="flex items-center gap-3 mb-4">
                                <img
                                    src={userAvatar}
                                    alt={user.name}
                                    className="w-10 h-10 rounded-full object-cover"
                                />
                                <div>
                                    <h4 className="font-bold text-gray-800 text-sm">
                                        {user.name}
                                    </h4>
                                    <select
                                        value={data.visibility}
                                        onChange={(e) =>
                                            setData(
                                                'visibility',
                                                e.target.value as 'public' | 'friends' | 'private'
                                            )
                                        }
                                        className="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded-lg border-0 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                    >
                                        <option value="public">🌍 Public</option>
                                        <option value="friends">👥 Amis</option>
                                        <option value="private">🔒 Privé</option>
                                    </select>
                                </div>
                            </div>
                        )}

                        {/* Textarea */}
                        <textarea
                            value={data.content}
                            onChange={(e) => setData('content', e.target.value)}
                            rows={5}
                            placeholder="Que voulez-vous partager ? 🌴"
                            className="w-full p-3 border-0 focus:outline-none resize-none text-gray-800 text-lg"
                            autoFocus
                        />
                        {errors.content && (
                            <p className="text-red-500 text-xs mt-1">{errors.content}</p>
                        )}

                        {/* Image preview */}
                        {imagePreview && (
                            <div className="relative mt-3 rounded-xl overflow-hidden border border-gray-200">
                                <img
                                    src={imagePreview}
                                    alt="Preview"
                                    className="w-full max-h-80 object-cover"
                                />
                                <button
                                    type="button"
                                    onClick={removeImage}
                                    className="absolute top-2 right-2 p-2 bg-black/60 hover:bg-black/80 rounded-full transition-all"
                                >
                                    <X className="w-4 h-4 text-white" />
                                </button>
                            </div>
                        )}

                        {/* Video preview */}
                        {videoPreview && (
                            <div className="relative mt-3 rounded-xl overflow-hidden border border-gray-200 bg-black">
                                <video
                                    src={videoPreview}
                                    controls
                                    className="w-full max-h-80"
                                />
                                <button
                                    type="button"
                                    onClick={removeVideo}
                                    className="absolute top-2 right-2 p-2 bg-black/60 hover:bg-black/80 rounded-full transition-all"
                                >
                                    <X className="w-4 h-4 text-white" />
                                </button>
                            </div>
                        )}

                        {/* Add media */}
                        <div className="mt-4 p-3 border border-gray-200 rounded-xl">
                            <p className="text-sm font-semibold text-gray-700 mb-2">
                                Ajouter à votre publication
                            </p>
                            <div className="flex gap-2 flex-wrap">
                                <label className="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-emerald-50 text-emerald-600 cursor-pointer transition-all border border-emerald-200">
                                    <ImageIcon className="w-4 h-4" />
                                    <span className="text-sm font-semibold">Photo</span>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={handleImageChange}
                                        className="hidden"
                                    />
                                </label>
                                <label className="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-50 text-blue-600 cursor-pointer transition-all border border-blue-200">
                                    <VideoIcon className="w-4 h-4" />
                                    <span className="text-sm font-semibold">Vidéo</span>
                                    <input
                                        type="file"
                                        accept="video/*"
                                        onChange={handleVideoChange}
                                        className="hidden"
                                    />
                                </label>
                            </div>
                            {errors.image && (
                                <p className="text-red-500 text-xs mt-2">{errors.image}</p>
                            )}
                            {errors.video && (
                                <p className="text-red-500 text-xs mt-2">{errors.video}</p>
                            )}
                        </div>

                        {/* Submit */}
                        <button
                            type="submit"
                            disabled={processing || !data.content.trim()}
                            className="w-full mt-4 bg-emerald-500 hover:bg-emerald-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2"
                        >
                            {processing ? (
                                <>
                                    <svg
                                        className="animate-spin h-5 w-5"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        />
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        />
                                    </svg>
                                    Publication en cours...
                                </>
                            ) : (
                                'Publier'
                            )}
                        </button>
                    </form>
                </div>
            </div>

            <style>{`
                @keyframes fade-in {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes scale-in {
                    from { transform: scale(0.9); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
                .animate-fade-in { animation: fade-in 0.2s ease-out; }
                .animate-scale-in { animation: scale-in 0.2s ease-out; }
            `}</style>
        </div>
    );
}
import { useState } from 'react';
import { router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Link as LinkIcon, Check } from 'lucide-react';
import type { Publication } from '@/types';

interface Props {
    publication: Publication;
    onClose: () => void;
}

export default function ShareModal({ publication, onClose }: Props) {
    const [copied, setCopied] = useState(false);

    const baseUrl = window.location.origin;
    // Lien court : aperçu (image + texte) généré côté serveur pour WhatsApp & co
    const shareUrl = `${baseUrl}/p/${publication.id}`;
    const shareText = publication.text || 'Découvrez cette publication sur PARADISIA!';

    const recordShare = () => {
        router.post(`/publications/${publication.id}/share`, {}, { preserveScroll: true });
    };

    const handleShare = (platform: 'facebook' | 'whatsapp' | 'twitter' | 'telegram') => {
        let url = '';
        const encodedUrl = encodeURIComponent(shareUrl);
        const encodedText = encodeURIComponent(shareText);

        switch (platform) {
            case 'facebook':
                url = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}&quote=${encodedText}`;
                break;
            case 'whatsapp':
                url = `https://wa.me/?text=${encodeURIComponent(`${shareText}\n\n${shareUrl}`)}`;
                break;
            case 'twitter':
                url = `https://twitter.com/intent/tweet?text=${encodedText}&url=${encodedUrl}`;
                break;
            case 'telegram':
                url = `https://t.me/share/url?url=${encodedUrl}&text=${encodedText}`;
                break;
        }

        recordShare();
        window.open(url, '_blank', 'width=600,height=400,noopener,noreferrer');
        onClose();
    };

    const handleCopyLink = async () => {
        try {
            await navigator.clipboard.writeText(shareUrl);
            setCopied(true);
            recordShare();
            setTimeout(() => {
                setCopied(false);
                onClose();
            }, 1500);
        } catch (err) {
            console.error('Erreur copie :', err);
        }
    };

    return (
        <AnimatePresence>
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                onClick={(e) => e.target === e.currentTarget && onClose()}
            >
                <motion.div
                    initial={{ opacity: 0, scale: 0.95, y: 20 }}
                    animate={{ opacity: 1, scale: 1, y: 0 }}
                    exit={{ opacity: 0, scale: 0.95, y: 20 }}
                    transition={{ duration: 0.2 }}
                    className="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
                >
                    {/* Header */}
                    <div className="flex items-center justify-between p-5 border-b border-zinc-200">
                        <h3 className="text-lg font-bold text-zinc-900">Partager</h3>
                        <button
                            onClick={onClose}
                            className="p-2 hover:bg-zinc-100 rounded-full transition-all"
                        >
                            <X className="w-5 h-5 text-zinc-500" />
                        </button>
                    </div>

                    {/* Share options */}
                    <div className="p-5">
                        {/* Aperçu de la publication */}
                        {publication.text && (
                            <div className="mb-5 p-3 bg-zinc-50 border border-zinc-200 rounded-xl">
                                <p className="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">
                                    Vous partagez
                                </p>
                                <p className="text-sm text-zinc-700 line-clamp-2">
                                    {publication.text}
                                </p>
                            </div>
                        )}

                        {/* Plateformes */}
                        <p className="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-3">
                            Partager via
                        </p>
                        <div className="grid grid-cols-4 gap-3 mb-5">
                            <ShareButton
                                label="Facebook"
                                color="bg-blue-600 hover:bg-blue-700"
                                onClick={() => handleShare('facebook')}
                                icon={
                                    <svg
                                        className="w-7 h-7 text-white"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                }
                            />

                            <ShareButton
                                label="WhatsApp"
                                color="bg-emerald-500 hover:bg-emerald-600"
                                onClick={() => handleShare('whatsapp')}
                                icon={
                                    <svg
                                        className="w-7 h-7 text-white"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                    </svg>
                                }
                            />

                            <ShareButton
                                label="X"
                                color="bg-zinc-900 hover:bg-zinc-800"
                                onClick={() => handleShare('twitter')}
                                icon={
                                    <svg
                                        className="w-6 h-6 text-white"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                    </svg>
                                }
                            />

                            <ShareButton
                                label="Telegram"
                                color="bg-blue-500 hover:bg-blue-600"
                                onClick={() => handleShare('telegram')}
                                icon={
                                    <svg
                                        className="w-6 h-6 text-white"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
                                    </svg>
                                }
                            />
                        </div>

                        {/* Lien direct */}
                        <p className="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">
                            Ou copier le lien
                        </p>
                        <div className="flex items-center gap-2 p-2 bg-zinc-50 border border-zinc-200 rounded-xl mb-4">
                            <input
                                type="text"
                                readOnly
                                value={shareUrl}
                                className="flex-1 bg-transparent text-xs text-zinc-700 px-2 focus:outline-none truncate"
                            />
                            <button
                                onClick={handleCopyLink}
                                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all ${
                                    copied
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-zinc-900 hover:bg-zinc-800 text-white'
                                }`}
                            >
                                {copied ? (
                                    <>
                                        <Check className="w-3.5 h-3.5" />
                                        Copié !
                                    </>
                                ) : (
                                    <>
                                        <LinkIcon className="w-3.5 h-3.5" />
                                        Copier
                                    </>
                                )}
                            </button>
                        </div>

                        {/* Stats */}
                        {publication.shares_count > 0 && (
                            <p className="text-xs text-zinc-500 text-center">
                                Cette publication a déjà été partagée{' '}
                                <span className="font-semibold text-zinc-700">
                                    {publication.shares_count}
                                </span>{' '}
                                fois
                            </p>
                        )}
                    </div>
                </motion.div>
            </motion.div>
        </AnimatePresence>
    );
}

function ShareButton({
    label,
    color,
    onClick,
    icon,
}: {
    label: string;
    color: string;
    onClick: () => void;
    icon: React.ReactNode;
}) {
    return (
        <button
            onClick={onClick}
            className="flex flex-col items-center gap-2 p-2 rounded-xl hover:bg-zinc-50 transition-all group"
        >
            <div
                className={`w-12 h-12 ${color} rounded-full flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm`}
            >
                {icon}
            </div>
            <span className="text-[11px] font-medium text-zinc-700">{label}</span>
        </button>
    );
}
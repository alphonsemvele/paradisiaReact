import { useState } from 'react';
import { Link as LinkIcon, Check, Share2 } from 'lucide-react';

interface Props {
    /** Chemin relatif à partager, ex. « /formations/3 » */
    path: string;
    /** Texte accompagnant le lien sur WhatsApp / X / Telegram */
    text: string;
    /** Libellé affiché au-dessus des boutons */
    label?: string;
}

/**
 * Boutons de partage réutilisables (formations, produits…).
 *
 * L'aperçu riche (grande image + titre + description) est produit côté
 * serveur par les balises Open Graph : ces boutons ne font que transmettre
 * l'URL aux différentes plateformes.
 */
export default function ShareButtons({ path, text, label = 'Partager cette formation' }: Props) {
    const [copied, setCopied] = useState(false);

    const url = typeof window !== 'undefined' ? `${window.location.origin}${path}` : path;

    const open = (target: string) => {
        const u = encodeURIComponent(url);
        const t = encodeURIComponent(text);

        const liens: Record<string, string> = {
            whatsapp: `https://wa.me/?text=${encodeURIComponent(`${text}\n\n${url}`)}`,
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${u}&quote=${t}`,
            telegram: `https://t.me/share/url?url=${u}&text=${t}`,
        };

        window.open(liens[target], '_blank', 'width=600,height=500,noopener,noreferrer');
    };

    const copier = async () => {
        try {
            await navigator.clipboard.writeText(url);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch {
            // Navigateurs sans accès au presse-papiers : on sélectionne le lien
            window.prompt('Copiez le lien :', url);
        }
    };

    return (
        <div className="rounded-2xl border border-zinc-200 bg-white p-4">
            <p className="flex items-center gap-2 text-sm font-medium text-zinc-700 mb-3">
                <Share2 className="w-4 h-4 text-emerald-600" />
                {label}
            </p>

            <div className="flex flex-wrap gap-2">
                <button
                    type="button"
                    onClick={() => open('whatsapp')}
                    className="flex items-center gap-2 px-3 py-2 rounded-lg bg-[#25D366] hover:bg-[#20bd5a] text-white text-sm font-medium transition-colors"
                >
                    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M17.5 14.4c-.3-.2-1.7-.9-2-1-.3-.1-.5-.2-.7.1-.2.3-.7 1-.9 1.2-.2.2-.3.2-.6.1-.3-.2-1.2-.5-2.3-1.4-.9-.8-1.4-1.7-1.6-2-.2-.3 0-.5.1-.6l.5-.6c.1-.2.2-.3.3-.5 0-.2 0-.4 0-.5 0-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5 4.5.7.3 1.3.5 1.7.6.7.2 1.3.2 1.8.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2z" />
                    </svg>
                    WhatsApp
                </button>

                <button
                    type="button"
                    onClick={() => open('facebook')}
                    className="flex items-center gap-2 px-3 py-2 rounded-lg bg-[#1877F2] hover:bg-[#1465d8] text-white text-sm font-medium transition-colors"
                >
                    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.7l-.4 2.9h-2.3v7A10 10 0 0022 12z" />
                    </svg>
                    Facebook
                </button>

                <button
                    type="button"
                    onClick={() => open('telegram')}
                    className="flex items-center gap-2 px-3 py-2 rounded-lg bg-[#0088cc] hover:bg-[#0077b3] text-white text-sm font-medium transition-colors"
                >
                    <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 100 20 10 10 0 000-20m4.6 6.8-1.6 7.6c-.1.5-.4.7-.9.4l-2.4-1.8-1.2 1.1c-.1.1-.3.3-.5.3l.2-2.5 4.5-4.1c.2-.2 0-.3-.3-.1l-5.6 3.5-2.4-.7c-.5-.2-.5-.5.1-.8l9.4-3.6c.4-.2.8.1.7.7z" />
                    </svg>
                    Telegram
                </button>

                <button
                    type="button"
                    onClick={copier}
                    className={`flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium border transition-colors ${
                        copied
                            ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                            : 'bg-white border-zinc-200 text-zinc-700 hover:bg-zinc-50'
                    }`}
                >
                    {copied ? <Check className="w-4 h-4" /> : <LinkIcon className="w-4 h-4" />}
                    {copied ? 'Lien copié' : 'Copier le lien'}
                </button>
            </div>
        </div>
    );
}

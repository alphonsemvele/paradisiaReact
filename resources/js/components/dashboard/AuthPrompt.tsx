import { router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, LogIn, UserPlus, ThumbsUp, MessageCircle } from 'lucide-react';

interface Props {
    open: boolean;
    onClose: () => void;
    /** Action que le visiteur tentait de faire : adapte le message. */
    action?: 'like' | 'comment' | 'share' | null;
}

const MESSAGES: Record<string, { icon: any; titre: string; texte: string }> = {
    like: { icon: ThumbsUp, titre: 'Aimez cette publication', texte: 'Connectez-vous pour aimer et soutenir les participants.' },
    comment: { icon: MessageCircle, titre: 'Rejoignez la conversation', texte: 'Connectez-vous pour commenter les publications.' },
    share: { icon: MessageCircle, titre: 'Partagez cette publication', texte: 'Connectez-vous pour interagir avec la communauté.' },
};

/**
 * Invite instantanée à se connecter / créer un compte, affichée quand un
 * visiteur non connecté tente une action (like, commentaire…). Purement
 * côté client : aucune navigation tant que l'utilisateur ne choisit pas.
 */
export default function AuthPrompt({ open, onClose, action }: Props) {
    const conf = (action && MESSAGES[action]) || {
        icon: LogIn,
        titre: 'Connectez-vous pour continuer',
        texte: 'Créez un compte ou connectez-vous pour interagir sur PARADISIA.',
    };
    const Icon = conf.icon;

    return (
        <AnimatePresence>
            {open && (
                <motion.div
                    initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                    onClick={onClose}
                    className="fixed inset-0 z-[60] bg-black/60 flex items-center justify-center p-4"
                >
                    <motion.div
                        initial={{ scale: 0.9, y: 20 }} animate={{ scale: 1, y: 0 }} exit={{ scale: 0.95, y: 10 }}
                        transition={{ type: 'spring', stiffness: 300, damping: 26 }}
                        onClick={(e) => e.stopPropagation()}
                        className="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl"
                    >
                        <div className="relative px-6 pt-8 pb-6 text-center bg-gradient-to-b from-emerald-50 to-white">
                            <button onClick={onClose} className="absolute top-3 right-3 p-2 hover:bg-white/60 rounded-full">
                                <X className="w-5 h-5 text-zinc-500" />
                            </button>
                            <div className="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                                <Icon className="w-8 h-8 text-emerald-600" />
                            </div>
                            <h3 className="text-lg font-bold text-zinc-900">{conf.titre}</h3>
                            <p className="mt-2 text-sm text-zinc-600">{conf.texte}</p>
                        </div>
                        <div className="px-6 pb-6 flex flex-col gap-2">
                            <button
                                onClick={() => router.visit('/login')}
                                className="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold transition-colors"
                            >
                                <LogIn className="w-4 h-4" /> Se connecter
                            </button>
                            <button
                                onClick={() => router.visit('/register')}
                                className="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-sm font-semibold transition-colors"
                            >
                                <UserPlus className="w-4 h-4" /> Créer un compte
                            </button>
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}

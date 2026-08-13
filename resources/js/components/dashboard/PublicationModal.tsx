import { useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X } from 'lucide-react';
import PublicationCard from './PublicationCard';
import type { Publication, User } from '@/types';

interface Props {
    publication: Publication;
    currentUser: User | null;
    onClose: () => void;
    onShare: () => void;
}

export default function PublicationModal({
    publication,
    currentUser,
    onClose,
    onShare,
}: Props) {
    // Bloquer le scroll du body quand le modal est ouvert
    useEffect(() => {
        document.body.style.overflow = 'hidden';

        const handleEscape = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
        };
        window.addEventListener('keydown', handleEscape);

        return () => {
            document.body.style.overflow = 'auto';
            window.removeEventListener('keydown', handleEscape);
        };
    }, [onClose]);

    return (
        <AnimatePresence>
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                className="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-start justify-center py-8 px-4"
                onClick={(e) => e.target === e.currentTarget && onClose()}
            >
                <motion.div
                    initial={{ opacity: 0, scale: 0.95, y: 20 }}
                    animate={{ opacity: 1, scale: 1, y: 0 }}
                    exit={{ opacity: 0, scale: 0.95, y: 20 }}
                    transition={{ duration: 0.2 }}
                    className="relative w-full max-w-2xl"
                >
                    {/* Header avec bouton fermer */}
                    <div className="flex items-center justify-between mb-3">
                        <div className="flex items-center gap-2 px-3 py-1.5 bg-white/15 backdrop-blur-md border border-white/30 rounded-full">
                            <span className="w-2 h-2 bg-emerald-400 rounded-full animate-pulse" />
                            <span className="text-xs font-medium text-white">
                                Publication partagée
                            </span>
                        </div>

                        <button
                            onClick={onClose}
                            className="p-2 bg-white/15 backdrop-blur-md hover:bg-white/25 rounded-full transition-all border border-white/30"
                        >
                            <X className="w-5 h-5 text-white" />
                        </button>
                    </div>

                    {/* Publication card (commentaires ouverts, façon Facebook) */}
                    <PublicationCard
                        publication={publication}
                        currentUser={currentUser}
                        onShare={onShare}
                        defaultShowComments
                    />
                </motion.div>
            </motion.div>
        </AnimatePresence>
    );
}
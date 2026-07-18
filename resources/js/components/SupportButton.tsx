import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Phone, MessageCircle, HelpCircle } from 'lucide-react';

/** Contact d'assistance PARADISIA. */
const WHATSAPP = '237687984282';
const AFFICHAGE = '+237 687 98 42 82';

/**
 * Bouton d'assistance flottant, présent sur tout le site.
 *
 * Placé en bas à GAUCHE : le panier occupe déjà le coin bas-droite.
 */
export default function SupportButton() {
    const [ouvert, setOuvert] = useState(false);

    const message = encodeURIComponent(
        "Bonjour PARADISIA, j'ai besoin d'assistance."
    );

    return (
        <>
            <AnimatePresence>
                {ouvert && (
                    <motion.div
                        initial={{ opacity: 0, y: 10, scale: 0.96 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 10, scale: 0.96 }}
                        className="fixed bottom-24 left-6 z-40 w-72 rounded-2xl bg-white shadow-2xl border border-zinc-200 overflow-hidden"
                    >
                        <div className="bg-gradient-to-br from-emerald-500 to-teal-600 px-4 py-3 text-white">
                            <p className="font-semibold text-sm">Besoin d'aide ?</p>
                            <p className="text-xs text-emerald-50">
                                Notre équipe vous répond rapidement
                            </p>
                        </div>

                        <div className="p-3 space-y-2">
                            <a
                                href={`https://wa.me/${WHATSAPP}?text=${message}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center gap-3 rounded-xl border border-zinc-200 px-3 py-2.5 hover:bg-emerald-50 hover:border-emerald-200 transition-colors"
                            >
                                <span className="w-9 h-9 rounded-full bg-[#25D366] flex items-center justify-center flex-shrink-0">
                                    <MessageCircle className="w-4 h-4 text-white" />
                                </span>
                                <span className="min-w-0">
                                    <span className="block text-sm font-medium text-zinc-800">WhatsApp</span>
                                    <span className="block text-xs text-zinc-500 truncate">{AFFICHAGE}</span>
                                </span>
                            </a>

                            <a
                                href={`tel:+${WHATSAPP}`}
                                className="flex items-center gap-3 rounded-xl border border-zinc-200 px-3 py-2.5 hover:bg-zinc-50 transition-colors"
                            >
                                <span className="w-9 h-9 rounded-full bg-zinc-100 flex items-center justify-center flex-shrink-0">
                                    <Phone className="w-4 h-4 text-zinc-600" />
                                </span>
                                <span className="min-w-0">
                                    <span className="block text-sm font-medium text-zinc-800">Nous appeler</span>
                                    <span className="block text-xs text-zinc-500 truncate">{AFFICHAGE}</span>
                                </span>
                            </a>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>

            <motion.button
                initial={{ scale: 0, opacity: 0 }}
                animate={{ scale: 1, opacity: 1 }}
                transition={{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }}
                onClick={() => setOuvert((o) => !o)}
                aria-label="Assistance"
                className="fixed bottom-6 left-6 z-40 w-14 h-14 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white shadow-xl shadow-emerald-600/25 flex items-center justify-center transition-colors group"
            >
                {ouvert ? (
                    <X className="w-6 h-6" />
                ) : (
                    <HelpCircle className="w-6 h-6 group-hover:scale-110 transition-transform" />
                )}
            </motion.button>
        </>
    );
}

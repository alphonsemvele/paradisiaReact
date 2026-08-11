import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { MessageCircle } from 'lucide-react';

/** Bouton messagerie flottant (bas-droite, au-dessus du bouton boutique). */
export default function MessagesButton() {
    const [nonLus, setNonLus] = useState(0);
    const surMessagerie = usePage().url.startsWith('/messages');

    useEffect(() => {
        const charger = () =>
            fetch('/messages/non-lus', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then((r) => (r.ok ? r.json() : null))
                .then((d) => d && setNonLus(d.non_lus ?? 0))
                .catch(() => {});
        charger();
        const t = setInterval(charger, 30000);
        return () => clearInterval(t);
    }, []);

    if (surMessagerie) return null;

    return (
        <motion.div
            initial={{ scale: 0, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ type: 'spring', stiffness: 300, damping: 25 }}
            className="fixed bottom-24 right-6 z-40"
        >
            <Link
                href="/messages"
                aria-label="Messagerie"
                className="relative w-14 h-14 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl shadow-xl shadow-emerald-900/20 flex items-center justify-center transition-colors group"
            >
                <MessageCircle className="w-6 h-6 group-hover:scale-110 transition-transform" />
                {nonLus > 0 && (
                    <span className="absolute -top-1.5 -right-1.5 min-w-[22px] h-[22px] px-1.5 bg-red-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center ring-2 ring-white">
                        {nonLus > 99 ? '99+' : nonLus}
                    </span>
                )}
            </Link>
        </motion.div>
    );
}

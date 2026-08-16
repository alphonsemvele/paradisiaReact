import { useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { Bell, MessageCircle } from 'lucide-react';

interface Notif {
    id: number;
    type: string;
    body: string;
    lien: string | null;
    lue: boolean;
    date: string;
}

/** Cloche de notifications du header (utilisateur connecté). */
export default function NotificationBell() {
    const [ouvert, setOuvert] = useState(false);
    const [notifs, setNotifs] = useState<Notif[]>([]);
    const [nonLues, setNonLues] = useState(0);
    const ref = useRef<HTMLDivElement>(null);

    const charger = () =>
        fetch('/notifications/liste', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then((r) => (r.ok ? r.json() : null))
            .then((d) => {
                if (!d) return;
                setNotifs(d.notifications ?? []);
                setNonLues(d.non_lues ?? 0);
            })
            .catch(() => {});

    // Chargement initial + rafraîchissement discret toutes les 60 s.
    useEffect(() => {
        charger();
        const t = setInterval(charger, 60000);
        return () => clearInterval(t);
    }, []);

    // Fermeture au clic extérieur.
    useEffect(() => {
        const h = (e: MouseEvent) => {
            if (ref.current && !ref.current.contains(e.target as Node)) setOuvert(false);
        };
        if (ouvert) {
            document.addEventListener('mousedown', h);
            return () => document.removeEventListener('mousedown', h);
        }
    }, [ouvert]);

    const ouvrir = () => {
        setOuvert((v) => !v);
        if (!ouvert && nonLues > 0) {
            // Marque tout lu à l'ouverture (optimiste).
            setNonLues(0);
            setNotifs((ns) => ns.map((n) => ({ ...n, lue: true })));
            fetch('/notifications/lire-tout', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            }).catch(() => {});
        }
    };

    return (
        <div className="relative" ref={ref}>
            <button
                onClick={ouvrir}
                className="relative p-2 rounded-full hover:bg-zinc-100 transition-colors"
                aria-label="Notifications"
            >
                <Bell className="w-5 h-5 text-zinc-600" />
                {nonLues > 0 && (
                    <span className="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white">
                        {nonLues > 9 ? '9+' : nonLues}
                    </span>
                )}
            </button>

            <AnimatePresence>
                {ouvert && (
                    <motion.div
                        initial={{ opacity: 0, y: -8, scale: 0.98 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: -8, scale: 0.98 }}
                        className="absolute right-0 top-full mt-2 w-80 max-w-[90vw] bg-white rounded-2xl shadow-xl border border-zinc-200 overflow-hidden z-50"
                    >
                        <div className="px-4 py-3 border-b border-zinc-100">
                            <p className="font-semibold text-zinc-900 text-sm">Notifications</p>
                        </div>
                        <div className="max-h-96 overflow-y-auto">
                            {notifs.length === 0 ? (
                                <p className="px-4 py-8 text-center text-sm text-zinc-400">Aucune notification</p>
                            ) : (
                                notifs.map((n) => (
                                    <button
                                        key={n.id}
                                        onClick={() => n.lien && router.visit(n.lien)}
                                        className={`w-full text-left px-4 py-3 flex items-start gap-3 hover:bg-zinc-50 border-b border-zinc-50 transition-colors ${
                                            n.lue ? '' : 'bg-emerald-50/40'
                                        }`}
                                    >
                                        <span className="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                            <MessageCircle className="w-4 h-4 text-emerald-600" />
                                        </span>
                                        <span className="min-w-0">
                                            <span className="block text-sm text-zinc-800 leading-snug">{n.body}</span>
                                            <span className="block text-[11px] text-zinc-400 mt-0.5">{n.date}</span>
                                        </span>
                                    </button>
                                ))
                            )}
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}

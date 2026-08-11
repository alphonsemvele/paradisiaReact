import { useEffect, useState } from 'react';
import { Link } from '@inertiajs/react';
import { MessageCircle } from 'lucide-react';

/** Bouton messagerie du header, avec pastille de messages non lus. */
export default function MessagesButton() {
    const [nonLus, setNonLus] = useState(0);

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

    return (
        <Link href="/messages" className="relative p-2 rounded-full hover:bg-zinc-100 transition-colors" aria-label="Messages">
            <MessageCircle className="w-5 h-5 text-zinc-600" />
            {nonLus > 0 && (
                <span className="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white">
                    {nonLus > 9 ? '9+' : nonLus}
                </span>
            )}
        </Link>
    );
}

import { useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/components/layouts/AppLayout';
import { Send, ArrowLeft, MessageCircle, Search } from 'lucide-react';

interface Autre { id: number; name: string; photo: string | null }
interface Msg { id: number; de_moi: boolean; body: string; date: string }
interface Conv { id: number; autre: Autre; dernier: string; de_moi: boolean; date: string; non_lus: number }
interface Active { id: number; autre: Autre; messages: Msg[] }
interface Props { conversations: Conv[]; active: Active | null }

const xsrf = () => decodeURIComponent(document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? '');

function Avatar({ u, size = 44 }: { u: Autre; size?: number }) {
    return u.photo
        ? <img src={u.photo} alt={u.name} className="rounded-full object-cover flex-shrink-0" style={{ width: size, height: size }} />
        : <div className="rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center flex-shrink-0" style={{ width: size, height: size, fontSize: size * 0.4 }}>{u.name.charAt(0).toUpperCase()}</div>;
}

export default function Messages({ conversations, active }: Props) {
    const [messages, setMessages] = useState<Msg[]>(active?.messages ?? []);
    const [body, setBody] = useState('');
    const [envoi, setEnvoi] = useState(false);
    const [recherche, setRecherche] = useState('');
    const filEnd = useRef<HTMLDivElement>(null);

    // Ré-initialise quand on change de conversation.
    useEffect(() => { setMessages(active?.messages ?? []); }, [active?.id]);

    const scrollBas = () => filEnd.current?.scrollIntoView({ behavior: 'smooth' });
    useEffect(() => { scrollBas(); }, [messages.length]);

    // Polling des nouveaux messages entrants.
    useEffect(() => {
        if (!active) return;
        const t = setInterval(async () => {
            const dernier = messages.length ? messages[messages.length - 1].id : 0;
            try {
                const r = await fetch(`/messages/${active.id}/poll?after=${dernier}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (!r.ok) return;
                const d = await r.json();
                if (d.messages?.length) {
                    setMessages((prev) => {
                        const ids = new Set(prev.map((m) => m.id));
                        return [...prev, ...d.messages.filter((m: Msg) => !ids.has(m.id))];
                    });
                }
            } catch { /* silencieux */ }
        }, 4000);
        return () => clearInterval(t);
    }, [active?.id, messages]);

    const envoyer = async (e: React.FormEvent) => {
        e.preventDefault();
        const texte = body.trim();
        if (!texte || !active || envoi) return;
        setEnvoi(true);
        setBody('');
        try {
            const r = await fetch(`/messages/${active.id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': xsrf(), 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ body: texte }),
            });
            if (r.ok) {
                const d = await r.json();
                setMessages((prev) => (prev.some((m) => m.id === d.message.id) ? prev : [...prev, d.message]));
            } else {
                setBody(texte);
            }
        } catch { setBody(texte); }
        finally { setEnvoi(false); }
    };

    const convsFiltrees = conversations.filter((c) => c.autre.name.toLowerCase().includes(recherche.toLowerCase()));

    return (
        <AppLayout>
            <Head title="Messagerie — Paradisia" />

            <div className="max-w-5xl mx-auto md:px-4 md:py-6">
                <div className="bg-white md:rounded-2xl md:border border-zinc-200 md:shadow-sm overflow-hidden flex" style={{ height: 'calc(100vh - 8rem)' }}>
                    {/* Liste des conversations */}
                    <div className={`w-full md:w-80 md:border-r border-zinc-200 flex-col ${active ? 'hidden md:flex' : 'flex'}`}>
                        <div className="p-3 border-b border-zinc-100">
                            <h1 className="font-bold text-zinc-900 px-1 mb-2">Messages</h1>
                            <div className="relative">
                                <Search className="absolute left-3 top-2.5 w-4 h-4 text-zinc-400" />
                                <input value={recherche} onChange={(e) => setRecherche(e.target.value)} placeholder="Rechercher…"
                                    className="w-full pl-9 pr-3 py-2 bg-zinc-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            {convsFiltrees.length === 0 && (
                                <p className="text-center text-sm text-zinc-400 py-10 px-4">Aucune conversation.<br />Écris à quelqu'un depuis son profil.</p>
                            )}
                            {convsFiltrees.map((c) => (
                                <Link key={c.id} href={`/messages/${c.id}`} preserveScroll
                                    className={`flex items-center gap-3 px-3 py-3 hover:bg-zinc-50 border-b border-zinc-50 ${active?.id === c.id ? 'bg-emerald-50/50' : ''}`}>
                                    <Avatar u={c.autre} />
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center justify-between gap-2">
                                            <p className="font-semibold text-sm text-zinc-900 truncate">{c.autre.name}</p>
                                            <span className="text-[11px] text-zinc-400 flex-shrink-0">{c.date}</span>
                                        </div>
                                        <div className="flex items-center justify-between gap-2">
                                            <p className="text-xs text-zinc-500 truncate">{c.de_moi && 'Vous : '}{c.dernier}</p>
                                            {c.non_lus > 0 && <span className="flex-shrink-0 min-w-[18px] h-[18px] px-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{c.non_lus}</span>}
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>

                    {/* Fil de discussion */}
                    <div className={`flex-1 flex-col ${active ? 'flex' : 'hidden md:flex'}`}>
                        {active ? (
                            <>
                                <div className="flex items-center gap-3 px-3 py-3 border-b border-zinc-100 bg-white">
                                    <button onClick={() => router.visit('/messages')} className="md:hidden p-1 text-zinc-500"><ArrowLeft className="w-5 h-5" /></button>
                                    <Link href={`/u/${active.autre.id}`} className="flex items-center gap-3 min-w-0">
                                        <Avatar u={active.autre} size={38} />
                                        <p className="font-semibold text-zinc-900 truncate">{active.autre.name}</p>
                                    </Link>
                                </div>

                                <div className="flex-1 overflow-y-auto px-3 py-4 space-y-1.5" style={{ background: '#f7f7f5' }}>
                                    {messages.map((m) => (
                                        <div key={m.id} className={`flex ${m.de_moi ? 'justify-end' : 'justify-start'}`}>
                                            <div className={`max-w-[78%] px-3 py-2 rounded-2xl text-sm leading-snug whitespace-pre-wrap break-words ${m.de_moi ? 'bg-emerald-500 text-white rounded-br-md' : 'bg-white text-zinc-800 rounded-bl-md shadow-sm'}`}>
                                                {m.body}
                                                <span className={`block text-[10px] mt-0.5 text-right ${m.de_moi ? 'text-emerald-50/80' : 'text-zinc-400'}`}>{m.date}</span>
                                            </div>
                                        </div>
                                    ))}
                                    {messages.length === 0 && <p className="text-center text-sm text-zinc-400 py-10">Démarre la conversation 👋</p>}
                                    <div ref={filEnd} />
                                </div>

                                <form onSubmit={envoyer} className="flex items-center gap-2 p-3 border-t border-zinc-100 bg-white">
                                    <input value={body} onChange={(e) => setBody(e.target.value)} placeholder="Écris un message…" autoFocus
                                        className="flex-1 px-4 py-2.5 bg-zinc-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                                    <button type="submit" disabled={!body.trim() || envoi}
                                        className="w-11 h-11 rounded-full bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white flex items-center justify-center flex-shrink-0">
                                        <Send className="w-5 h-5" />
                                    </button>
                                </form>
                            </>
                        ) : (
                            <div className="flex-1 flex flex-col items-center justify-center text-zinc-400">
                                <MessageCircle className="w-14 h-14 mb-3 opacity-40" />
                                <p className="text-sm">Choisis une conversation</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

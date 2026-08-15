import { useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/components/layouts/AppLayout';
import { Send, ArrowLeft, MessageCircle, Search, Smile, Check, CheckCheck, Download } from 'lucide-react';

interface Autre { id: number; name: string; photo: string | null; ville?: string | null }
interface Msg { id: number; de_moi: boolean; body: string; date: string; jour: string; lu: boolean }

const EMOJIS = [
    '😀', '😁', '😂', '🤣', '😊', '😍', '😘', '😎', '🤩', '🥳', '😉', '🙃', '😅', '😇', '🥰', '😋',
    '🤔', '🤗', '🤭', '😴', '😜', '😝', '🤪', '😏', '😒', '🙄', '😬', '😳', '🥺', '😢', '😭', '😤',
    '😠', '😡', '🤬', '😱', '😨', '😰', '😥', '🤯', '😷', '🤒', '🤕', '🤢', '🥴', '😵', '🤠', '🤡',
    '👍', '👎', '👏', '🙌', '🙏', '💪', '👌', '✌️', '🤞', '🤝', '👋', '🫶', '❤️', '🔥', '✨', '🎉',
    '💯', '💥', '⭐', '🌟', '🍍', '🍋', '🍊', '🥭', '🍹', '⚽', '🏆', '🎁', '💚', '😻', '👀', '🚀',
];
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
    const [personnes, setPersonnes] = useState<Autre[]>([]);
    const [emojisOuvert, setEmojisOuvert] = useState(false);
    const filEnd = useRef<HTMLDivElement>(null);
    const zoneSaisie = useRef<HTMLTextAreaElement>(null);

    // PWA : installation du « tchat » sur l'écran d'accueil.
    const [promptInstall, setPromptInstall] = useState<any>(null);
    const [installe, setInstalle] = useState(false);
    const [iOS, setIOS] = useState(false);
    const [aideIOS, setAideIOS] = useState(false);

    useEffect(() => {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
        const capter = (e: any) => { e.preventDefault(); setPromptInstall(e); };
        const installee = () => { setInstalle(true); setPromptInstall(null); };
        window.addEventListener('beforeinstallprompt', capter);
        window.addEventListener('appinstalled', installee);

        const standalone = window.matchMedia('(display-mode: standalone)').matches || (navigator as any).standalone === true;
        setInstalle(standalone);
        setIOS(/iphone|ipad|ipod/i.test(navigator.userAgent) && !(navigator as any).standalone);

        return () => {
            window.removeEventListener('beforeinstallprompt', capter);
            window.removeEventListener('appinstalled', installee);
        };
    }, []);

    const installer = async () => {
        if (promptInstall) {
            promptInstall.prompt();
            const { outcome } = await promptInstall.userChoice;
            if (outcome === 'accepted') setInstalle(true);
            setPromptInstall(null);
        } else if (iOS) {
            setAideIOS(true);
        } else {
            setAideIOS(true);
        }
    };
    const peutInstaller = !installe;

    // Recherche de personnes à qui écrire (debounce).
    useEffect(() => {
        const t = setTimeout(async () => {
            try {
                const r = await fetch(`/messages/recherche?q=${encodeURIComponent(recherche)}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (r.ok) { const d = await r.json(); setPersonnes(d.users ?? []); }
            } catch { /* silencieux */ }
        }, 250);
        return () => clearTimeout(t);
    }, [recherche]);

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
                setMessages((prev) => {
                    let next = prev;
                    if (d.messages?.length) {
                        const ids = new Set(prev.map((m) => m.id));
                        next = [...prev, ...d.messages.filter((m: Msg) => !ids.has(m.id))];
                    }
                    // Accusés de lecture : mes messages ≤ lu_jusqua passent en « lu ».
                    if (d.lu_jusqua) {
                        next = next.map((m) => (m.de_moi && !m.lu && m.id <= d.lu_jusqua ? { ...m, lu: true } : m));
                    }
                    return next;
                });
            } catch { /* silencieux */ }
        }, 4000);
        return () => clearInterval(t);
    }, [active?.id, messages]);

    const autoGrow = () => {
        const el = zoneSaisie.current;
        if (el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 128) + 'px'; }
    };
    const insererEmoji = (em: string) => {
        setBody((b) => b + em);
        setTimeout(() => { zoneSaisie.current?.focus(); autoGrow(); }, 0);
    };
    const surTouche = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); envoyer(e); }
    };

    const envoyer = async (e: React.FormEvent) => {
        e.preventDefault();
        const texte = body.trim();
        if (!texte || !active || envoi) return;
        setEnvoi(true);
        setBody('');
        setEmojisOuvert(false);
        if (zoneSaisie.current) zoneSaisie.current.style.height = 'auto';
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
    const partenaires = new Set(conversations.map((c) => c.autre.id));
    const personnesAffichees = personnes.filter((p) => !partenaires.has(p.id)).slice(0, recherche ? 20 : 8);

    return (
        <AppLayout>
            <Head title="Messagerie — Paradisia">
                <link rel="manifest" href="/manifest-chat.json" />
                <meta name="theme-color" content="#10b981" />
                <meta name="mobile-web-app-capable" content="yes" />
                <meta name="apple-mobile-web-app-capable" content="yes" />
                <meta name="apple-mobile-web-app-status-bar-style" content="default" />
                <meta name="apple-mobile-web-app-title" content="Paradisia Chat" />
                <link rel="apple-touch-icon" href="/pwa-icon.png" />
            </Head>

            <div className="messagerie-app max-w-5xl mx-auto md:px-4 md:py-6">
                <div className="bg-white md:rounded-2xl md:border border-zinc-200 md:shadow-sm overflow-hidden flex" style={{ height: 'calc(100vh - 8rem)' }}>
                    {/* Liste des conversations */}
                    <div className={`w-full md:w-80 md:border-r border-zinc-200 flex-col ${active ? 'hidden md:flex' : 'flex'}`}>
                        <div className="p-3 border-b border-zinc-100">
                            <div className="flex items-center justify-between px-1 mb-2">
                                <h1 className="font-bold text-zinc-900">Messages</h1>
                                {peutInstaller && (
                                    <button onClick={installer}
                                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-full px-3 py-1.5 transition-colors">
                                        <Download className="w-3.5 h-3.5" /> Installer le tchat
                                    </button>
                                )}
                            </div>
                            <div className="relative">
                                <Search className="absolute left-3 top-2.5 w-4 h-4 text-zinc-400" />
                                <input value={recherche} onChange={(e) => setRecherche(e.target.value)} placeholder="Rechercher…"
                                    className="w-full pl-9 pr-3 py-2 bg-zinc-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto">
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

                            {/* Personnes à qui écrire */}
                            {personnesAffichees.length > 0 && (
                                <>
                                    <p className="px-3 pt-3 pb-1 text-[11px] font-bold text-zinc-400 uppercase tracking-wide">
                                        {recherche ? 'Résultats' : 'Écrire à quelqu\'un'}
                                    </p>
                                    {personnesAffichees.map((p) => (
                                        <button key={p.id} onClick={() => router.visit(`/messages/u/${p.id}`)}
                                            className="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-zinc-50 border-b border-zinc-50 text-left">
                                            <Avatar u={p} size={40} />
                                            <div className="flex-1 min-w-0">
                                                <p className="font-semibold text-sm text-zinc-900 truncate">{p.name}</p>
                                                {p.ville && <p className="text-xs text-zinc-400 truncate">{p.ville}</p>}
                                            </div>
                                            <Send className="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                        </button>
                                    ))}
                                </>
                            )}

                            {convsFiltrees.length === 0 && personnesAffichees.length === 0 && (
                                <p className="text-center text-sm text-zinc-400 py-10 px-4">Recherche une personne pour lui écrire.</p>
                            )}
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

                                <div className="flex-1 overflow-y-auto px-3 py-4 space-y-1" style={{ background: '#eae6df' }}>
                                    {messages.map((m, i) => {
                                        const nouveauJour = m.jour && m.jour !== (i > 0 ? messages[i - 1].jour : null);
                                        return (
                                            <div key={m.id}>
                                                {nouveauJour && (
                                                    <div className="flex justify-center my-3">
                                                        <span className="text-[11px] font-medium text-zinc-600 bg-white/80 rounded-full px-3 py-1 shadow-sm">{m.jour}</span>
                                                    </div>
                                                )}
                                                <div className={`flex ${m.de_moi ? 'justify-end' : 'justify-start'}`}>
                                                    <div className={`max-w-[80%] px-2.5 py-1.5 rounded-2xl text-sm leading-snug whitespace-pre-wrap break-words shadow-sm ${m.de_moi ? 'bg-emerald-500 text-white rounded-br-sm' : 'bg-white text-zinc-800 rounded-bl-sm'}`}>
                                                        <span>{m.body}</span>
                                                        <span className={`inline-flex items-center gap-0.5 align-bottom ml-2 text-[10px] float-right mt-1 ${m.de_moi ? 'text-emerald-50/90' : 'text-zinc-400'}`}>
                                                            {m.date}
                                                            {m.de_moi && (m.lu
                                                                ? <CheckCheck className="w-3.5 h-3.5 text-sky-200" />
                                                                : <Check className="w-3.5 h-3.5 text-emerald-100/80" />)}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                    {messages.length === 0 && <p className="text-center text-sm text-zinc-500 py-10">Démarre la conversation 👋</p>}
                                    <div ref={filEnd} />
                                </div>

                                <form onSubmit={envoyer} className="relative flex items-end gap-1.5 p-2.5 border-t border-zinc-100 bg-white"
                                    style={{ paddingBottom: 'calc(0.625rem + env(safe-area-inset-bottom))' }}>
                                    {emojisOuvert && (
                                        <div className="absolute bottom-16 left-2.5 w-72 max-w-[90%] bg-white border border-zinc-200 rounded-2xl shadow-xl p-2 grid grid-cols-8 gap-0.5 max-h-52 overflow-y-auto z-10">
                                            {EMOJIS.map((em) => (
                                                <button type="button" key={em} onClick={() => insererEmoji(em)} className="text-xl hover:bg-zinc-100 rounded-lg p-1 leading-none">{em}</button>
                                            ))}
                                        </div>
                                    )}
                                    <button type="button" onClick={() => setEmojisOuvert((v) => !v)}
                                        className={`w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 transition-colors ${emojisOuvert ? 'text-emerald-600 bg-emerald-50' : 'text-zinc-500 hover:bg-zinc-100'}`}>
                                        <Smile className="w-6 h-6" />
                                    </button>
                                    <textarea ref={zoneSaisie} value={body} rows={1} placeholder="Écris un message…" autoFocus
                                        onChange={(e) => { setBody(e.target.value); autoGrow(); }} onKeyDown={surTouche}
                                        className="flex-1 px-4 py-2.5 bg-zinc-100 rounded-2xl resize-none max-h-32 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        style={{ fontSize: 16 }} />
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

            {/* Aide installation iOS (pas de prompt natif sur iPhone) */}
            {aideIOS && (
                <div className="fixed inset-0 z-[60] bg-black/50 flex items-end sm:items-center justify-center p-4" onClick={() => setAideIOS(false)}>
                    <div className="bg-white rounded-2xl w-full max-w-sm p-6 text-center" onClick={(e) => e.stopPropagation()}>
                        <div className="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-3">
                            <Download className="w-7 h-7 text-emerald-600" />
                        </div>
                        <h3 className="font-bold text-zinc-900 text-lg">Installer le tchat</h3>
                        {iOS ? (
                            <p className="mt-2 text-sm text-zinc-600">
                                Sur iPhone : appuie sur le bouton <b>Partager</b> <span className="inline-block">⬆️</span> de Safari,
                                puis choisis <b>« Sur l'écran d'accueil »</b>. Le tchat s'ouvrira en plein écran comme une vraie app.
                            </p>
                        ) : (
                            <p className="mt-2 text-sm text-zinc-600">
                                Ouvre le menu de ton navigateur (⋮) puis <b>« Installer l'application »</b> / <b>« Ajouter à l'écran d'accueil »</b>.
                            </p>
                        )}
                        <button onClick={() => setAideIOS(false)} className="mt-5 w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm">
                            J'ai compris
                        </button>
                    </div>
                </div>
            )}

            <style>{`.messagerie-app, .messagerie-app *{ -webkit-tap-highlight-color: transparent; } .messagerie-app button, .messagerie-app h1{ user-select: none; }`}</style>
        </AppLayout>
    );
}

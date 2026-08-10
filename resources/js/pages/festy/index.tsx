import { useState } from 'react';
import { Head } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import AppLayout from '@/components/layouts/AppLayout';
import { Trophy, Users, CheckCircle2, MessageCircle, User, Phone, Mail, MapPin, Home, PartyPopper, ChevronDown } from 'lucide-react';

interface Equipe { id: number; nom: string; trait: string | null; couleur: string; emoji: string | null; image: string | null; membres: number }
interface Props {
    festy: { titre: string; sous_titre: string | null; date_label: string | null; prix: string | null; description: string | null; inscriptions_ouvertes: boolean };
    equipes: Equipe[];
}

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

/* Vraies images des fruits (SVG inline, papaye comprise — aucun emoji papaye n'existe). */
function Fruit({ nom, size = 40 }: { nom: string; size?: number }) {
    const k = nom.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
    const p = { width: size, height: size, viewBox: '0 0 64 64', xmlns: 'http://www.w3.org/2000/svg' };
    switch (k) {
        case 'ananas':
            return (
                <svg {...p}>
                    <g fill="#3f9d4f">
                        <path d="M32 6 L28 22 L32 18 L36 22 Z" />
                        <path d="M32 7 L21 17 L29 20 L31 14 Z" />
                        <path d="M32 7 L43 17 L35 20 L33 14 Z" />
                    </g>
                    <ellipse cx="32" cy="41" rx="15" ry="19" fill="#f2b705" />
                    <g stroke="#cf9500" strokeWidth="1.6" strokeLinecap="round">
                        <line x1="22" y1="27" x2="43" y2="47" /><line x1="32" y1="25" x2="46" y2="42" /><line x1="18" y1="35" x2="35" y2="57" />
                        <line x1="42" y1="27" x2="21" y2="47" /><line x1="32" y1="25" x2="18" y2="42" /><line x1="46" y1="35" x2="29" y2="57" />
                    </g>
                </svg>
            );
        case 'citron':
            return (
                <svg {...p}>
                    <path d="M40 18c7-6 13-5 15-3-2 7-9 9-15 3z" fill="#4aa657" />
                    <ellipse cx="31" cy="37" rx="21" ry="15" fill="#f7e017" />
                    <ellipse cx="31" cy="37" rx="21" ry="15" fill="none" stroke="#e0c800" strokeWidth="1.5" />
                    <circle cx="10.5" cy="37" r="2.5" fill="#e0c800" /><circle cx="51.5" cy="37" r="2.5" fill="#e0c800" />
                </svg>
            );
        case 'gingembre':
            return (
                <svg {...p}>
                    <path d="M16 42c-5-5-1-15 6-15 1-9 13-11 18-3 9-1 12 9 6 14 3 8-6 15-13 11-7 5-15 1-17-7z" fill="#d8a066" />
                    <path d="M22 26c2-3 6-3 8 0-3 1-6 1-8 0z" fill="#e6b782" />
                    <circle cx="24" cy="38" r="2.2" fill="#c1854c" /><circle cx="38" cy="44" r="2.2" fill="#c1854c" /><circle cx="40" cy="30" r="2" fill="#c1854c" />
                </svg>
            );
        case 'orange':
            return (
                <svg {...p}>
                    <path d="M32 16c2-6 8-8 12-7-1 6-6 9-12 7z" fill="#4aa657" />
                    <rect x="31" y="12" width="2.5" height="7" rx="1" fill="#7a5230" />
                    <circle cx="32" cy="38" r="19" fill="#ff8c1a" />
                    <circle cx="25" cy="31" r="6" fill="#ffb055" opacity="0.55" />
                </svg>
            );
        case 'papaye':
            /* Demi-papaye : peau vert-orangé, chair orange, cavité de graines noires. */
            return (
                <svg {...p}>
                    <path d="M11 24 Q32 9 53 24 Q54 26 53 28 Q32 60 11 28 Q10 26 11 24 Z" fill="#8fbf3f" />
                    <path d="M15 26 Q32 15 49 26 Q32 55 15 26 Z" fill="#f0932b" />
                    <path d="M18 27 Q32 20 46 27 Q32 40 18 27 Z" fill="#f7b06a" opacity="0.5" />
                    <g fill="#2e2016">
                        <circle cx="26" cy="29" r="2.1" /><circle cx="32" cy="27.5" r="2.1" /><circle cx="38" cy="29" r="2.1" />
                        <circle cx="29" cy="33" r="2.1" /><circle cx="35" cy="33" r="2.1" /><circle cx="32" cy="37" r="2.1" />
                    </g>
                </svg>
            );
        default:
            return <span style={{ fontSize: size * 0.7 }}>🍹</span>;
    }
}

export default function FestyIndex({ festy, equipes }: Props) {
    const [choix, setChoix] = useState<Equipe | null>(null);
    const [ouvert, setOuvert] = useState(false);
    const [form, setForm] = useState({ nom: '', prenom: '', email: '', telephone: '', ville: '', quartier: '' });
    const [erreurs, setErreurs] = useState<Record<string, string>>({});
    const [envoi, setEnvoi] = useState(false);
    const [succes, setSucces] = useState<any>(null);

    const inscrire = async () => {
        setErreurs({});
        const e: Record<string, string> = {};
        if (!choix) e.equipe = 'Choisissez une équipe.';
        if (!form.nom.trim()) e.nom = 'Le nom est requis.';
        if (!form.prenom.trim()) e.prenom = 'Le prénom est requis.';
        if (!form.telephone.trim()) e.telephone = 'Le téléphone est requis.';
        if (Object.keys(e).length) { setErreurs(e); return; }

        setEnvoi(true);
        try {
            const r = await fetch('/festy/inscription', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ festy_team_id: choix!.id, ...form }),
            });
            const d = await r.json();
            if (d.ok) setSucces(d);
            else setErreurs(d.errors ? Object.fromEntries(Object.entries(d.errors).map(([k, v]: any) => [k, v[0]])) : { global: d.message ?? 'Erreur' });
        } catch { setErreurs({ global: 'Connexion impossible.' }); }
        finally { setEnvoi(false); }
    };

    return (
        <AppLayout>
            <Head title={`${festy.titre} — Paradisia`} />

            {/* Héro */}
            <div style={{ background: 'linear-gradient(135deg,#0b2e1a,#14532d)', color: '#fff' }}>
                <div className="max-w-5xl mx-auto px-4 py-12 text-center">
                    <p className="text-amber-300 font-semibold tracking-wide">{festy.sous_titre}</p>
                    <h1 className="mt-2 text-4xl sm:text-6xl font-extrabold" style={{ letterSpacing: '-.02em' }}>{festy.titre}</h1>
                    {festy.date_label && <p className="mt-2 text-xl font-bold text-amber-400">{festy.date_label}</p>}
                    {festy.prix && (
                        <div className="mt-5 inline-flex items-center gap-2 bg-amber-400 text-emerald-950 font-extrabold px-5 py-2.5 rounded-full">
                            <Trophy className="w-5 h-5" /> Tentez de gagner {festy.prix}
                        </div>
                    )}
                    {festy.description && <p className="mt-5 max-w-2xl mx-auto text-emerald-50/90 text-sm leading-relaxed">{festy.description}</p>}
                </div>
            </div>

            <div className="max-w-md mx-auto px-4 py-10">
                <h2 className="text-2xl font-bold text-zinc-900 text-center mb-1">Inscris-toi</h2>
                <p className="text-center text-zinc-500 text-sm mb-6">Choisis ton équipe et rejoins son groupe WhatsApp.</p>

                {festy.inscriptions_ouvertes ? (
                    <div className="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6 space-y-3">
                        {/* Sélecteur d'équipe (menu déroulant avec images de fruits) */}
                        <div className="relative">
                            <span className="block text-xs font-medium text-zinc-500 mb-1">Équipe</span>
                            <button type="button" onClick={() => setOuvert(!ouvert)}
                                className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl border bg-white text-left transition-colors"
                                style={{ borderColor: choix ? choix.couleur : '#e4e4e7' }}>
                                {choix ? <Fruit nom={choix.nom} size={30} /> : <div className="w-[30px] h-[30px] rounded-lg bg-zinc-100 flex items-center justify-center"><PartyPopper className="w-4 h-4 text-zinc-400" /></div>}
                                <span className="flex-1 font-semibold text-sm" style={{ color: choix ? choix.couleur : '#a1a1aa' }}>
                                    {choix ? `Équipe ${choix.nom}` : 'Choisis ton équipe'}
                                </span>
                                <ChevronDown className={`w-4 h-4 text-zinc-400 transition-transform ${ouvert ? 'rotate-180' : ''}`} />
                            </button>
                            <AnimatePresence>
                                {ouvert && (
                                    <motion.div initial={{ opacity: 0, y: -6 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -6 }}
                                        className="absolute z-20 mt-2 w-full bg-white rounded-xl border border-zinc-200 shadow-xl overflow-hidden">
                                        {equipes.map((eq) => (
                                            <button key={eq.id} type="button" onClick={() => { setChoix(eq); setOuvert(false); setErreurs((prev) => ({ ...prev, equipe: '' })); }}
                                                className="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-zinc-50 text-left">
                                                <Fruit nom={eq.nom} size={34} />
                                                <div className="flex-1 min-w-0">
                                                    <p className="font-bold text-sm" style={{ color: eq.couleur }}>Équipe {eq.nom}</p>
                                                    {eq.trait && <p className="text-[11px] text-zinc-400">{eq.trait}</p>}
                                                </div>
                                                <span className="text-[11px] text-zinc-400 flex items-center gap-1"><Users className="w-3 h-3" />{eq.membres}</span>
                                                {choix?.id === eq.id && <CheckCircle2 className="w-4 h-4" style={{ color: eq.couleur }} />}
                                            </button>
                                        ))}
                                    </motion.div>
                                )}
                            </AnimatePresence>
                            {erreurs.equipe && <p className="text-xs text-red-600 mt-1">{erreurs.equipe}</p>}
                        </div>

                        {/* Champs */}
                        <div className="grid grid-cols-2 gap-3">
                            <Champ icon={User} err={erreurs.nom}><input className="ipt" placeholder="Nom" value={form.nom} onChange={(e) => setForm({ ...form, nom: e.target.value })} /></Champ>
                            <Champ icon={User} err={erreurs.prenom}><input className="ipt" placeholder="Prénom" value={form.prenom} onChange={(e) => setForm({ ...form, prenom: e.target.value })} /></Champ>
                        </div>
                        <Champ icon={Mail} err={erreurs.email}><input className="ipt" placeholder="E-mail (optionnel)" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} /></Champ>
                        <Champ icon={Phone} err={erreurs.telephone}><input className="ipt" placeholder="Téléphone (WhatsApp)" value={form.telephone} onChange={(e) => setForm({ ...form, telephone: e.target.value })} /></Champ>
                        <div className="grid grid-cols-2 gap-3">
                            <Champ icon={MapPin} err={erreurs.ville}><input className="ipt" placeholder="Ville" value={form.ville} onChange={(e) => setForm({ ...form, ville: e.target.value })} /></Champ>
                            <Champ icon={Home} err={erreurs.quartier}><input className="ipt" placeholder="Quartier" value={form.quartier} onChange={(e) => setForm({ ...form, quartier: e.target.value })} /></Champ>
                        </div>

                        {erreurs.global && <p className="text-red-600 text-sm">{erreurs.global}</p>}
                        <button onClick={inscrire} disabled={envoi}
                            className="w-full py-3 rounded-xl text-white font-bold transition-colors disabled:opacity-60"
                            style={{ background: choix?.couleur ?? '#14532d' }}>
                            {envoi ? 'Inscription…' : 'Rejoindre mon équipe'}
                        </button>
                    </div>
                ) : (
                    <p className="text-center text-zinc-500">Les inscriptions ne sont pas encore ouvertes.</p>
                )}
            </div>

            {/* Confirmation + groupe WhatsApp */}
            <AnimatePresence>
                {succes && (
                    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4" onClick={() => setSucces(null)}>
                        <motion.div initial={{ scale: 0.9, y: 20 }} animate={{ scale: 1, y: 0 }} onClick={(e) => e.stopPropagation()}
                            className="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl">
                            <div className="px-6 pt-8 pb-5 text-center bg-gradient-to-b from-emerald-50 to-white">
                                <div className="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3">
                                    <PartyPopper className="w-10 h-10 text-emerald-600" />
                                </div>
                                <h3 className="text-xl font-bold text-zinc-900">{succes.deja_inscrit ? 'Déjà inscrit' : 'Inscription confirmée !'}</h3>
                                <p className="mt-2 text-sm text-zinc-600">{succes.message}</p>
                            </div>
                            <div className="px-6 pb-6">
                                {succes.whatsapp ? (
                                    <a href={succes.whatsapp} target="_blank" rel="noopener noreferrer"
                                        className="flex items-center justify-center gap-2 py-3 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold transition-colors">
                                        <MessageCircle className="w-5 h-5" /> Rejoindre le groupe WhatsApp
                                    </a>
                                ) : (
                                    <p className="text-sm text-center text-zinc-500 bg-zinc-50 rounded-xl p-3">
                                        Le lien du groupe WhatsApp de l'équipe {succes.equipe} vous sera communiqué très bientôt.
                                    </p>
                                )}
                                <button onClick={() => setSucces(null)} className="mt-2 w-full py-2.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-sm font-semibold">Fermer</button>
                            </div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <style>{`.ipt{width:100%;padding:.65rem .75rem .65rem 2.4rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.7rem;font-size:.9rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </AppLayout>
    );
}

function Champ({ icon: Icon, err, children }: { icon: any; err?: string; children: React.ReactNode }) {
    return (
        <div>
            <div className="relative">
                <Icon className="absolute left-2.5 top-3 w-4 h-4 text-zinc-400" />
                {children}
            </div>
            {err && <p className="text-xs text-red-600 mt-1">{err}</p>}
        </div>
    );
}

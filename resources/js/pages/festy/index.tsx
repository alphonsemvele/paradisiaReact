import { useState } from 'react';
import { Head } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import AppLayout from '@/components/layouts/AppLayout';
import { Trophy, Users, CheckCircle2, MessageCircle, User, Phone, Mail, MapPin, PartyPopper } from 'lucide-react';

interface Equipe { id: number; nom: string; trait: string | null; couleur: string; emoji: string | null; image: string | null; membres: number }
interface Props {
    festy: { titre: string; sous_titre: string | null; date_label: string | null; prix: string | null; description: string | null; inscriptions_ouvertes: boolean };
    equipes: Equipe[];
}

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

export default function FestyIndex({ festy, equipes }: Props) {
    const [choix, setChoix] = useState<Equipe | null>(null);
    const [form, setForm] = useState({ nom: '', telephone: '', email: '', ville: '' });
    const [erreurs, setErreurs] = useState<Record<string, string>>({});
    const [envoi, setEnvoi] = useState(false);
    const [succes, setSucces] = useState<any>(null);

    const inscrire = async () => {
        setErreurs({});
        const e: Record<string, string> = {};
        if (!choix) e.equipe = 'Choisissez une équipe.';
        if (!form.nom.trim()) e.nom = 'Votre nom est requis.';
        if (!form.telephone.trim()) e.telephone = 'Votre téléphone est requis.';
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
            else setErreurs(d.errors ?? { global: d.message ?? 'Erreur' });
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
                    <h1 className="mt-2 text-4xl sm:text-6xl font-extrabold" style={{ letterSpacing: '-.02em' }}>
                        {festy.titre}
                    </h1>
                    {festy.date_label && <p className="mt-2 text-xl font-bold text-amber-400">{festy.date_label}</p>}
                    {festy.prix && (
                        <div className="mt-5 inline-flex items-center gap-2 bg-amber-400 text-emerald-950 font-extrabold px-5 py-2.5 rounded-full">
                            <Trophy className="w-5 h-5" /> Tentez de gagner {festy.prix}
                        </div>
                    )}
                    {festy.description && <p className="mt-5 max-w-2xl mx-auto text-emerald-50/90 text-sm leading-relaxed">{festy.description}</p>}
                </div>
            </div>

            <div className="max-w-5xl mx-auto px-4 py-10">
                <h2 className="text-2xl font-bold text-zinc-900 text-center mb-2">Choisis ton équipe</h2>
                <p className="text-center text-zinc-500 text-sm mb-8">Rejoins une équipe et son groupe WhatsApp après inscription.</p>

                {/* Équipes */}
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    {equipes.map((eq) => {
                        const actif = choix?.id === eq.id;
                        return (
                            <button key={eq.id} onClick={() => setChoix(eq)}
                                className="rounded-2xl border-2 p-4 text-center transition-all"
                                style={{ borderColor: actif ? eq.couleur : '#e5e7eb', background: actif ? `${eq.couleur}18` : '#fff', boxShadow: actif ? `0 6px 20px ${eq.couleur}40` : 'none' }}>
                                {eq.image
                                    ? <img src={eq.image} alt={eq.nom} className="w-16 h-16 mx-auto rounded-xl object-cover mb-2" />
                                    : <div className="text-4xl mb-1">{eq.emoji ?? '🍹'}</div>}
                                <p className="font-extrabold text-sm" style={{ color: eq.couleur }}>ÉQUIPE {eq.nom.toUpperCase()}</p>
                                {eq.trait && <p className="text-[11px] text-zinc-500 mt-0.5">{eq.trait}</p>}
                                <p className="text-[11px] text-zinc-400 mt-1 flex items-center justify-center gap-1"><Users className="w-3 h-3" />{eq.membres}</p>
                                {actif && <CheckCircle2 className="w-5 h-5 mx-auto mt-2" style={{ color: eq.couleur }} />}
                            </button>
                        );
                    })}
                </div>
                {erreurs.equipe && <p className="text-center text-red-600 text-sm mt-3">{erreurs.equipe}</p>}

                {/* Formulaire */}
                {festy.inscriptions_ouvertes ? (
                    <div className="mt-8 max-w-md mx-auto bg-white rounded-2xl border border-zinc-200 shadow-sm p-6">
                        <h3 className="font-bold text-zinc-900 mb-4">
                            {choix ? <>M'inscrire dans l'équipe <span style={{ color: choix.couleur }}>{choix.nom}</span></> : 'Inscription'}
                        </h3>
                        <div className="space-y-3">
                            <Champ icon={User} err={erreurs.nom}><input className="ipt" placeholder="Nom complet" value={form.nom} onChange={(e) => setForm({ ...form, nom: e.target.value })} /></Champ>
                            <Champ icon={Phone} err={erreurs.telephone}><input className="ipt" placeholder="Téléphone (WhatsApp)" value={form.telephone} onChange={(e) => setForm({ ...form, telephone: e.target.value })} /></Champ>
                            <Champ icon={Mail} err={erreurs.email}><input className="ipt" placeholder="E-mail (optionnel)" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} /></Champ>
                            <Champ icon={MapPin}><input className="ipt" placeholder="Ville / Quartier (optionnel)" value={form.ville} onChange={(e) => setForm({ ...form, ville: e.target.value })} /></Champ>
                        </div>
                        {erreurs.global && <p className="text-red-600 text-sm mt-2">{erreurs.global}</p>}
                        <button onClick={inscrire} disabled={envoi}
                            className="mt-4 w-full py-3 rounded-xl text-white font-bold transition-colors disabled:opacity-60"
                            style={{ background: choix?.couleur ?? '#14532d' }}>
                            {envoi ? 'Inscription…' : 'Rejoindre mon équipe'}
                        </button>
                    </div>
                ) : (
                    <p className="mt-8 text-center text-zinc-500">Les inscriptions ne sont pas encore ouvertes.</p>
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

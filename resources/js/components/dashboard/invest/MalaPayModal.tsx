import { useEffect, useMemo, useState } from 'react';
import { motion } from 'framer-motion';
import {
    X, Wallet, Globe, Loader2, AlertCircle, CheckCircle2,
    ArrowRight, Phone, MessageCircle, ShieldCheck, PlusCircle, LifeBuoy, MailCheck,
    Smartphone,
} from 'lucide-react';

interface Pays {
    code: string;
    nom: string;
    drapeau: string | null;
    devise: string;
    indicatif: string | null;
}

interface Operateur {
    code: string;
    nom: string;
}

interface Representant {
    nom: string;
    pays: string | null;
    telephone: string | null;
    whatsapp: string | null;
    email: string | null;
}

interface Props {
    parts: number;
    prixPart: number;
    onClose: () => void;
}

type Etape =
    | 'pays'
    | 'moyen'
    | 'portefeuille'
    | 'confirmation'
    | 'attente_validation'
    | 'mobile'
    | 'attente_mobile'
    | 'succes';

const operateurStyle: Record<string, { label: string; emoji: string; accent: string }> = {
    orange: { label: 'Orange Money', emoji: '🟠', accent: 'text-orange-600' },
    mtn: { label: 'MTN Mobile Money', emoji: '🟡', accent: 'text-yellow-600' },
};

const nf = (n: number) => new Intl.NumberFormat('fr-FR').format(n);

/** Jeton CSRF frais : le cookie XSRF-TOKEN est mis à jour à chaque réponse,
 *  contrairement au <meta> qui se périme après une navigation SPA Inertia. */
const lireCookie = (nom: string): string => {
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + nom + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
};

const poster = async (url: string, corps: unknown) => {
    const xsrf = lireCookie('XSRF-TOKEN');
    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
    // On privilégie X-XSRF-TOKEN (cookie frais). En repli seulement, le <meta>.
    if (xsrf) {
        headers['X-XSRF-TOKEN'] = xsrf;
    } else {
        headers['X-CSRF-TOKEN'] =
            document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
    }

    const res = await fetch(url, {
        method: 'POST',
        headers,
        credentials: 'same-origin',
        body: JSON.stringify(corps),
    });

    return { statut: res.status, corps: await res.json().catch(() => ({})) };
};

/**
 * Paiement d'un investissement par portefeuille Malapay.
 *
 * Le pays détermine la devise ; le portefeuille doit être dans cette devise
 * et suffisamment approvisionné. Toute anomalie affiche les représentants
 * Malapay habilités à créditer le portefeuille.
 */
export default function MalaPayModal({ parts, prixPart, onClose }: Props) {
    const [etape, setEtape] = useState<Etape>('pays');
    // Voile « traitement » pendant la création du paiement en mode redirection :
    // sans lui, l'écran reste figé quelques secondes et le client croit au bug.
    const [redirection, setRedirection] = useState(false);
    const [paysDisponibles, setPaysDisponibles] = useState<Pays[]>([]);
    const [chargementPays, setChargementPays] = useState(true);
    const [malapayIndisponible, setMalapayIndisponible] = useState(false);
    const [urlPortefeuilles, setUrlPortefeuilles] = useState('https://mala-pay.com/portefeuilles');

    const [recherche, setRecherche] = useState('');
    const [paysChoisi, setPaysChoisi] = useState<Pays | null>(null);
    const [code, setCode] = useState('');

    const [operateurs, setOperateurs] = useState<Operateur[]>([]);
    // Connu avant le clic : en redirection, tout se passe sur la page Malapay
    // et rien de l'écran de paiement ne doit apparaître ici.
    const [modeIntegration, setModeIntegration] = useState<'redirection' | 'api'>('redirection');
    const [chargementOperateurs, setChargementOperateurs] = useState(false);
    const [operateurChoisi, setOperateurChoisi] = useState<Operateur | null>(null);
    const [telephone, setTelephone] = useState('');

    const [enCours, setEnCours] = useState(false);
    const [erreur, setErreur] = useState<string | null>(null);
    const [representants, setRepresentants] = useState<Representant[]>([]);
    const [portefeuille, setPortefeuille] = useState<any>(null);
    const [succes, setSucces] = useState<any>(null);
    const [attente, setAttente] = useState<any>(null);

    // Sélection d'un pays → on charge les moyens de paiement disponibles.
    const choisirPays = async (p: Pays) => {
        setPaysChoisi(p);
        setOperateurs([]);
        setOperateurChoisi(null);
        reinitialiserErreur();
        setEtape('moyen');
        setChargementOperateurs(true);
        try {
            const res = await fetch(`/invest/paiement/operateurs?pays=${p.code}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const d = await res.json();
            setOperateurs(d.operateurs ?? []);
            setModeIntegration(d.mode_integration === 'api' ? 'api' : 'redirection');
        } catch {
            setOperateurs([]);
        } finally {
            setChargementOperateurs(false);
        }
    };

    const payerMobile = async () => {
        if (!paysChoisi || !operateurChoisi || telephone.trim().length < 6) return;
        reinitialiserErreur();

        // En mode API, le paiement se déroule ici : l'écran d'attente s'affiche
        // au clic, sans attendre la réponse, pour que l'utilisateur voie que
        // son geste a été pris en compte.
        //
        // En redirection, rien ne s'affiche : le client part sur la page
        // Malapay dès que l'URL arrive. Montrer un écran de paiement qu'on
        // quitte aussitôt ne fait que doublonner celui de destination.
        if (modeIntegration === 'api') {
            setAttente(null);
            setEtape('attente_mobile');
        } else {
            // Mode redirection : on affiche tout de suite un voile « traitement »
            // pour couvrir la latence d'initialisation, jusqu'au départ du
            // navigateur vers la page Malapay.
            setRedirection(true);
        }

        setEnCours(true);

        const { corps } = await poster('/invest/paiement/mobile', {
            pays: paysChoisi.code,
            devise: paysChoisi.devise,
            parts,
            operateur: operateurChoisi.code,
            telephone: telephone.trim(),
        });

        setEnCours(false);

        if (!corps.ok) {
            // Retour au formulaire : la demande n'est jamais partie.
            setRedirection(false);
            setErreur(corps.message ?? 'Le paiement mobile a échoué.');
            setEtape('mobile');
            return;
        }

        // Redirection : on quitte la page. Le voile reste affiché jusqu'au
        // chargement de la page Malapay, où le client saisit et valide.
        if (corps.url_paiement) {
            window.location.href = corps.url_paiement;

            return;
        }

        // Pas d'URL (mode API) : on bascule sur l'écran d'attente mobile.
        setRedirection(false);
        setAttente(corps);
        if (modeIntegration !== 'api') {
            setEtape('attente_mobile');
        }
    };

    // Chargement des pays à l'ouverture
    useEffect(() => {
        let annule = false;

        fetch('/invest/paiement/pays', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then((r) => r.json())
            .then((d) => {
                if (annule) return;
                setPaysDisponibles(d.pays ?? []);
                setMalapayIndisponible(!d.disponible);
                if (d.url_portefeuilles) setUrlPortefeuilles(d.url_portefeuilles);
            })
            .catch(() => !annule && setMalapayIndisponible(true))
            .finally(() => !annule && setChargementPays(false));

        return () => { annule = true; };
    }, []);

    const paysFiltres = useMemo(() => {
        const q = recherche.trim().toLowerCase();
        const liste = q
            ? paysDisponibles.filter(
                  (p) => p.nom.toLowerCase().includes(q) || p.devise.toLowerCase().includes(q)
              )
            : paysDisponibles;

        return liste.slice(0, 60);
    }, [recherche, paysDisponibles]);

    const total = parts * prixPart;

    const reinitialiserErreur = () => {
        setErreur(null);
        setRepresentants([]);
    };

    const verifier = async () => {
        if (!paysChoisi || code.trim().length < 10) return;
        setEnCours(true);
        reinitialiserErreur();

        const { corps } = await poster('/invest/paiement/verifier', {
            code: code.trim(),
            pays: paysChoisi.code,
            devise: paysChoisi.devise,
            parts,
        });

        setEnCours(false);

        if (!corps.ok) {
            setErreur(corps.message ?? 'Vérification impossible.');
            setRepresentants(corps.representants ?? []);
            return;
        }

        setPortefeuille(corps.portefeuille);
        setEtape('confirmation');
    };

    const payer = async () => {
        if (!paysChoisi) return;
        setEnCours(true);
        reinitialiserErreur();

        const { corps } = await poster('/invest/paiement', {
            code: code.trim(),
            pays: paysChoisi.code,
            devise: paysChoisi.devise,
            parts,
        });

        setEnCours(false);

        if (!corps.ok) {
            setErreur(corps.message ?? 'Le paiement a échoué.');
            setRepresentants(corps.representants ?? []);
            setEtape('portefeuille');
            return;
        }

        // Malapay exige la validation du titulaire par e-mail : rien n'est
        // débité tant qu'il n'a pas ouvert le lien.
        if (corps.en_attente) {
            setAttente(corps);
            setEtape('attente_validation');
            return;
        }

        setSucces(corps);
        setEtape('succes');
    };

    // Chronomètre de l'attente : sans repère de temps, l'utilisateur ne sait pas
    // si le paiement avance ou si la page est figée.
    const [secondes, setSecondes] = useState(0);

    useEffect(() => {
        if (etape !== 'attente_mobile') { setSecondes(0); return; }
        const t = setInterval(() => setSecondes(s => s + 1), 1000);
        return () => clearInterval(t);
    }, [etape]);

    // Interroge le serveur pendant l'attente (validation e-mail OU paiement mobile).
    useEffect(() => {
        const enAttente = etape === 'attente_validation' || etape === 'attente_mobile';
        // Pas de référence tant que le serveur n'a pas répondu : rien à sonder.
        if (!enAttente || !attente?.reference) return;

        const retourSurEchec: Etape = etape === 'attente_mobile' ? 'moyen' : 'portefeuille';
        let annule = false;

        const sonder = async () => {
            const res = await fetch(`/invest/paiement/statut/${attente.reference}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            }).catch(() => null);

            if (annule || !res) return;

            const d = await res.json().catch(() => ({}));

            if (!d.termine) return;

            if (d.reussi) {
                setSucces({
                    reference: attente.reference,
                    montant_formate: attente.montant_formate,
                    solde_restant: d.solde_restant,
                    message: d.message ?? 'Investissement confirmé.',
                });
                setEtape('succes');
            } else {
                setErreur(d.message ?? 'Le paiement n\'a pas abouti.');
                setEtape(retourSurEchec);
            }
        };

        const minuteur = setInterval(sonder, 4000);
        sonder();

        return () => { annule = true; clearInterval(minuteur); };
    }, [etape, attente]);

    // ── Voile « traitement en cours » avant la redirection Malapay ─────────
    // Affiché dès le clic en mode redirection et maintenu jusqu'au départ vers
    // la page de paiement : le client voit que sa demande est prise en compte.
    if (redirection) {
        return (
            <div className="fixed inset-0 z-[70] flex items-center justify-center p-6 text-center text-white"
                 style={{ background: 'radial-gradient(circle at 50% 30%, #10b981 0%, #06281a 72%)' }}
                 role="dialog" aria-modal="true">
                <div className="w-full max-w-xs">
                    <Loader2 className="w-12 h-12 animate-spin mx-auto mb-5" strokeWidth={1.6} />
                    <h3 className="text-lg font-extrabold">Traitement en cours…</h3>
                    <p className="mt-2 text-sm text-emerald-50/85">Nous préparons votre paiement sécurisé. Vous allez être redirigé, merci de patienter et de ne pas fermer cette page.</p>
                    <div className="mt-5 h-1 w-full overflow-hidden rounded-full bg-white/20">
                        <div className="h-full w-1/3 rounded-full bg-white/80" style={{ animation: 'mpGlisse 1.1s ease-in-out infinite' }} />
                    </div>
                </div>
                <style>{`@keyframes mpGlisse { 0% { margin-left: -35% } 100% { margin-left: 100% } }`}</style>
            </div>
        );
    }

    // ── Écran d'attente du paiement mobile ─────────────────────────────────
    // Rendu seul, en plein écran : pendant que la demande est sur le téléphone
    // du client, plus rien d'autre ne compte. Volontairement non refermable —
    // fermer par mégarde en cliquant à côté ferait perdre le suivi du paiement.
    if (etape === 'attente_mobile') {
        const minutes = Math.floor(secondes / 60);
        const reste = String(secondes % 60).padStart(2, '0');
        // Tant que le serveur n'a pas répondu, on affiche le montant calculé
        // localement : l'écran ne doit jamais rester vide.
        const sommeAffichee = attente?.montant_formate ?? `${nf(total)} ${paysChoisi?.devise ?? ''}`;
        const envoiEnCours = !attente;
        // Malapay a renvoyé une page de paiement : c'est elle qui recueille le
        // numéro et déclenche la demande. Afficher ici « validez sur votre
        // téléphone » serait faux — rien n'est encore parti — et doublonnerait
        // l'écran de cette page.
        const surPageHebergee = !!attente?.url_paiement;

        return (
            <div className="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto p-6 text-center text-white"
                 style={{ background: 'radial-gradient(circle at 50% 30%, #10b981 0%, #06281a 72%)' }}
                 role="dialog" aria-modal="true" aria-labelledby="mp-attente-titre">
                <style>{`
                    @keyframes mpOnde { 0% { transform: scale(.4); opacity: .75 } 75% { opacity: 0 } 100% { transform: scale(2.6); opacity: 0 } }
                    @keyframes mpHalo { 0%,100% { transform: scale(1); opacity: .35 } 50% { transform: scale(1.12); opacity: .6 } }
                    @keyframes mpFlotte { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-9px) } }
                    @keyframes mpBat { 0%,100% { opacity: 1; transform: scale(1) } 50% { opacity: .25; transform: scale(.82) } }
                    @keyframes mpGlisse { 0% { left: -45% } 100% { left: 100% } }
                    @keyframes mpMonte { from { opacity: 0; transform: translateY(22px) scale(.94) } to { opacity: 1; transform: none } }
                    .mp-onde { position:absolute; inset:0; border-radius:50%; border:2px solid rgba(255,255,255,.55); opacity:0; animation: mpOnde 3s cubic-bezier(.25,.6,.35,1) infinite }
                    .mp-onde:nth-child(2){ animation-delay:.75s } .mp-onde:nth-child(3){ animation-delay:1.5s } .mp-onde:nth-child(4){ animation-delay:2.25s }
                    @media (prefers-reduced-motion: reduce) { .mp-anim { animation: none !important } .mp-onde { animation: none !important; opacity:.2 } }
                `}</style>

                <div className="w-full max-w-md" style={{ animation: 'mpMonte .45s cubic-bezier(.2,.8,.25,1) both' }}>

                    <div className="relative mx-auto" style={{ width: 190, height: 190 }}>
                        <span className="mp-onde" /><span className="mp-onde" /><span className="mp-onde" /><span className="mp-onde" />
                        <div className="mp-anim absolute left-1/2 top-1/2" style={{ width: 130, height: 130, margin: '-65px 0 0 -65px', borderRadius: '50%', background: 'radial-gradient(circle, rgba(255,255,255,.28), transparent 68%)', animation: 'mpHalo 3s ease-in-out infinite' }} />
                        <div className="mp-anim absolute left-1/2 top-1/2 flex items-center justify-center bg-white text-emerald-600"
                             style={{ width: 104, height: 104, margin: '-52px 0 0 -52px', borderRadius: 30, boxShadow: '0 16px 44px rgba(0,0,0,.35)', animation: 'mpFlotte 3.2s ease-in-out infinite' }}>
                            <Smartphone style={{ width: 52, height: 52 }} strokeWidth={1.5} />
                        </div>
                    </div>

                    <div className="mt-4 mb-4 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-5 py-2 text-xs font-bold uppercase tracking-wider">
                        <span className="mp-anim inline-block rounded-full" style={{ width: 9, height: 9, background: '#fbbf24', boxShadow: '0 0 12px #fbbf24', animation: 'mpBat 1.3s ease-in-out infinite' }} />
                        {envoiEnCours ? 'Envoi de la demande…' : surPageHebergee ? 'Paiement en cours' : 'En attente de validation'}
                    </div>

                    <h2 id="mp-attente-titre" className="mb-4 text-[26px] font-extrabold leading-tight tracking-tight">
                        {envoiEnCours
                            ? 'Préparation du paiement'
                            : 'Validez sur votre téléphone'}
                    </h2>

                    <div className="text-[46px] font-extrabold leading-none tracking-tight" style={{ textShadow: '0 4px 24px rgba(0,0,0,.3)' }}>
                        {sommeAffichee}
                    </div>

                    {telephone && !surPageHebergee && (
                        <div className="mt-3 inline-block rounded-lg bg-white/15 px-4 py-1.5 text-[15px] font-bold tracking-wider tabular-nums">
                            {telephone}
                        </div>
                    )}

                    <p className="mt-5 text-sm leading-relaxed text-white/80">
                        {envoiEnCours ? (
                            <>Nous contactons votre opérateur.<br />Gardez votre téléphone à portée de main.</>
                        ) : surPageHebergee ? (
                            <>Nous vous conduisons vers la page de paiement sécurisée.
                            <br />Vous reviendrez ici automatiquement une fois le paiement terminé.</>
                        ) : (
                            <>Une demande de paiement vient d&apos;être envoyée sur votre ligne.
                            <br />Saisissez votre <strong>code secret Mobile Money</strong> pour confirmer.</>
                        )}
                    </p>

                    <div className="relative mx-auto mt-6 mb-3 h-1 max-w-[260px] overflow-hidden rounded bg-white/20">
                        <span className="mp-anim absolute top-0 h-full rounded" style={{ width: '45%', background: 'linear-gradient(90deg, transparent, white, transparent)', animation: 'mpGlisse 1.8s ease-in-out infinite' }} />
                    </div>

                    <div className="text-[15px] font-bold tabular-nums text-white/70">{minutes}:{reste}</div>

                    {attente?.code_ussd && (
                        <p className="mt-4 text-[12.5px] leading-relaxed text-white/70">
                            Le paiement ne s&apos;affiche pas sur votre téléphone ?
                            Composez <strong className="tracking-wider text-white">{attente.code_ussd}</strong> pour le valider.
                        </p>
                    )}

                    {secondes >= 120 && (
                        <p className="mt-6 rounded-xl border px-4 py-3 text-[13px] leading-relaxed"
                           style={{ background: 'rgba(251,191,36,.16)', borderColor: 'rgba(251,191,36,.42)', color: '#fde68a' }}>
                            Toujours rien ? Vérifiez que votre ligne est active et que votre solde
                            suffit, puis relancez le paiement.
                        </p>
                    )}

                    {surPageHebergee && (
                        <a href={attente!.url_paiement!}
                           className="mt-6 block w-full rounded-xl bg-white py-3 text-sm font-bold text-emerald-700 transition hover:bg-white/90">
                            Continuer vers le paiement
                        </a>
                    )}

                    <button type="button" onClick={onClose}
                        className="mt-3 w-full rounded-xl border border-white/30 bg-white/10 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                        Annuler et revenir au site
                    </button>

                    <p className="mt-3 text-[11px] leading-relaxed text-white/40">
                        Annuler ferme seulement cette fenêtre. Si vous avez déjà validé sur votre
                        téléphone, l&apos;investissement sera bien enregistré.
                    </p>

                    {attente && (
                        <div className="mt-4 text-[11px] tracking-wide text-white/40">Réf. {attente.reference}</div>
                    )}
                </div>
            </div>
        );
    }

    return (
        <div className="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4" onClick={onClose}>
            <motion.div
                initial={{ opacity: 0, scale: 0.96, y: 10 }}
                animate={{ opacity: 1, scale: 1, y: 0 }}
                onClick={(e) => e.stopPropagation()}
                className="bg-white rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-2xl"
            >
                {/* En-tête */}
                <div className="sticky top-0 bg-white flex items-center justify-between px-5 py-4 border-b border-zinc-100 rounded-t-2xl">
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <Wallet className="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <h3 className="font-semibold text-zinc-900">Payer via Malapay</h3>
                            <p className="text-xs text-zinc-500">
                                {parts} part{parts > 1 ? 's' : ''} · {nf(total)} FCFA
                            </p>
                        </div>
                    </div>
                    <button onClick={onClose} className="p-2 hover:bg-zinc-100 rounded-full transition-colors">
                        <X className="w-5 h-5 text-zinc-500" />
                    </button>
                </div>

                <div className="p-5">
                    {malapayIndisponible && (
                        <Alerte
                            titre="Paiement indisponible"
                            message="Le service de paiement Malapay n'est pas encore activé sur ce site."
                        />
                    )}

                    {/* ── Étape 1 : pays ─────────────────────────────── */}
                    {etape === 'pays' && !malapayIndisponible && (
                        <>
                            <label className="block text-sm font-medium text-zinc-700 mb-2">
                                <Globe className="w-4 h-4 inline mr-1.5 text-zinc-400" />
                                Depuis quel pays payez-vous ?
                            </label>
                            <input
                                type="text"
                                value={recherche}
                                onChange={(e) => setRecherche(e.target.value)}
                                placeholder="Rechercher un pays ou une devise…"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />

                            {chargementPays ? (
                                <div className="py-10 flex flex-col items-center gap-2">
                                    <Loader2 className="w-6 h-6 text-emerald-500 animate-spin" />
                                    <p className="text-sm text-zinc-500">Chargement des pays…</p>
                                </div>
                            ) : (
                                <div className="max-h-72 overflow-y-auto divide-y divide-zinc-100 border border-zinc-100 rounded-xl">
                                    {paysFiltres.map((p) => (
                                        <button
                                            key={p.code}
                                            onClick={() => choisirPays(p)}
                                            className="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-emerald-50 transition-colors text-left"
                                        >
                                            <span className="text-xl">{p.drapeau ?? '🌍'}</span>
                                            <span className="flex-1 text-sm text-zinc-800">{p.nom}</span>
                                            <span className="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                                                {p.devise}
                                            </span>
                                        </button>
                                    ))}
                                    {paysFiltres.length === 0 && (
                                        <p className="px-4 py-6 text-sm text-zinc-500 text-center">Aucun pays trouvé.</p>
                                    )}
                                </div>
                            )}
                        </>
                    )}

                    {/* ── Étape 1bis : moyen de paiement ──────────────── */}
                    {etape === 'moyen' && paysChoisi && (
                        <>
                            <div className="flex items-center justify-between rounded-xl bg-zinc-50 border border-zinc-200 px-4 py-3 mb-4">
                                <span className="flex items-center gap-2 text-sm text-zinc-700">
                                    <span className="text-lg">{paysChoisi.drapeau ?? '🌍'}</span>
                                    {paysChoisi.nom}
                                </span>
                                <span className="text-xs font-semibold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-full">
                                    {paysChoisi.devise}
                                </span>
                            </div>

                            <label className="block text-sm font-medium text-zinc-700 mb-2">
                                Comment souhaitez-vous payer ?
                            </label>

                            <div className="space-y-2">
                                <button
                                    onClick={() => { reinitialiserErreur(); setEtape('portefeuille'); }}
                                    className="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-zinc-200 hover:border-emerald-400 hover:bg-emerald-50 transition-colors text-left"
                                >
                                    <span className="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                                        <Wallet className="w-5 h-5 text-emerald-600" />
                                    </span>
                                    <span className="flex-1">
                                        <span className="block text-sm font-semibold text-zinc-800">Portefeuille MalaPay</span>
                                        <span className="block text-xs text-zinc-500">Payer depuis votre solde MalaPay</span>
                                    </span>
                                    <ArrowRight className="w-4 h-4 text-zinc-400" />
                                </button>

                                {chargementOperateurs && (
                                    <div className="py-4 flex items-center justify-center gap-2 text-sm text-zinc-500">
                                        <Loader2 className="w-4 h-4 animate-spin" /> Chargement des opérateurs…
                                    </div>
                                )}

                                {operateurs.map((op) => {
                                    const st = operateurStyle[op.code] ?? { label: op.nom, emoji: '📱', accent: 'text-zinc-700' };
                                    return (
                                        <button
                                            key={op.code}
                                            onClick={() => { reinitialiserErreur(); setOperateurChoisi(op); setTelephone(''); setEtape('mobile'); }}
                                            className="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-zinc-200 hover:border-emerald-400 hover:bg-emerald-50 transition-colors text-left"
                                        >
                                            <span className="w-9 h-9 rounded-lg bg-zinc-50 flex items-center justify-center text-lg">{st.emoji}</span>
                                            <span className="flex-1">
                                                <span className="block text-sm font-semibold text-zinc-800">{st.label}</span>
                                                <span className="block text-xs text-zinc-500">Paiement mobile money</span>
                                            </span>
                                            <ArrowRight className="w-4 h-4 text-zinc-400" />
                                        </button>
                                    );
                                })}
                            </div>

                            <button
                                onClick={() => { setEtape('pays'); reinitialiserErreur(); }}
                                className="mt-4 px-4 py-2.5 rounded-lg border border-zinc-200 text-sm text-zinc-700 hover:bg-zinc-50"
                            >
                                Retour
                            </button>
                        </>
                    )}

                    {/* ── Étape mobile money : numéro ──────────────────── */}
                    {etape === 'mobile' && paysChoisi && operateurChoisi && (
                        <>
                            <div className="flex items-center gap-3 rounded-xl bg-zinc-50 border border-zinc-200 px-4 py-3 mb-4">
                                <span className="w-9 h-9 rounded-lg bg-white border border-zinc-200 flex items-center justify-center text-lg">
                                    {(operateurStyle[operateurChoisi.code]?.emoji) ?? '📱'}
                                </span>
                                <span className="flex-1 text-sm font-semibold text-zinc-800">
                                    {operateurStyle[operateurChoisi.code]?.label ?? operateurChoisi.nom}
                                </span>
                                <span className="text-xs font-semibold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-full">
                                    {nf(total)} {paysChoisi.devise}
                                </span>
                            </div>

                            <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                <Smartphone className="w-4 h-4 inline mr-1.5 text-zinc-400" />
                                Numéro {operateurStyle[operateurChoisi.code]?.label ?? ''}
                            </label>
                            <div className="flex items-center rounded-lg border border-zinc-200 bg-zinc-50 focus-within:ring-2 focus-within:ring-emerald-500">
                                <span className="px-3 py-2.5 text-sm font-semibold text-zinc-500 border-r border-zinc-200">
                                    +{paysChoisi.indicatif ?? ''}
                                </span>
                                <input
                                    type="tel"
                                    value={telephone}
                                    onChange={(e) => { setTelephone(e.target.value.replace(/[^0-9]/g, '')); reinitialiserErreur(); }}
                                    placeholder="6xx xxx xxx"
                                    className="flex-1 px-3 py-2.5 bg-transparent text-sm focus:outline-none"
                                    maxLength={15}
                                />
                            </div>
                            <p className="text-xs text-zinc-500 mt-1.5">
                                Vous serez redirigé vers une page sécurisée pour valider le paiement avec votre code secret.
                            </p>

                            {erreur && <Alerte titre="Paiement impossible" message={erreur} />}

                            <div className="flex gap-2 mt-5">
                                <button
                                    onClick={() => { setEtape('moyen'); reinitialiserErreur(); }}
                                    className="px-4 py-2.5 rounded-lg border border-zinc-200 text-sm text-zinc-700 hover:bg-zinc-50"
                                >
                                    Retour
                                </button>
                                <button
                                    onClick={payerMobile}
                                    disabled={enCours || telephone.trim().length < 6}
                                    className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 disabled:bg-zinc-300 text-white text-sm font-semibold rounded-lg transition-colors"
                                >
                                    {enCours ? (
                                        <><Loader2 className="w-4 h-4 animate-spin" /> Initialisation…</>
                                    ) : (
                                        <>Payer {nf(total)} {paysChoisi.devise} <ArrowRight className="w-4 h-4" /></>
                                    )}
                                </button>
                            </div>
                        </>
                    )}

                    {/* ── Étape 2 : code du portefeuille ──────────────── */}
                    {etape === 'portefeuille' && paysChoisi && (
                        <>
                            <div className="flex items-center justify-between rounded-xl bg-zinc-50 border border-zinc-200 px-4 py-3 mb-4">
                                <span className="flex items-center gap-2 text-sm text-zinc-700">
                                    <span className="text-lg">{paysChoisi.drapeau ?? '🌍'}</span>
                                    {paysChoisi.nom}
                                </span>
                                <span className="text-xs font-semibold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-full">
                                    Devise : {paysChoisi.devise}
                                </span>
                            </div>

                            <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                Code de votre portefeuille Malapay
                            </label>
                            <input
                                type="text"
                                value={code}
                                onChange={(e) => { setCode(e.target.value); reinitialiserErreur(); }}
                                placeholder="Collez ici le code de votre portefeuille"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                            <p className="text-xs text-zinc-500 mt-1.5">
                                Retrouvez ce code dans Malapay, rubrique « Portefeuilles ». Le portefeuille
                                doit être en {paysChoisi.devise}.
                            </p>

                            {/* L'investisseur n'a pas encore de portefeuille */}
                            <div className="mt-3 flex flex-wrap items-center gap-2">
                                <a
                                    href={urlPortefeuilles}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors"
                                >
                                    <PlusCircle className="w-4 h-4" />
                                    Je n'ai pas de portefeuille — en créer un
                                </a>
                                <a
                                    href={`https://wa.me/237687984282?text=${encodeURIComponent(
                                        "Bonjour PARADISIA, j'ai besoin d'aide pour payer mon investissement."
                                    )}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors"
                                >
                                    <LifeBuoy className="w-4 h-4 text-emerald-600" />
                                    Assistance
                                </a>
                            </div>

                            {erreur && <Alerte titre="Paiement impossible" message={erreur} />}
                            {representants.length > 0 && <Representants liste={representants} />}

                            <div className="flex gap-2 mt-5">
                                <button
                                    onClick={() => { setEtape('moyen'); reinitialiserErreur(); }}
                                    className="px-4 py-2.5 rounded-lg border border-zinc-200 text-sm text-zinc-700 hover:bg-zinc-50"
                                >
                                    Retour
                                </button>
                                <button
                                    onClick={verifier}
                                    disabled={enCours || code.trim().length < 10}
                                    className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 disabled:bg-zinc-300 text-white text-sm font-semibold rounded-lg transition-colors"
                                >
                                    {enCours ? (
                                        <><Loader2 className="w-4 h-4 animate-spin" /> Vérification…</>
                                    ) : (
                                        <>Vérifier mon portefeuille <ArrowRight className="w-4 h-4" /></>
                                    )}
                                </button>
                            </div>
                        </>
                    )}

                    {/* ── Étape 3 : confirmation ──────────────────────── */}
                    {etape === 'confirmation' && portefeuille && paysChoisi && (
                        <>
                            <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 mb-4">
                                <p className="flex items-center gap-2 text-sm font-medium text-emerald-800 mb-2">
                                    <CheckCircle2 className="w-4 h-4" />
                                    Portefeuille vérifié
                                </p>
                                <Ligne label="Titulaire" valeur={portefeuille.titulaire} />
                                <Ligne label="Portefeuille" valeur={`${portefeuille.portefeuille} (${portefeuille.devise})`} />
                                <Ligne label="Solde disponible" valeur={portefeuille.solde_formate} />
                            </div>

                            <div className="rounded-xl border border-zinc-200 p-4 mb-4">
                                <Ligne label="Parts" valeur={String(parts)} />
                                <Ligne label="Prix par part" valeur={`${nf(prixPart)} ${paysChoisi.devise}`} />
                                <div className="flex items-center justify-between pt-2 mt-2 border-t border-zinc-100">
                                    <span className="text-sm font-medium text-zinc-700">Total à payer</span>
                                    <span className="text-lg font-bold text-emerald-600">
                                        {nf(total)} {paysChoisi.devise}
                                    </span>
                                </div>
                            </div>

                            {erreur && <Alerte titre="Paiement refusé" message={erreur} />}
                            {representants.length > 0 && <Representants liste={representants} />}

                            <div className="flex gap-2">
                                <button
                                    onClick={() => setEtape('portefeuille')}
                                    className="px-4 py-2.5 rounded-lg border border-zinc-200 text-sm text-zinc-700 hover:bg-zinc-50"
                                >
                                    Retour
                                </button>
                                <button
                                    onClick={payer}
                                    disabled={enCours}
                                    className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-zinc-900 hover:bg-zinc-800 disabled:bg-zinc-300 text-white text-sm font-semibold rounded-lg transition-colors"
                                >
                                    {enCours ? (
                                        <><Loader2 className="w-4 h-4 animate-spin" /> Paiement en cours…</>
                                    ) : (
                                        <>Envoyer la demande <ShieldCheck className="w-4 h-4" /></>
                                    )}
                                </button>
                            </div>
                        </>
                    )}

                    {/* ── Étape 4 : validation par e-mail ─────────────── */}
                    {etape === 'attente_validation' && attente && (
                        <div className="text-center py-2">
                            <div className="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                                <MailCheck className="w-7 h-7 text-amber-600" />
                            </div>
                            <h4 className="text-lg font-bold text-zinc-900 mb-1">
                                Validez depuis votre e-mail
                            </h4>
                            <p className="text-sm text-zinc-600 mb-4">
                                Un lien de confirmation vient d'être envoyé
                                {attente.email_masque ? ` à ${attente.email_masque}` : ' au titulaire du portefeuille'}.
                                <strong> Aucun montant n'a encore été débité.</strong>
                            </p>

                            <div className="rounded-xl border border-zinc-200 p-4 text-left mb-4">
                                <Ligne label="Montant" valeur={attente.montant_formate} />
                                <Ligne label="Référence" valeur={attente.reference} />
                            </div>

                            <div className="flex items-center justify-center gap-2 rounded-xl bg-zinc-50 border border-zinc-200 px-4 py-3">
                                <Loader2 className="w-4 h-4 text-emerald-600 animate-spin" />
                                <span className="text-sm text-zinc-600">
                                    En attente de votre validation…
                                </span>
                            </div>

                            <p className="text-xs text-zinc-500 mt-3">
                                Cette page se mettra à jour automatiquement dès que vous aurez
                                cliqué sur le lien. Le lien expire dans 30 minutes.
                            </p>
                        </div>
                    )}

                    {/* ── Étape 5 : succès ────────────────────────────── */}
                    {etape === 'succes' && succes && (
                        <div className="text-center py-4">
                            <div className="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                                <CheckCircle2 className="w-7 h-7 text-emerald-600" />
                            </div>
                            <h4 className="text-lg font-bold text-zinc-900 mb-1">Investissement confirmé</h4>
                            <p className="text-sm text-zinc-600 mb-4">{succes.message}</p>

                            <div className="rounded-xl border border-zinc-200 p-4 text-left mb-5">
                                <Ligne label="Référence" valeur={succes.reference} />
                                <Ligne label="Montant" valeur={succes.montant_formate} />
                                {succes.solde_restant && (
                                    <Ligne label="Solde restant" valeur={succes.solde_restant} />
                                )}
                            </div>

                            <button
                                onClick={() => window.location.reload()}
                                className="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg"
                            >
                                Terminer
                            </button>
                        </div>
                    )}
                </div>
            </motion.div>
        </div>
    );
}

function Ligne({ label, valeur }: { label: string; valeur: string }) {
    return (
        <div className="flex items-center justify-between py-1">
            <span className="text-xs text-zinc-500">{label}</span>
            <span className="text-sm font-medium text-zinc-800 text-right">{valeur}</span>
        </div>
    );
}

function Alerte({ titre, message }: { titre: string; message: string }) {
    return (
        <div className="mt-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <AlertCircle className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
            <div>
                <p className="text-sm font-semibold text-amber-900">{titre}</p>
                <p className="text-sm text-amber-800 mt-0.5">{message}</p>
            </div>
        </div>
    );
}

/** Contacts habilités à créditer un portefeuille Malapay. */
function Representants({ liste }: { liste: Representant[] }) {
    return (
        <div className="mt-3 rounded-xl border border-zinc-200 p-4">
            <p className="text-sm font-medium text-zinc-700 mb-3">
                Contactez un représentant Malapay pour créditer votre portefeuille :
            </p>
            <div className="space-y-2">
                {liste.map((r, i) => (
                    <div key={i} className="flex items-center justify-between gap-2 rounded-lg bg-zinc-50 px-3 py-2">
                        <div className="min-w-0">
                            <p className="text-sm font-medium text-zinc-800 truncate">{r.nom}</p>
                            <p className="text-xs text-zinc-500 truncate">
                                {r.pays ? `${r.pays} · ` : ''}{r.telephone ?? r.email}
                            </p>
                        </div>
                        <div className="flex gap-1.5 flex-shrink-0">
                            {r.whatsapp && (
                                <a
                                    href={`https://wa.me/${r.whatsapp.replace(/\D/g, '')}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="p-2 rounded-lg bg-[#25D366] text-white hover:opacity-90"
                                    title="WhatsApp"
                                >
                                    <MessageCircle className="w-4 h-4" />
                                </a>
                            )}
                            {r.telephone && (
                                <a
                                    href={`tel:${r.telephone}`}
                                    className="p-2 rounded-lg bg-zinc-200 text-zinc-700 hover:bg-zinc-300"
                                    title="Appeler"
                                >
                                    <Phone className="w-4 h-4" />
                                </a>
                            )}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

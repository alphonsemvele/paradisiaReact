import { useEffect, useRef, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/components/layouts/AppLayout';
import {
    Ticket as TicketIcon, LogIn, UserPlus, Smartphone, Phone, Check,
    Loader2, MessageCircle, Copy, ChevronRight, ShieldCheck, Clock, Users,
} from 'lucide-react';

interface Prix { type: string; montant: number; normal: number; promo: boolean }
interface Equipe { id: number; nom: string; trait: string | null; couleur: string }
interface TicketData {
    id: number; reference: string; code: string; type: string; type_libelle: string;
    montant: number; promo: boolean; statut: string; moyen: string;
    equipe: string | null; couleur: string | null; whatsapp: string | null;
    titulaire: string; date: string | null; paye_le: string | null;
}
interface Props {
    festy: { titre: string; date_label: string | null };
    prix: { participant: Prix; fan: Prix };
    promo_fin: string | null;
    moi: { nom: string; telephone: string | null; email: string } | null;
    equipe: { id: number; nom: string; couleur: string; whatsapp: string | null } | null;
    equipes: Equipe[];
    tickets: TicketData[];
    en_attente: TicketData | null;
    om: { ussd: string; code_marchand: string; whatsapp: string };
    mtn_disponible: boolean;
}

const fcfa = (n: number) => n.toLocaleString('fr-FR') + ' FCFA';

function csrf(): string {
    const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : '';
}
async function postJSON(url: string, body: Record<string, unknown>) {
    const r = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json', Accept: 'application/json',
            'X-XSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
    const data = await r.json().catch(() => ({}));
    return { ok: r.ok, status: r.status, data } as { ok: boolean; status: number; data: any };
}

export default function FestyTicket({ festy, prix, promo_fin, moi, equipe, equipes, tickets, en_attente, om, mtn_disponible }: Props) {
    const page = usePage();
    const { auth } = page.props as any;
    const connecte = !!auth?.user && !!moi;
    const flash = (page.props as any).flash?.success as string | undefined;

    const [type, setType] = useState<'participant' | 'fan'>('participant');
    const [moyen, setMoyen] = useState<'mtn' | 'om' | null>(null);
    const [tel, setTel] = useState(moi?.telephone ?? '');
    const [busy, setBusy] = useState(false);
    const [erreur, setErreur] = useState<string | null>(null);

    // Écran d'attente MTN (polling) + confirmation.
    const [vue, setVue] = useState<'form' | 'mtn_attente' | 'succes' | 'om_ok'>('form');
    const [ussd, setUssd] = useState<string | null>(null);
    const [omMontant, setOmMontant] = useState<number>(0);
    const pollRef = useRef<number | null>(null);

    const p = prix[type];

    // Reprise du paiement MTN après retour de redirection (?ref=...).
    useEffect(() => {
        const ref = new URLSearchParams(page.url.split('?')[1] ?? '').get('ref');
        if (ref) { setVue('mtn_attente'); suivre(ref); }
        return () => { if (pollRef.current) window.clearInterval(pollRef.current); };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const suivre = (reference: string) => {
        if (pollRef.current) window.clearInterval(pollRef.current);
        const tick = async () => {
            const { data } = await postJSONget(`/festy/ticket/statut/${reference}`);
            if (data?.termine) {
                if (pollRef.current) window.clearInterval(pollRef.current);
                if (data.reussi) { setVue('succes'); setTimeout(() => router.reload(), 1500); }
                else { setErreur(data.message ?? "Le paiement n'a pas abouti."); setVue('form'); }
            }
        };
        tick();
        pollRef.current = window.setInterval(tick, 4000);
    };

    const payerMtn = async () => {
        setErreur(null);
        if (!tel.trim() || tel.replace(/\D/g, '').length < 8) { setErreur('Entre un numéro MTN valide.'); return; }
        setBusy(true);
        const { ok, data } = await postJSON('/festy/ticket/mobile', { type, telephone: tel });
        setBusy(false);
        if (!ok) { setErreur(data?.message ?? 'Paiement impossible pour le moment.'); return; }
        if (data.url_paiement) { window.location.href = data.url_paiement; return; }
        setUssd(data.code_ussd ?? null);
        setVue('mtn_attente');
        suivre(data.reference);
    };

    const commanderOm = async () => {
        setErreur(null);
        setBusy(true);
        const { ok, data } = await postJSON('/festy/ticket/manuel', { type });
        setBusy(false);
        if (!ok) { setErreur(data?.message ?? 'Réservation impossible pour le moment.'); return; }
        setOmMontant(data.montant ?? p.montant);
        setVue('om_ok');
    };

    const waLink = (montant: number) =>
        `https://wa.me/${om.whatsapp}?text=${encodeURIComponent(
            `Bonjour, j'ai payé mon ticket ${type === 'fan' ? 'Fan' : 'Participant'} PARADISIA FESTY (${fcfa(montant)}) par Orange Money. Voici la preuve :`,
        )}`;

    return (
        <AppLayout>
            <Head title={`Ticket — ${festy.titre}`} />

            {/* Héro */}
            <div style={{ background: 'linear-gradient(135deg,#0b2e1a,#14532d)', color: '#fff' }}>
                <div className="max-w-3xl mx-auto px-4 py-10 text-center">
                    <div className="inline-flex items-center gap-2 bg-white/10 rounded-full px-4 py-1.5 text-sm font-semibold text-amber-300">
                        <TicketIcon className="w-4 h-4" /> Billetterie officielle
                    </div>
                    <h1 className="mt-3 text-3xl sm:text-5xl font-extrabold tracking-tight">PARADISIA FESTY</h1>
                    {festy.date_label && <p className="mt-2 text-lg font-bold text-amber-400">{festy.date_label}</p>}
                    {promo_fin && (
                        <p className="mt-3 inline-block bg-amber-400 text-emerald-950 font-bold text-sm px-4 py-1.5 rounded-full">
                            🔥 Tarif promo jusqu'au {promo_fin}
                        </p>
                    )}
                </div>
            </div>

            <div className="max-w-md mx-auto px-4 py-8">
                {flash && <div className="mb-4 rounded-xl bg-emerald-600 text-white px-4 py-2.5 text-sm font-semibold text-center">{flash}</div>}

                {!connecte ? (
                    <GuestCard />
                ) : (
                    <>
                        {/* Mes tickets déjà payés */}
                        {tickets.map((t) => (
                            <div key={t.id} className="mb-4">
                                <TicketCard t={t} />
                                {!t.equipe && <ChoixEquipe equipes={equipes} note="Ton ticket est validé — choisis ton équipe pour rejoindre son groupe." />}
                            </div>
                        ))}

                        {/* Rappel équipe (si pas de ticket mais déjà inscrit à une équipe) */}
                        {tickets.length === 0 && equipe && (
                            <div className="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 flex items-center gap-3">
                                <span className="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold" style={{ background: equipe.couleur }}>{equipe.nom[0]}</span>
                                <p className="text-sm text-emerald-900">Tu es dans l'équipe <b>{equipe.nom}</b>. Prends ton ticket ci-dessous.</p>
                            </div>
                        )}

                        {/* Paiement Orange en attente de validation */}
                        {en_attente && vue === 'form' && (
                            <div className="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                <div className="flex items-center gap-2 text-amber-800 font-bold text-sm"><Clock className="w-4 h-4" /> Ticket {en_attente.type_libelle} en attente</div>
                                <p className="text-xs text-amber-700 mt-1">Nous validons ton ticket dès réception de ta preuve de paiement Orange Money sur WhatsApp.</p>
                                <a href={waLink(en_attente.montant)} target="_blank" rel="noopener noreferrer"
                                    className="mt-3 inline-flex items-center gap-2 bg-[#25D366] text-white font-bold text-sm px-4 py-2 rounded-xl">
                                    <MessageCircle className="w-4 h-4" /> Envoyer ma preuve
                                </a>
                            </div>
                        )}

                        {/* ── Écrans ── */}
                        {vue === 'succes' ? (
                            <div className="rounded-2xl border border-emerald-200 bg-white p-8 text-center">
                                <div className="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3"><Check className="w-8 h-8 text-emerald-600" /></div>
                                <h2 className="text-xl font-extrabold text-zinc-900">Paiement confirmé 🎉</h2>
                                <p className="text-sm text-zinc-500 mt-1">Ton ticket t'a été envoyé par e-mail. On l'affiche ici…</p>
                                <Loader2 className="w-5 h-5 text-emerald-500 animate-spin mx-auto mt-4" />
                            </div>
                        ) : vue === 'mtn_attente' ? (
                            <div className="rounded-2xl border border-zinc-200 bg-white p-8 text-center">
                                <div className="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-3"><Smartphone className="w-8 h-8 text-amber-500" /></div>
                                <h2 className="text-lg font-bold text-zinc-900">Validez sur votre téléphone</h2>
                                <p className="text-sm text-zinc-500 mt-1">Une demande de paiement MTN Mobile Money a été envoyée. Saisissez votre code secret pour confirmer.</p>
                                {ussd && (
                                    <p className="mt-3 text-sm text-zinc-600">Rien reçu ? Composez <b className="font-mono">{ussd}</b>.</p>
                                )}
                                <div className="flex items-center justify-center gap-2 text-emerald-600 text-sm mt-5"><Loader2 className="w-4 h-4 animate-spin" /> En attente de confirmation…</div>
                                <button onClick={() => { if (pollRef.current) window.clearInterval(pollRef.current); setVue('form'); }} className="mt-4 text-xs text-zinc-400 hover:text-zinc-600">Annuler</button>
                            </div>
                        ) : vue === 'om_ok' ? (
                            <OrangeInstructions om={om} montant={omMontant} type={type} waLink={waLink(omMontant)} onDone={() => router.reload()} />
                        ) : (
                            /* ── Formulaire d'achat ── */
                            <div className="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                                <h2 className="font-bold text-zinc-900 mb-1">Prends ton ticket</h2>
                                <p className="text-xs text-zinc-500 mb-4">Choisis ta formule et ton moyen de paiement.</p>

                                {/* Type */}
                                <div className="grid grid-cols-2 gap-3 mb-4">
                                    <TypeCard actif={type === 'participant'} onClick={() => setType('participant')}
                                        titre="Participant" sous="Je joue le challenge" prix={prix.participant} />
                                    <TypeCard actif={type === 'fan'} onClick={() => setType('fan')}
                                        titre="Fan" sous="Je soutiens mon équipe" prix={prix.fan} />
                                </div>

                                {/* Moyen de paiement */}
                                <p className="text-xs font-medium text-zinc-500 mb-2">Moyen de paiement</p>
                                <div className="space-y-2.5">
                                    {mtn_disponible && (
                                        <PayCard actif={moyen === 'mtn'} onClick={() => setMoyen('mtn')}
                                            couleur="#ffcc00" emoji="📱" titre="MTN Mobile Money" sous="Paiement instantané" />
                                    )}
                                    <PayCard actif={moyen === 'om'} onClick={() => setMoyen('om')}
                                        couleur="#ff7900" emoji="🟠" titre="Orange Money" sous="Code marchand + preuve WhatsApp" />
                                </div>

                                {erreur && <p className="mt-3 text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{erreur}</p>}

                                {/* MTN : numéro + payer */}
                                {moyen === 'mtn' && (
                                    <div className="mt-4">
                                        <label className="block text-xs font-medium text-zinc-500 mb-1">Numéro MTN</label>
                                        <div className="relative">
                                            <Phone className="absolute left-3 top-3 w-4 h-4 text-zinc-400" />
                                            <input value={tel} onChange={(e) => setTel(e.target.value)} inputMode="tel" placeholder="6XX XX XX XX"
                                                className="w-full pl-9 pr-3 py-2.5 rounded-xl border border-zinc-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                                        </div>
                                        <button onClick={payerMtn} disabled={busy}
                                            className="mt-4 w-full py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold flex items-center justify-center gap-2 disabled:opacity-60">
                                            {busy ? <Loader2 className="w-5 h-5 animate-spin" /> : <>Payer {fcfa(p.montant)} <ChevronRight className="w-4 h-4" /></>}
                                        </button>
                                    </div>
                                )}

                                {/* Orange : réserver puis instructions */}
                                {moyen === 'om' && (
                                    <button onClick={commanderOm} disabled={busy}
                                        className="mt-4 w-full py-3 rounded-xl text-white font-bold flex items-center justify-center gap-2 disabled:opacity-60" style={{ background: '#ff7900' }}>
                                        {busy ? <Loader2 className="w-5 h-5 animate-spin" /> : <>Payer {fcfa(p.montant)} par Orange Money <ChevronRight className="w-4 h-4" /></>}
                                    </button>
                                )}

                                <p className="mt-4 flex items-center justify-center gap-1.5 text-[11px] text-zinc-400"><ShieldCheck className="w-3.5 h-3.5" /> Paiement sécurisé · ticket envoyé par e-mail</p>
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}

/* ─────────────── Sous-composants ─────────────── */

function TypeCard({ actif, onClick, titre, sous, prix }: { actif: boolean; onClick: () => void; titre: string; sous: string; prix: Prix }) {
    return (
        <button onClick={onClick} type="button"
            className={`text-left rounded-xl border-2 p-3 transition-colors ${actif ? 'border-emerald-600 bg-emerald-50' : 'border-zinc-200 bg-white hover:border-zinc-300'}`}>
            <div className="flex items-center justify-between">
                <span className="font-bold text-sm text-zinc-900">{titre}</span>
                {actif && <Check className="w-4 h-4 text-emerald-600" />}
            </div>
            <p className="text-[11px] text-zinc-400 mb-1.5">{sous}</p>
            <div className="flex items-baseline gap-1.5">
                <span className="font-extrabold text-emerald-700">{fcfa(prix.montant)}</span>
                {prix.promo && <span className="text-[11px] text-zinc-400 line-through">{fcfa(prix.normal)}</span>}
            </div>
        </button>
    );
}

function PayCard({ actif, onClick, couleur, emoji, titre, sous }: { actif: boolean; onClick: () => void; couleur: string; emoji: string; titre: string; sous: string }) {
    return (
        <button onClick={onClick} type="button"
            className={`w-full flex items-center gap-3 rounded-xl border-2 p-3 text-left transition-colors ${actif ? 'border-emerald-600 bg-emerald-50' : 'border-zinc-200 bg-white hover:border-zinc-300'}`}>
            <span className="w-10 h-10 rounded-lg flex items-center justify-center text-xl" style={{ background: `${couleur}22` }}>{emoji}</span>
            <div className="flex-1 min-w-0">
                <p className="font-bold text-sm text-zinc-900">{titre}</p>
                <p className="text-[11px] text-zinc-400">{sous}</p>
            </div>
            {actif && <Check className="w-4 h-4 text-emerald-600" />}
        </button>
    );
}

function OrangeInstructions({ om, montant, type, waLink, onDone }: { om: Props['om']; montant: number; type: string; waLink: string; onDone: () => void }) {
    const [copie, setCopie] = useState(false);
    const copier = () => { navigator.clipboard?.writeText(om.code_marchand); setCopie(true); setTimeout(() => setCopie(false), 1500); };
    return (
        <div className="rounded-2xl border-2 p-5 bg-white" style={{ borderColor: '#ff7900' }}>
            <div className="flex items-center gap-2 mb-3">
                <span className="w-9 h-9 rounded-lg flex items-center justify-center text-lg" style={{ background: '#ff790022' }}>🟠</span>
                <h2 className="font-bold text-zinc-900">Paiement Orange Money</h2>
            </div>
            <p className="text-sm text-zinc-500 mb-4">Ticket <b>{type === 'fan' ? 'Fan' : 'Participant'}</b> — <b className="text-zinc-800">{fcfa(montant)}</b>. Suis ces étapes :</p>

            <ol className="space-y-2.5 mb-4">
                <Etape n={1}>Compose <b className="font-mono text-zinc-900">{om.ussd}</b> sur ta ligne Orange.</Etape>
                <Etape n={2}>
                    Paie au code marchand
                    <span className="inline-flex items-center gap-1.5 ml-1 align-middle">
                        <b className="font-mono bg-zinc-100 rounded px-1.5 py-0.5 text-zinc-900">{om.code_marchand}</b>
                        <button onClick={copier} className="text-orange-600"><Copy className="w-3.5 h-3.5" /></button>
                        {copie && <span className="text-[11px] text-emerald-600">copié</span>}
                    </span>
                </Etape>
                <Etape n={3}>Montant : <b className="text-zinc-900">{fcfa(montant)}</b>.</Etape>
                <Etape n={4}>Envoie la <b>capture de confirmation</b> sur WhatsApp — on valide ton ticket aussitôt.</Etape>
            </ol>

            <a href={waLink} target="_blank" rel="noopener noreferrer"
                className="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold">
                <MessageCircle className="w-5 h-5" /> Envoyer ma preuve sur WhatsApp
            </a>
            <button onClick={onDone} className="mt-3 w-full text-sm text-zinc-500 hover:text-zinc-700">J'ai terminé</button>
        </div>
    );
}

function Etape({ n, children }: { n: number; children: React.ReactNode }) {
    return (
        <li className="flex gap-3 text-sm text-zinc-600">
            <span className="w-6 h-6 rounded-full bg-orange-100 text-orange-700 font-bold flex items-center justify-center flex-shrink-0 text-xs">{n}</span>
            <span className="pt-0.5">{children}</span>
        </li>
    );
}

function TicketCard({ t }: { t: TicketData }) {
    const couleur = t.couleur ?? '#F5B301';
    return (
        <div className="rounded-2xl overflow-hidden shadow-sm border border-zinc-200 bg-white">
            <div className="p-4 text-white relative" style={{ background: 'linear-gradient(135deg,#0b2e1a,#14532d)' }}>
                <div className="flex items-center justify-between">
                    <span className="text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-full text-emerald-950" style={{ background: couleur }}>Ticket {t.type_libelle}</span>
                    <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-200"><Check className="w-3.5 h-3.5" /> Validé</span>
                </div>
                <p className="mt-3 text-xl font-extrabold">PARADISIA FESTY</p>
                <p className="text-xs text-white/70">{t.equipe ? `Équipe ${t.equipe}` : 'Le grand challenge'}</p>
            </div>
            <div className="border-t-2 border-dashed border-zinc-200 relative">
                <span className="absolute -left-2 -top-2 w-4 h-4 rounded-full bg-white border border-zinc-200" />
                <span className="absolute -right-2 -top-2 w-4 h-4 rounded-full bg-white border border-zinc-200" />
            </div>
            <div className="p-4 flex items-center justify-between">
                <div>
                    <p className="text-[10px] uppercase tracking-wide text-zinc-400">Code du ticket</p>
                    <p className="text-lg font-extrabold font-mono tracking-widest text-emerald-800">{t.code}</p>
                    <p className="text-[11px] text-zinc-400 mt-0.5">{t.titulaire} · {fcfa(t.montant)}</p>
                </div>
                {t.whatsapp && (
                    <a href={t.whatsapp} target="_blank" rel="noopener noreferrer"
                        className="flex items-center gap-1.5 bg-[#25D366] text-white text-xs font-bold px-3 py-2 rounded-lg">
                        <MessageCircle className="w-4 h-4" /> Groupe
                    </a>
                )}
            </div>
        </div>
    );
}

function ChoixEquipe({ equipes, note }: { equipes: Equipe[]; note: string }) {
    const [open, setOpen] = useState(false);
    const [choix, setChoix] = useState<number | null>(null);
    const [busy, setBusy] = useState(false);
    const valider = () => {
        if (!choix) return;
        setBusy(true);
        router.post('/festy/ticket/equipe', { festy_team_id: choix }, { preserveScroll: true, onFinish: () => setBusy(false), onSuccess: () => setOpen(false) });
    };
    return (
        <div className="mt-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3">
            <p className="text-xs text-emerald-800 mb-2 flex items-center gap-1.5"><Users className="w-3.5 h-3.5" /> {note}</p>
            {!open ? (
                <button onClick={() => setOpen(true)} className="text-sm font-bold text-emerald-700 hover:text-emerald-900">Choisir mon équipe →</button>
            ) : (
                <div className="grid grid-cols-2 gap-2">
                    {equipes.map((e) => (
                        <button key={e.id} onClick={() => setChoix(e.id)}
                            className={`flex items-center gap-2 rounded-lg border-2 px-2.5 py-2 text-left ${choix === e.id ? 'border-emerald-600 bg-white' : 'border-transparent bg-white/70'}`}>
                            <span className="w-6 h-6 rounded-full text-white text-xs font-bold flex items-center justify-center" style={{ background: e.couleur }}>{e.nom[0]}</span>
                            <span className="text-xs font-semibold text-zinc-800 truncate">{e.nom}</span>
                        </button>
                    ))}
                    <button onClick={valider} disabled={!choix || busy}
                        className="col-span-2 mt-1 py-2 rounded-lg bg-emerald-700 text-white text-sm font-bold disabled:opacity-60">
                        {busy ? 'Validation…' : 'Rejoindre cette équipe'}
                    </button>
                </div>
            )}
        </div>
    );
}

function GuestCard() {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 shadow-sm p-7 text-center">
            <div className="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4"><TicketIcon className="w-8 h-8 text-emerald-600" /></div>
            <h2 className="text-xl font-bold text-zinc-900">Prends ton ticket Festy</h2>
            <p className="mt-2 text-sm text-zinc-500">Connecte-toi ou crée ton compte pour acheter ton ticket et rejoindre ton équipe.</p>
            <div className="mt-6 space-y-2.5">
                <Link href="/login" className="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold"><LogIn className="w-5 h-5" /> Se connecter</Link>
                <Link href="/register" className="w-full flex items-center justify-center gap-2 py-3 rounded-xl border border-emerald-600 text-emerald-700 hover:bg-emerald-50 font-bold"><UserPlus className="w-5 h-5" /> Créer un compte</Link>
            </div>
        </div>
    );
}

/* GET JSON helper (statut). */
async function postJSONget(url: string) {
    const r = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
    const data = await r.json().catch(() => ({}));
    return { ok: r.ok, data } as { ok: boolean; data: any };
}

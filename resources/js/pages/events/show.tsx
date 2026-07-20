import { FormEvent, useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    Calendar, Clock, MapPin, Monitor, Radio, ArrowLeft, FileDown,
    Mail, User, Phone, Globe, CheckCircle2, TrendingUp, Users, ChevronLeft, ChevronRight,
} from 'lucide-react';
import AppLayout from '@/components/layouts/AppLayout';
import ShareButtons from '@/components/ShareButtons';
import type { PageProps } from '@/types';

interface EventData {
    id: number;
    titre: string;
    description: string | null;
    type: string;
    mode: string;
    mode_label: string;
    lieu: string | null;
    date_label: string;
    date_fin_label: string | null;
    passe: boolean;
    image: string | null;
    images_galerie: string[];
    document: string | null;
    document_nom: string | null;
    collecte_pays: boolean;
    collecte_profil: boolean;
    collecte_telephone: boolean;
    collecte_nom: boolean;
    inscriptions_ouvertes: boolean;
    places_restantes: number | null;
}

interface ShowProps extends PageProps {
    event: EventData;
    pays: { code: string; nom: string }[];
    flash: { success?: string; info?: string };
}

export default function EventShow() {
    const { event, pays, flash } = usePage<ShowProps>().props;
    const galerie = event.images_galerie?.length ? event.images_galerie : event.image ? [event.image] : [];
    const [imgActive, setImgActive] = useState(0);

    const { data, setData, post, processing, errors, recentlySuccessful, reset } = useForm<any>({
        email: '',
        nom: '',
        pays: '',
        telephone: '',
        profil: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(`/events/${event.id}/inscription`, {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const ModeIcon = event.mode === 'presentiel' ? MapPin : event.mode === 'hybride' ? Radio : Monitor;

    return (
        <AppLayout>
            <Head title={`${event.titre} - Paradisia`} />

            <div className="max-w-6xl mx-auto px-4 py-8">
                <Link href="/events" className="inline-flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900 mb-6">
                    <ArrowLeft className="w-4 h-4" /> Tous les événements
                </Link>

                <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    {/* Détail */}
                    <div className="lg:col-span-3">
                        <div className="relative aspect-video bg-zinc-100 rounded-2xl overflow-hidden">
                            {galerie.length > 0 ? (
                                <img src={galerie[imgActive]} alt={event.titre} className="w-full h-full object-cover" />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-100 to-teal-100">
                                    <Calendar className="w-14 h-14 text-emerald-500" />
                                </div>
                            )}
                            {galerie.length > 1 && (
                                <>
                                    <button type="button" onClick={() => setImgActive((c) => (c - 1 + galerie.length) % galerie.length)}
                                        className="absolute left-3 top-1/2 -translate-y-1/2 p-2 bg-black/40 hover:bg-black/60 text-white rounded-full">
                                        <ChevronLeft className="w-5 h-5" />
                                    </button>
                                    <button type="button" onClick={() => setImgActive((c) => (c + 1) % galerie.length)}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-black/40 hover:bg-black/60 text-white rounded-full">
                                        <ChevronRight className="w-5 h-5" />
                                    </button>
                                    <span className="absolute bottom-3 right-3 px-2.5 py-1 bg-black/50 text-white text-xs font-medium rounded-full">
                                        {imgActive + 1} / {galerie.length}
                                    </span>
                                </>
                            )}
                        </div>
                        {galerie.length > 1 && (
                            <div className="mt-3 flex gap-2 overflow-x-auto pb-1">
                                {galerie.map((url, i) => (
                                    <button key={i} type="button" onClick={() => setImgActive(i)}
                                        className={`flex-shrink-0 w-20 h-14 rounded-lg overflow-hidden border-2 transition-all ${imgActive === i ? 'border-emerald-500' : 'border-transparent opacity-60 hover:opacity-100'}`}>
                                        <img src={url} alt="" loading="lazy" className="w-full h-full object-cover" />
                                    </button>
                                ))}
                            </div>
                        )}

                        <span className="inline-block mt-5 bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full">
                            {event.type}
                        </span>
                        <h1 className="mt-3 text-2xl lg:text-3xl font-bold text-zinc-900">{event.titre}</h1>

                        <div className="mt-4 flex flex-wrap gap-3">
                            <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-medium">
                                <Calendar className="w-4 h-4" /> {event.date_label}
                            </span>
                            <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium">
                                <ModeIcon className="w-4 h-4" /> {event.mode_label}
                            </span>
                            {event.lieu && (
                                <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-zinc-100 text-zinc-700 rounded-lg text-sm font-medium">
                                    <MapPin className="w-4 h-4" /> {event.lieu}
                                </span>
                            )}
                            {event.date_fin_label && (
                                <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-zinc-100 text-zinc-700 rounded-lg text-sm font-medium">
                                    <Clock className="w-4 h-4" /> Fin : {event.date_fin_label}
                                </span>
                            )}
                        </div>

                        {event.description && (
                            <p className="mt-6 text-zinc-600 whitespace-pre-line leading-relaxed">{event.description}</p>
                        )}

                        {event.document && (
                            <a href={event.document} target="_blank" rel="noopener noreferrer"
                                className="mt-6 inline-flex items-center gap-2 px-4 py-2.5 border border-emerald-200 text-emerald-700 hover:bg-emerald-50 rounded-lg text-sm font-medium">
                                <FileDown className="w-4 h-4" /> Télécharger le document
                            </a>
                        )}

                        <div className="mt-6">
                            <ShareButtons path={`/events/${event.id}`}
                                text={`${event.titre} — ${event.mode_label} chez Paradisia`}
                                label="Partager cet événement" />
                        </div>
                    </div>

                    {/* Inscription */}
                    <div className="lg:col-span-2">
                        <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }}
                            className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6 lg:sticky lg:top-24">

                            {event.passe ? (
                                <div className="text-center py-6">
                                    <Clock className="w-10 h-10 text-zinc-300 mx-auto mb-2" />
                                    <p className="text-sm text-zinc-500">Cet événement est terminé.</p>
                                </div>
                            ) : !event.inscriptions_ouvertes ? (
                                <div className="text-center py-6">
                                    <Users className="w-10 h-10 text-zinc-300 mx-auto mb-2" />
                                    <p className="text-sm text-zinc-500">Les inscriptions sont closes.</p>
                                </div>
                            ) : (
                                <>
                                    <div className="flex items-center gap-3 mb-5">
                                        <div className="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                                            <Calendar className="w-5 h-5 text-emerald-600" />
                                        </div>
                                        <div>
                                            <h2 className="font-semibold text-zinc-900">S'inscrire</h2>
                                            <p className="text-xs text-zinc-500">
                                                {event.places_restantes !== null
                                                    ? `${event.places_restantes} place(s) restante(s)`
                                                    : 'Recevez le lien le moment venu'}
                                            </p>
                                        </div>
                                    </div>

                                    {recentlySuccessful && flash.success && (
                                        <div className="mb-4 flex items-start gap-3 rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                                            <CheckCircle2 className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                                            <p className="text-sm text-emerald-800">{flash.success}</p>
                                        </div>
                                    )}
                                    {flash.info && (
                                        <div className="mb-4 rounded-lg bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800">
                                            {flash.info}
                                        </div>
                                    )}

                                    <form onSubmit={submit} className="space-y-4">
                                        <Champ icon={Mail} label="Adresse e-mail" error={errors.email}>
                                            <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                                                required placeholder="vous@exemple.com" className="input" />
                                        </Champ>

                                        {event.collecte_nom && (
                                            <Champ icon={User} label="Nom complet" error={errors.nom}>
                                                <input type="text" value={data.nom} onChange={(e) => setData('nom', e.target.value)}
                                                    required className="input" />
                                            </Champ>
                                        )}

                                        {event.collecte_pays && (
                                            <Champ icon={Globe} label="Pays" error={errors.pays}>
                                                <select value={data.pays} onChange={(e) => setData('pays', e.target.value)}
                                                    required className="input">
                                                    <option value="">Sélectionnez votre pays</option>
                                                    {pays.map((p) => <option key={p.code} value={p.nom}>{p.nom}</option>)}
                                                </select>
                                            </Champ>
                                        )}

                                        {event.collecte_telephone && (
                                            <Champ icon={Phone} label="Téléphone (facultatif)" error={errors.telephone}>
                                                <input type="tel" value={data.telephone} onChange={(e) => setData('telephone', e.target.value)}
                                                    className="input" placeholder="+237 6 99 88 77 66" />
                                            </Champ>
                                        )}

                                        {event.collecte_profil && (
                                            <div>
                                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">Vous êtes</label>
                                                <div className="grid grid-cols-2 gap-3">
                                                    <button type="button" onClick={() => setData('profil', 'investisseur')}
                                                        className={`flex flex-col items-center gap-1.5 py-3 rounded-xl border-2 text-sm font-medium transition-all ${data.profil === 'investisseur' ? 'border-violet-500 bg-violet-50 text-violet-700' : 'border-zinc-200 bg-zinc-50 text-zinc-600'}`}>
                                                        <TrendingUp className="w-5 h-5" /> Investisseur
                                                    </button>
                                                    <button type="button" onClick={() => setData('profil', 'participant')}
                                                        className={`flex flex-col items-center gap-1.5 py-3 rounded-xl border-2 text-sm font-medium transition-all ${data.profil === 'participant' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-zinc-200 bg-zinc-50 text-zinc-600'}`}>
                                                        <Users className="w-5 h-5" /> Participant
                                                    </button>
                                                </div>
                                                {errors.profil && <p className="mt-1 text-xs text-red-600">{errors.profil}</p>}
                                            </div>
                                        )}

                                        <button type="submit" disabled={processing}
                                            className="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-300 text-white font-semibold rounded-lg transition-colors mt-2">
                                            {processing ? 'Envoi…' : "M'inscrire"}
                                        </button>

                                        <p className="text-xs text-zinc-400 text-center">
                                            Le lien de la réunion vous sera envoyé par e-mail le moment venu.
                                        </p>
                                    </form>
                                </>
                            )}
                        </motion.div>
                    </div>
                </div>
            </div>

            <style>{`.input{width:100%;padding:.6rem .75rem .6rem 2.4rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.9rem}
            .input:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </AppLayout>
    );
}

function Champ({ icon: Icon, label, error, children }: { icon: any; label: string; error?: string; children: React.ReactNode }) {
    return (
        <div>
            <label className="block text-sm font-medium text-zinc-700 mb-1.5">{label}</label>
            <div className="relative">
                <Icon className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400 pointer-events-none" />
                {children}
            </div>
            {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
        </div>
    );
}

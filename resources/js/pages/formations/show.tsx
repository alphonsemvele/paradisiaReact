import { FormEvent, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    GraduationCap,
    Clock,
    CalendarDays,
    Tag,
    FileDown,
    User,
    Phone,
    Check,
    CheckCircle2,
    BookOpen,
    Zap,
    ArrowLeft,
    MapPin,
    Monitor,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';
import AppLayout from '@/components/layouts/AppLayout';

interface Formation {
    id: number;
    titre: string;
    description: string | null;
    prix: number;
    prix_formatte: string;
    prix_inscription: number;
    prix_inscription_formatte: string;
    duree: string | null;
    session: string | null;
    mode: string;
    mode_label: string;
    image: string | null;
    images: string[];
    document: string | null;
}

interface InscriptionData {
    nom: string;
    prenom: string;
    telephone: string;
    type: 'acceleree' | 'normale' | '';
    [key: string]: any;
}

export default function FormationShow({ formation }: { formation: Formation }) {
    const images = formation.images?.length ? formation.images : formation.image ? [formation.image] : [];
    const [current, setCurrent] = useState(0);

    const { data, setData, post, processing, errors, recentlySuccessful, reset } =
        useForm<InscriptionData>({
            nom: '',
            prenom: '',
            telephone: '',
            type: '',
        });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(`/formations/${formation.id}/inscription`, {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <AppLayout>
            <Head title={`${formation.titre} - Paradisia`} />

            <div className="max-w-6xl mx-auto px-4 py-8">
                <Link
                    href="/formations"
                    className="inline-flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900 mb-6"
                >
                    <ArrowLeft className="w-4 h-4" />
                    Toutes les formations
                </Link>

                <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    {/* ===== Détail formation ===== */}
                    <div className="lg:col-span-3">
                        {/* Galerie */}
                        <div className="relative aspect-video bg-zinc-100 rounded-2xl overflow-hidden">
                            {images.length > 0 ? (
                                <img src={images[current]} alt={formation.titre} className="w-full h-full object-cover" />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-100 to-orange-100">
                                    <GraduationCap className="w-14 h-14 text-emerald-500" />
                                </div>
                            )}
                            {images.length > 1 && (
                                <>
                                    <button
                                        type="button"
                                        onClick={() => setCurrent((c) => (c - 1 + images.length) % images.length)}
                                        className="absolute left-3 top-1/2 -translate-y-1/2 p-2 bg-black/40 hover:bg-black/60 text-white rounded-full transition-colors"
                                    >
                                        <ChevronLeft className="w-5 h-5" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrent((c) => (c + 1) % images.length)}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-black/40 hover:bg-black/60 text-white rounded-full transition-colors"
                                    >
                                        <ChevronRight className="w-5 h-5" />
                                    </button>
                                    <span className="absolute bottom-3 right-3 px-2.5 py-1 bg-black/50 text-white text-xs font-medium rounded-full">
                                        {current + 1} / {images.length}
                                    </span>
                                </>
                            )}
                        </div>
                        {images.length > 1 && (
                            <div className="mt-3 flex gap-2 overflow-x-auto pb-1">
                                {images.map((url, i) => (
                                    <button
                                        key={i}
                                        type="button"
                                        onClick={() => setCurrent(i)}
                                        className={`flex-shrink-0 w-20 h-14 rounded-lg overflow-hidden border-2 transition-all ${
                                            current === i ? 'border-emerald-500' : 'border-transparent opacity-60 hover:opacity-100'
                                        }`}
                                    >
                                        <img src={url} alt="" loading="lazy" className="w-full h-full object-cover" />
                                    </button>
                                ))}
                            </div>
                        )}

                        <h1 className="mt-6 text-2xl lg:text-3xl font-bold text-zinc-900">{formation.titre}</h1>

                        <div className="mt-4 flex flex-wrap gap-3">
                            <span
                                className={`inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium ${
                                    formation.mode === 'en_ligne' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700'
                                }`}
                            >
                                {formation.mode === 'en_ligne' ? <Monitor className="w-4 h-4" /> : <MapPin className="w-4 h-4" />}
                                {formation.mode_label}
                            </span>
                            {formation.duree && (
                                <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-medium">
                                    <Clock className="w-4 h-4" /> {formation.duree}
                                </span>
                            )}
                            {formation.session && (
                                <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium">
                                    <CalendarDays className="w-4 h-4" /> {formation.session}
                                </span>
                            )}
                            <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-50 text-orange-700 rounded-lg text-sm font-semibold">
                                <Tag className="w-4 h-4" /> Formation : {formation.prix_formatte}
                            </span>
                            {formation.prix_inscription > 0 && (
                                <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-violet-50 text-violet-700 rounded-lg text-sm font-semibold">
                                    <Tag className="w-4 h-4" /> Inscription : {formation.prix_inscription_formatte}
                                </span>
                            )}
                        </div>

                        {formation.description && (
                            <div className="mt-6 prose prose-zinc max-w-none">
                                <p className="text-zinc-600 whitespace-pre-line leading-relaxed">
                                    {formation.description}
                                </p>
                            </div>
                        )}

                        {formation.document && (
                            <a
                                href={formation.document}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-6 inline-flex items-center gap-2 px-4 py-2.5 border border-emerald-200 text-emerald-700 hover:bg-emerald-50 rounded-lg text-sm font-medium transition-colors"
                            >
                                <FileDown className="w-4 h-4" />
                                Télécharger le programme
                            </a>
                        )}
                    </div>

                    {/* ===== Formulaire d'inscription ===== */}
                    <div className="lg:col-span-2">
                        <motion.div
                            initial={{ opacity: 0, y: 12 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6 lg:sticky lg:top-24"
                        >
                            <div className="flex items-center gap-3 mb-5">
                                <div className="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                                    <GraduationCap className="w-5 h-5 text-emerald-600" />
                                </div>
                                <div>
                                    <h2 className="font-semibold text-zinc-900">S'inscrire</h2>
                                    <p className="text-xs text-zinc-500">Nous vous recontactons rapidement</p>
                                </div>
                            </div>

                            {recentlySuccessful && (
                                <motion.div
                                    initial={{ opacity: 0, height: 0 }}
                                    animate={{ opacity: 1, height: 'auto' }}
                                    className="mb-5 flex items-start gap-3 rounded-lg bg-emerald-50 border border-emerald-200 p-4"
                                >
                                    <CheckCircle2 className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                                    <p className="text-sm text-emerald-800">
                                        Votre inscription a bien été enregistrée. Merci !
                                    </p>
                                </motion.div>
                            )}

                            <form onSubmit={submit} className="space-y-4">
                                {/* Prénom */}
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">Prénom</label>
                                    <div className="relative">
                                        <User className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                        <input
                                            type="text"
                                            value={data.prenom}
                                            onChange={(e) => setData('prenom', e.target.value)}
                                            required
                                            className={`w-full pl-10 pr-4 py-2.5 bg-zinc-50 border ${
                                                errors.prenom ? 'border-red-300' : 'border-zinc-200'
                                            } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                                            placeholder="Aristide"
                                        />
                                    </div>
                                    {errors.prenom && <p className="mt-1 text-xs text-red-600">{errors.prenom}</p>}
                                </div>

                                {/* Nom */}
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">Nom</label>
                                    <div className="relative">
                                        <User className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                        <input
                                            type="text"
                                            value={data.nom}
                                            onChange={(e) => setData('nom', e.target.value)}
                                            required
                                            className={`w-full pl-10 pr-4 py-2.5 bg-zinc-50 border ${
                                                errors.nom ? 'border-red-300' : 'border-zinc-200'
                                            } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                                            placeholder="Nguefack"
                                        />
                                    </div>
                                    {errors.nom && <p className="mt-1 text-xs text-red-600">{errors.nom}</p>}
                                </div>

                                {/* Téléphone */}
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                        Téléphone <span className="text-zinc-400 font-normal">(optionnel)</span>
                                    </label>
                                    <div className="relative">
                                        <Phone className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                        <input
                                            type="tel"
                                            value={data.telephone}
                                            onChange={(e) => setData('telephone', e.target.value)}
                                            className={`w-full pl-10 pr-4 py-2.5 bg-zinc-50 border ${
                                                errors.telephone ? 'border-red-300' : 'border-zinc-200'
                                            } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                                            placeholder="+237 6 99 88 77 66"
                                        />
                                    </div>
                                    {errors.telephone && <p className="mt-1 text-xs text-red-600">{errors.telephone}</p>}
                                </div>

                                {/* Type */}
                                <div>
                                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                        Type de formation
                                    </label>
                                    <div className="grid grid-cols-2 gap-3">
                                        <button
                                            type="button"
                                            onClick={() => setData('type', 'normale')}
                                            className={`relative flex flex-col items-center gap-1.5 py-3 px-3 rounded-xl border-2 text-sm font-medium transition-all ${
                                                data.type === 'normale'
                                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                                    : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300'
                                            }`}
                                        >
                                            {data.type === 'normale' && (
                                                <Check className="absolute top-2 right-2 w-4 h-4 text-emerald-600" />
                                            )}
                                            <BookOpen className="w-5 h-5" />
                                            Normale
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setData('type', 'acceleree')}
                                            className={`relative flex flex-col items-center gap-1.5 py-3 px-3 rounded-xl border-2 text-sm font-medium transition-all ${
                                                data.type === 'acceleree'
                                                    ? 'border-orange-500 bg-orange-50 text-orange-700'
                                                    : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300'
                                            }`}
                                        >
                                            {data.type === 'acceleree' && (
                                                <Check className="absolute top-2 right-2 w-4 h-4 text-orange-600" />
                                            )}
                                            <Zap className="w-5 h-5" />
                                            Accélérée
                                        </button>
                                    </div>
                                    {errors.type && <p className="mt-1 text-xs text-red-600">{errors.type}</p>}
                                </div>

                                {formation.prix_inscription > 0 && (
                                    <div className="flex items-center justify-between rounded-lg bg-violet-50 border border-violet-100 px-4 py-2.5 text-sm">
                                        <span className="text-violet-700">Frais d'inscription</span>
                                        <span className="font-bold text-violet-800">{formation.prix_inscription_formatte}</span>
                                    </div>
                                )}

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full py-3 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-300 text-white font-semibold rounded-lg transition-colors mt-2"
                                >
                                    {processing ? 'Inscription...' : "Confirmer mon inscription"}
                                </button>
                            </form>
                        </motion.div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

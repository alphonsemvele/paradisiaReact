import { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/components/layouts/AppLayout';
import { Camera, CalendarDays, FileText, Heart, MessageSquare, Save, ExternalLink, Mail, Phone, Image as ImageIcon } from 'lucide-react';

interface Profil {
    id: number;
    name: string;
    last_name: string | null;
    email: string;
    phone: string | null;
    ville: string | null;
    description: string | null;
    photo: string | null;
    cover: string | null;
    membre_depuis: string | null;
    nb_publications: number;
}
interface Publication { id: number; texte: string | null; image: string | null; video: string | null; lien: string; date: string; likes: number; commentaires: number }
interface Props { profil: Profil; publications: Publication[] }

export default function MonProfil({ profil, publications }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;
    const [apercuPhoto, setApercuPhoto] = useState<string | null>(profil.photo);
    const [apercuCover, setApercuCover] = useState<string | null>(profil.cover);

    const { data, setData, post, processing, errors } = useForm<any>({
        name: profil.name ?? '',
        last_name: profil.last_name ?? '',
        ville: profil.ville ?? '',
        description: profil.description ?? '',
        photo: null as File | null,
        cover: null as File | null,
    });

    const choisirPhoto = (e: React.ChangeEvent<HTMLInputElement>) => {
        const f = e.target.files?.[0];
        if (!f) return;
        setData('photo', f);
        setApercuPhoto(URL.createObjectURL(f));
    };
    const choisirCover = (e: React.ChangeEvent<HTMLInputElement>) => {
        const f = e.target.files?.[0];
        if (!f) return;
        setData('cover', f);
        setApercuCover(URL.createObjectURL(f));
    };

    const enregistrer = (e: React.FormEvent) => {
        e.preventDefault();
        post('/profile', { forceFormData: true, preserveScroll: true });
    };

    return (
        <AppLayout>
            <Head title="Mon profil — Paradisia" />

            <form onSubmit={enregistrer} className="max-w-2xl mx-auto pb-10">
                {/* Couverture */}
                <div className="relative h-40 sm:h-52 bg-gradient-to-br from-emerald-600 to-emerald-800 sm:rounded-b-2xl overflow-hidden">
                    {apercuCover && <img src={apercuCover} alt="" className="w-full h-full object-cover" />}
                    <label className="absolute bottom-3 right-3 inline-flex items-center gap-1.5 bg-black/50 hover:bg-black/70 text-white text-xs font-semibold px-3 py-1.5 rounded-full cursor-pointer backdrop-blur">
                        <Camera className="w-4 h-4" /> Couverture
                        <input type="file" accept="image/*" className="hidden" onChange={choisirCover} />
                    </label>
                </div>

                {/* Photo + entête */}
                <div className="px-4 -mt-12 sm:-mt-14">
                    <div className="relative inline-block">
                        {apercuPhoto
                            ? <img src={apercuPhoto} alt="" className="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-white shadow-md" />
                            : <div className="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-emerald-100 text-emerald-700 text-4xl font-bold flex items-center justify-center border-4 border-white shadow-md">{(data.name || '?').charAt(0).toUpperCase()}</div>}
                        <label className="absolute bottom-1 right-1 w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center cursor-pointer shadow">
                            <Camera className="w-4 h-4" />
                            <input type="file" accept="image/*" className="hidden" onChange={choisirPhoto} />
                        </label>
                    </div>

                    <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-zinc-500">
                        {profil.membre_depuis && <span className="flex items-center gap-1"><CalendarDays className="w-3.5 h-3.5" />Membre depuis {profil.membre_depuis}</span>}
                        <span className="flex items-center gap-1"><FileText className="w-3.5 h-3.5" />{profil.nb_publications} publication{profil.nb_publications > 1 ? 's' : ''}</span>
                        <Link href={`/u/${profil.id}`} className="flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-medium"><ExternalLink className="w-3.5 h-3.5" />Voir mon profil public</Link>
                    </div>
                </div>

                {flash && <div className="mx-4 mt-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

                {/* Formulaire */}
                <div className="mt-4 mx-4 bg-white rounded-2xl border border-zinc-200 p-5 space-y-3">
                    <div className="grid grid-cols-2 gap-3">
                        <L label="Nom" err={errors.name}><input className="ipt" value={data.name} onChange={(e) => setData('name', e.target.value)} /></L>
                        <L label="Prénom"><input className="ipt" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} /></L>
                    </div>
                    <L label="Ville"><input className="ipt" value={data.ville} onChange={(e) => setData('ville', e.target.value)} placeholder="Ta ville" /></L>
                    <L label="Bio / description"><textarea className="ipt" rows={3} value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Parle un peu de toi…" /></L>

                    {/* Lecture seule */}
                    <div className="grid grid-cols-2 gap-3 pt-1">
                        <div className="text-xs text-zinc-500 flex items-center gap-1.5"><Mail className="w-3.5 h-3.5" />{profil.email}</div>
                        <div className="text-xs text-zinc-500 flex items-center gap-1.5"><Phone className="w-3.5 h-3.5" />{profil.phone || 'Aucun numéro'}</div>
                    </div>

                    <button type="submit" disabled={processing}
                        className="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm flex items-center justify-center gap-2 disabled:opacity-60">
                        <Save className="w-4 h-4" /> {processing ? 'Enregistrement…' : 'Enregistrer'}
                    </button>
                </div>

                {/* Mes publications */}
                <div className="mt-6 px-4">
                    <h2 className="text-sm font-bold text-zinc-500 uppercase tracking-wide mb-3">Mes publications</h2>
                    {publications.length === 0 ? (
                        <div className="text-center text-zinc-400 py-14 bg-white rounded-2xl border border-zinc-100">
                            <FileText className="w-10 h-10 mx-auto mb-2 opacity-40" /> Aucune publication.
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {publications.map((p) => (
                                <Link key={p.id} href={p.lien} className="block bg-white rounded-2xl border border-zinc-100 overflow-hidden hover:border-emerald-200 transition-colors">
                                    {p.image && <img src={p.image} alt="" className="w-full max-h-96 object-cover" />}
                                    {!p.image && p.video && <div className="h-40 bg-zinc-900 flex items-center justify-center text-white/70"><ImageIcon className="w-8 h-8" /></div>}
                                    <div className="p-4">
                                        {p.texte && <p className="text-sm text-zinc-800 whitespace-pre-wrap">{p.texte}</p>}
                                        <div className="mt-2 flex items-center gap-4 text-xs text-zinc-400">
                                            <span className="flex items-center gap-1"><Heart className="w-3.5 h-3.5" />{p.likes}</span>
                                            <span className="flex items-center gap-1"><MessageSquare className="w-3.5 h-3.5" />{p.commentaires}</span>
                                            <span className="ml-auto">{p.date}</span>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </form>

            <style>{`.ipt{width:100%;padding:.6rem .75rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.7rem;font-size:.9rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </AppLayout>
    );
}

function L({ label, err, children }: { label: string; err?: string; children: React.ReactNode }) {
    return (
        <label className="block">
            <span className="block text-xs font-medium text-zinc-500 mb-1">{label}</span>
            {children}
            {err && <p className="text-xs text-red-600 mt-1">{err}</p>}
        </label>
    );
}

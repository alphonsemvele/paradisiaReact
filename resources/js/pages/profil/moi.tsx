import { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/components/layouts/AppLayout';
import { Camera, CalendarDays, FileText, Heart, MessageSquare, Save, ExternalLink, MapPin, Pencil, X, Image as ImageIcon } from 'lucide-react';

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
    const [edition, setEdition] = useState(false);
    const nomComplet = [profil.name, profil.last_name].filter(Boolean).join(' ');

    return (
        <AppLayout>
            <Head title="Mon profil — Paradisia" />

            <div className="max-w-2xl mx-auto pb-10">
                {/* Couverture */}
                <div className="h-40 sm:h-52 bg-gradient-to-br from-emerald-600 to-emerald-800 sm:rounded-b-2xl overflow-hidden">
                    {profil.cover && <img src={profil.cover} alt="" className="w-full h-full object-cover" />}
                </div>

                {/* En-tête */}
                <div className="px-4 -mt-12 sm:-mt-14">
                    <div className="flex items-end justify-between">
                        {profil.photo
                            ? <img src={profil.photo} alt={nomComplet} className="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-white shadow-md" />
                            : <div className="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-emerald-100 text-emerald-700 text-4xl font-bold flex items-center justify-center border-4 border-white shadow-md">{profil.name.charAt(0).toUpperCase()}</div>}

                        <div className="mb-2 flex gap-2">
                            <button onClick={() => setEdition(true)}
                                className="inline-flex items-center gap-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 shadow-sm">
                                <Pencil className="w-4 h-4" /> Modifier le profil
                            </button>
                        </div>
                    </div>

                    <h1 className="mt-3 text-2xl font-bold text-zinc-900">{nomComplet}</h1>
                    {profil.description && <p className="mt-1 text-sm text-zinc-600 whitespace-pre-wrap">{profil.description}</p>}
                    <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-zinc-500">
                        {profil.ville && <span className="flex items-center gap-1"><MapPin className="w-3.5 h-3.5" />{profil.ville}</span>}
                        {profil.membre_depuis && <span className="flex items-center gap-1"><CalendarDays className="w-3.5 h-3.5" />Membre depuis {profil.membre_depuis}</span>}
                        <span className="flex items-center gap-1"><FileText className="w-3.5 h-3.5" />{profil.nb_publications} publication{profil.nb_publications > 1 ? 's' : ''}</span>
                        <Link href={`/u/${profil.id}`} className="flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-medium"><ExternalLink className="w-3.5 h-3.5" />Profil public</Link>
                    </div>
                </div>

                {flash && <div className="mx-4 mt-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

                {/* Publications */}
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
            </div>

            {edition && <EditionProfil profil={profil} onClose={() => setEdition(false)} />}
        </AppLayout>
    );
}

/* Fenêtre d'édition du profil */
function EditionProfil({ profil, onClose }: { profil: Profil; onClose: () => void }) {
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

    const choisir = (champ: 'photo' | 'cover', setter: (u: string) => void) => (e: React.ChangeEvent<HTMLInputElement>) => {
        const f = e.target.files?.[0];
        if (!f) return;
        setData(champ, f);
        setter(URL.createObjectURL(f));
    };

    const enregistrer = (e: React.FormEvent) => {
        e.preventDefault();
        post('/profile', { forceFormData: true, preserveScroll: true, onSuccess: () => onClose() });
    };

    return (
        <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onClick={onClose}>
            <form onClick={(e) => e.stopPropagation()} onSubmit={enregistrer}
                className="bg-white rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div className="sticky top-0 bg-white flex items-center justify-between px-5 py-3 border-b border-zinc-100">
                    <h3 className="font-bold text-zinc-900">Modifier le profil</h3>
                    <button type="button" onClick={onClose} className="text-zinc-400 hover:text-zinc-700"><X className="w-5 h-5" /></button>
                </div>

                {/* Couverture + photo */}
                <div className="relative h-28 bg-gradient-to-br from-emerald-600 to-emerald-800 overflow-hidden">
                    {apercuCover && <img src={apercuCover} alt="" className="w-full h-full object-cover" />}
                    <label className="absolute bottom-2 right-2 inline-flex items-center gap-1.5 bg-black/50 hover:bg-black/70 text-white text-xs font-semibold px-2.5 py-1 rounded-full cursor-pointer">
                        <Camera className="w-3.5 h-3.5" /> Couverture
                        <input type="file" accept="image/*" className="hidden" onChange={choisir('cover', setApercuCover)} />
                    </label>
                </div>
                <div className="px-5 -mt-8">
                    <div className="relative inline-block">
                        {apercuPhoto
                            ? <img src={apercuPhoto} alt="" className="w-16 h-16 rounded-full object-cover border-4 border-white shadow" />
                            : <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 text-xl font-bold flex items-center justify-center border-4 border-white shadow">{(data.name || '?').charAt(0).toUpperCase()}</div>}
                        <label className="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center cursor-pointer">
                            <Camera className="w-3 h-3" />
                            <input type="file" accept="image/*" className="hidden" onChange={choisir('photo', setApercuPhoto)} />
                        </label>
                    </div>
                </div>

                <div className="p-5 pt-3 space-y-3">
                    <div className="grid grid-cols-2 gap-3">
                        <L label="Nom" err={errors.name}><input className="ipt" value={data.name} onChange={(e) => setData('name', e.target.value)} /></L>
                        <L label="Prénom"><input className="ipt" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} /></L>
                    </div>
                    <L label="Ville"><input className="ipt" value={data.ville} onChange={(e) => setData('ville', e.target.value)} placeholder="Ta ville" /></L>
                    <L label="Bio"><textarea className="ipt" rows={3} value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Parle un peu de toi…" /></L>

                    <button type="submit" disabled={processing}
                        className="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm flex items-center justify-center gap-2 disabled:opacity-60">
                        <Save className="w-4 h-4" /> {processing ? 'Enregistrement…' : 'Enregistrer'}
                    </button>
                </div>
            </form>
            <style>{`.ipt{width:100%;padding:.6rem .75rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.7rem;font-size:.9rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </div>
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

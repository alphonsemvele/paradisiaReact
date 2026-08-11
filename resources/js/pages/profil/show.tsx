import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/components/layouts/AppLayout';
import { MessageCircle, MapPin, CalendarDays, Heart, MessageSquare, FileText, Image as ImageIcon } from 'lucide-react';

interface Profil {
    id: number;
    nom: string;
    photo: string | null;
    cover: string | null;
    ville: string | null;
    pays: string | null;
    description: string | null;
    membre_depuis: string | null;
    is_me: boolean;
    peut_ecrire: boolean;
    nb_publications: number;
}
interface Publication {
    id: number;
    texte: string | null;
    image: string | null;
    video: string | null;
    lien: string;
    date: string;
    likes: number;
    commentaires: number;
}
interface Props { profil: Profil; publications: Publication[] }

export default function ProfilShow({ profil, publications }: Props) {
    const { auth } = usePage().props as any;

    return (
        <AppLayout>
            <Head title={`${profil.nom} — Paradisia`} />

            <div className="max-w-2xl mx-auto pb-10">
                {/* Couverture */}
                <div className="h-40 sm:h-52 bg-gradient-to-br from-emerald-600 to-emerald-800 sm:rounded-b-2xl overflow-hidden">
                    {profil.cover && <img src={profil.cover} alt="" className="w-full h-full object-cover" />}
                </div>

                {/* En-tête */}
                <div className="px-4 -mt-12 sm:-mt-14">
                    <div className="flex items-end justify-between">
                        {profil.photo
                            ? <img src={profil.photo} alt={profil.nom} className="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-white shadow-md" />
                            : <div className="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-emerald-100 text-emerald-700 text-4xl font-bold flex items-center justify-center border-4 border-white shadow-md">{profil.nom.charAt(0).toUpperCase()}</div>}

                        <div className="mb-2 flex gap-2">
                            {profil.peut_ecrire && (
                                <Link href={`/messages/u/${profil.id}`}
                                    className="inline-flex items-center gap-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 shadow-sm">
                                    <MessageCircle className="w-4 h-4" /> Message
                                </Link>
                            )}
                            {profil.is_me && (
                                <Link href="/profile" className="inline-flex items-center gap-2 rounded-full border border-zinc-300 text-zinc-700 text-sm font-semibold px-4 py-2 hover:bg-zinc-50">
                                    Modifier
                                </Link>
                            )}
                            {!auth?.user && (
                                <Link href="/login" className="inline-flex items-center gap-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2">
                                    <MessageCircle className="w-4 h-4" /> Message
                                </Link>
                            )}
                        </div>
                    </div>

                    <h1 className="mt-3 text-2xl font-bold text-zinc-900">{profil.nom}</h1>
                    {profil.description && <p className="mt-1 text-sm text-zinc-600 whitespace-pre-wrap">{profil.description}</p>}
                    <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-zinc-500">
                        {(profil.ville || profil.pays) && <span className="flex items-center gap-1"><MapPin className="w-3.5 h-3.5" />{[profil.ville, profil.pays].filter(Boolean).join(', ')}</span>}
                        {profil.membre_depuis && <span className="flex items-center gap-1"><CalendarDays className="w-3.5 h-3.5" />Membre depuis {profil.membre_depuis}</span>}
                        <span className="flex items-center gap-1"><FileText className="w-3.5 h-3.5" />{profil.nb_publications} publication{profil.nb_publications > 1 ? 's' : ''}</span>
                    </div>
                </div>

                {/* Publications */}
                <div className="mt-6 px-4">
                    <h2 className="text-sm font-bold text-zinc-500 uppercase tracking-wide mb-3">Publications</h2>
                    {publications.length === 0 ? (
                        <div className="text-center text-zinc-400 py-14 bg-white rounded-2xl border border-zinc-100">
                            <FileText className="w-10 h-10 mx-auto mb-2 opacity-40" /> Aucune publication.
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {publications.map((p) => (
                                <Link key={p.id} href={p.lien} className="block bg-white rounded-2xl border border-zinc-100 overflow-hidden hover:border-emerald-200 transition-colors">
                                    {p.image && <img src={p.image} alt="" className="w-full max-h-96 object-cover" />}
                                    {!p.image && p.video && (
                                        <div className="h-40 bg-zinc-900 flex items-center justify-center text-white/70"><ImageIcon className="w-8 h-8" /></div>
                                    )}
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
        </AppLayout>
    );
}

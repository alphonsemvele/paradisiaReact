import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Users, Download, Phone, Mail, MapPin } from 'lucide-react';

interface Inscrit {
    id: number;
    nom: string;
    telephone: string;
    email: string | null;
    ville: string | null;
    equipe: string | null;
    date: string;
}
interface Props {
    inscrits: Inscrit[];
    equipes: { id: number; nom: string }[];
    filtre: number | null;
    total: number;
    par_equipe: { nom: string; couleur: string; membres: number }[];
}

export default function FestyInscrits({ inscrits, equipes, filtre, total, par_equipe }: Props) {
    const filtrer = (id: number | null) => {
        router.get('/admin/festy/inscrits', id ? { equipe: id } : {}, { preserveScroll: true, preserveState: true });
    };

    return (
        <AdminLayout title="Inscrits Festy">
            <Head title="Inscrits Festy — Admin" />

            <div className="flex items-center justify-between mb-5">
                <div>
                    <h1 className="text-xl font-bold text-zinc-900">Inscrits Paradisia Festy</h1>
                    <p className="text-sm text-zinc-500">{total} inscription{total > 1 ? 's' : ''} au total</p>
                </div>
                <a href="/admin/festy/export"
                    className="inline-flex items-center gap-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2.5">
                    <Download className="w-4 h-4" /> Exporter (CSV)
                </a>
            </div>

            {/* Répartition par équipe */}
            <div className="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
                {par_equipe.map((e) => (
                    <div key={e.nom} className="bg-white rounded-xl border border-zinc-200 p-3 text-center">
                        <p className="text-2xl font-bold" style={{ color: e.couleur }}>{e.membres}</p>
                        <p className="text-xs text-zinc-500">{e.nom}</p>
                    </div>
                ))}
            </div>

            {/* Filtres */}
            <div className="flex flex-wrap gap-2 mb-4">
                <Chip actif={!filtre} onClick={() => filtrer(null)}>Toutes</Chip>
                {equipes.map((eq) => (
                    <Chip key={eq.id} actif={filtre === eq.id} onClick={() => filtrer(eq.id)}>{eq.nom}</Chip>
                ))}
            </div>

            {/* Tableau */}
            <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Nom</th>
                                <th className="text-left px-4 py-3 font-medium">Équipe</th>
                                <th className="text-left px-4 py-3 font-medium">Téléphone</th>
                                <th className="text-left px-4 py-3 font-medium">E-mail</th>
                                <th className="text-left px-4 py-3 font-medium">Ville</th>
                                <th className="text-left px-4 py-3 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {inscrits.map((i) => (
                                <tr key={i.id} className="hover:bg-zinc-50">
                                    <td className="px-4 py-3 font-medium text-zinc-900">{i.nom}</td>
                                    <td className="px-4 py-3 text-zinc-600">{i.equipe ?? '—'}</td>
                                    <td className="px-4 py-3 text-zinc-600"><a href={`tel:${i.telephone}`} className="hover:text-emerald-700 inline-flex items-center gap-1"><Phone className="w-3 h-3" />{i.telephone}</a></td>
                                    <td className="px-4 py-3 text-zinc-600">{i.email ? <span className="inline-flex items-center gap-1"><Mail className="w-3 h-3" />{i.email}</span> : '—'}</td>
                                    <td className="px-4 py-3 text-zinc-600">{i.ville ? <span className="inline-flex items-center gap-1"><MapPin className="w-3 h-3" />{i.ville}</span> : '—'}</td>
                                    <td className="px-4 py-3 text-zinc-400 whitespace-nowrap">{i.date}</td>
                                </tr>
                            ))}
                            {inscrits.length === 0 && (
                                <tr><td colSpan={6} className="px-4 py-12 text-center text-zinc-400">
                                    <Users className="w-8 h-8 mx-auto mb-2 opacity-40" /> Aucun inscrit pour ce filtre.
                                </td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}

function Chip({ actif, onClick, children }: { actif: boolean; onClick: () => void; children: React.ReactNode }) {
    return (
        <button onClick={onClick}
            className={`px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors ${actif ? 'bg-emerald-700 text-white' : 'bg-white border border-zinc-200 text-zinc-600 hover:bg-zinc-50'}`}>
            {children}
        </button>
    );
}

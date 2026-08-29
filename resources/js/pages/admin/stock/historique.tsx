import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { ArrowLeft, ArrowDownCircle, ArrowUpCircle } from 'lucide-react';

interface Mvt { id: number; type: string; quantite: number; stock_apres: number | null; motif: string | null; date: string }
interface Props {
    materiel: { id: number; nom: string; unite: string; stock: number };
    mouvements: Mvt[];
}

const LIB: Record<string, string> = {
    achat: 'Achat', sortie_vente: 'Sortie (vente)', retour: 'Retour', production: 'Production', casse: 'Casse', ajustement: 'Ajustement',
};

export default function Historique({ materiel, mouvements }: Props) {
    return (
        <AdminLayout title="Historique du stock">
            <Head title={`Historique — ${materiel.nom}`} />

            <Link href="/admin/stock" className="inline-flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900 mb-4"><ArrowLeft className="w-4 h-4" /> Retour au stock</Link>

            <div className="bg-white rounded-2xl border border-zinc-200 p-5 mb-4">
                <h1 className="text-lg font-bold text-zinc-900">{materiel.nom}</h1>
                <p className="text-sm text-zinc-500">Stock actuel : <b className="text-zinc-800">{materiel.stock.toLocaleString('fr-FR')} {materiel.unite}</b></p>
            </div>

            <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Type</th>
                                <th className="text-right px-4 py-3 font-medium">Mouvement</th>
                                <th className="text-right px-4 py-3 font-medium">Stock après</th>
                                <th className="text-left px-4 py-3 font-medium">Motif</th>
                                <th className="text-left px-4 py-3 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {mouvements.map((m) => {
                                const entree = m.quantite >= 0;
                                return (
                                    <tr key={m.id} className="hover:bg-zinc-50">
                                        <td className="px-4 py-3 text-zinc-700">{LIB[m.type] ?? m.type}</td>
                                        <td className={`px-4 py-3 text-right font-bold ${entree ? 'text-emerald-600' : 'text-red-600'}`}>
                                            <span className="inline-flex items-center gap-1 justify-end">
                                                {entree ? <ArrowUpCircle className="w-3.5 h-3.5" /> : <ArrowDownCircle className="w-3.5 h-3.5" />}
                                                {entree ? '+' : ''}{m.quantite.toLocaleString('fr-FR')}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right text-zinc-600">{m.stock_apres !== null ? m.stock_apres.toLocaleString('fr-FR') : '—'}</td>
                                        <td className="px-4 py-3 text-zinc-500">{m.motif ?? '—'}</td>
                                        <td className="px-4 py-3 text-zinc-400 whitespace-nowrap">{m.date}</td>
                                    </tr>
                                );
                            })}
                            {mouvements.length === 0 && <tr><td colSpan={5} className="px-4 py-12 text-center text-zinc-400">Aucun mouvement pour l'instant.</td></tr>}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}

import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { ArrowLeft, Trash2, Factory } from 'lucide-react';

interface Item { materiel: string; unite: string; quantite: number; cout_unitaire: number; sous_total: number }
interface Props {
    achat: { id: number; fournisseur: string | null; date: string; total: number; pour_production: boolean; note: string | null; items: Item[] };
}

export default function AchatShow({ achat }: Props) {
    const supprimer = () => {
        if (confirm('Supprimer cet achat ? Le stock ajouté sera retiré.')) router.delete(`/admin/achats/${achat.id}`);
    };

    return (
        <AdminLayout title="Détail achat">
            <Head title={`Achat #${achat.id}`} />

            <div className="max-w-2xl mx-auto">
                <div className="flex items-center justify-between mb-4">
                    <Link href="/admin/achats" className="inline-flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900"><ArrowLeft className="w-4 h-4" /> Retour aux achats</Link>
                    <button onClick={supprimer} className="inline-flex items-center gap-1.5 text-sm text-red-600 hover:text-red-800"><Trash2 className="w-4 h-4" /> Supprimer</button>
                </div>

                <div className="bg-white rounded-2xl border border-zinc-200 p-5 mb-4">
                    <div className="flex items-start justify-between">
                        <div>
                            <h1 className="text-lg font-bold text-zinc-900">{achat.fournisseur || 'Achat'} #{achat.id}</h1>
                            <p className="text-sm text-zinc-500">{achat.date}</p>
                        </div>
                        {achat.pour_production && <span className="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-100 rounded-full px-2.5 py-1"><Factory className="w-3.5 h-3.5" /> Pour production</span>}
                    </div>
                    {achat.note && <p className="mt-3 text-sm text-zinc-600 bg-zinc-50 rounded-lg p-3">{achat.note}</p>}
                </div>

                <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Matériel</th>
                                <th className="text-right px-4 py-3 font-medium">Quantité</th>
                                <th className="text-right px-4 py-3 font-medium">P.U.</th>
                                <th className="text-right px-4 py-3 font-medium">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {achat.items.map((it, i) => (
                                <tr key={i}>
                                    <td className="px-4 py-3 font-medium text-zinc-900">{it.materiel}</td>
                                    <td className="px-4 py-3 text-right text-zinc-600">{it.quantite.toLocaleString('fr-FR')} {it.unite}</td>
                                    <td className="px-4 py-3 text-right text-zinc-600">{it.cout_unitaire.toLocaleString('fr-FR')}</td>
                                    <td className="px-4 py-3 text-right font-semibold text-zinc-900">{it.sous_total.toLocaleString('fr-FR')}</td>
                                </tr>
                            ))}
                        </tbody>
                        <tfoot>
                            <tr className="border-t-2 border-zinc-100 bg-zinc-50/50">
                                <td colSpan={3} className="px-4 py-3 text-right font-bold text-zinc-700">Total</td>
                                <td className="px-4 py-3 text-right font-bold text-emerald-700">{achat.total.toLocaleString('fr-FR')} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}

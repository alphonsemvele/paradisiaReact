import { History, CheckCircle2, Inbox } from 'lucide-react';
import type { PaymentHistoryItem } from '@/types';

interface Props {
    history: PaymentHistoryItem[];
}

export default function PaymentHistory({ history }: Props) {
    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('fr-FR').format(value);

    return (
        <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
            <div className="p-6 border-b border-zinc-200">
                <div className="flex items-center gap-2 mb-1">
                    <History className="w-5 h-5 text-zinc-500" />
                    <h2 className="text-xl font-semibold text-zinc-900">
                        Historique des investissements
                    </h2>
                </div>
                <p className="text-sm text-zinc-500">
                    Les 10 derniers investissements de la communauté
                </p>
            </div>

            {history.length > 0 ? (
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="bg-zinc-50/50 border-b border-zinc-200">
                            <tr>
                                <th className="text-left py-3 px-6 text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                                    Date
                                </th>
                                <th className="text-left py-3 px-6 text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                                    Investisseur
                                </th>
                                <th className="text-left py-3 px-6 text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                                    Round
                                </th>
                                <th className="text-right py-3 px-6 text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                                    Parts
                                </th>
                                <th className="text-right py-3 px-6 text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                                    Montant
                                </th>
                                <th className="text-center py-3 px-6 text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                                    Statut
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {history.map((payment) => (
                                <tr
                                    key={payment.id}
                                    className="border-b border-zinc-100 last:border-0 hover:bg-zinc-50/50 transition-colors"
                                >
                                    <td className="py-4 px-6 text-sm text-zinc-600">
                                        <p>{payment.created_at_formatted.split(' ')[0]}</p>
                                        <p className="text-xs text-zinc-400">
                                            {payment.created_at_human}
                                        </p>
                                    </td>
                                    <td className="py-4 px-6">
                                        <div className="flex items-center gap-3">
                                            {payment.user.photo ? (
                                                <img
                                                    src={payment.user.photo}
                                                    alt={payment.user.name}
                                                    className="w-8 h-8 rounded-full object-cover"
                                                />
                                            ) : (
                                                <div className="w-8 h-8 rounded-full bg-zinc-100 flex items-center justify-center text-xs font-semibold text-zinc-700">
                                                    {payment.user.name?.charAt(0).toUpperCase() ?? '?'}
                                                </div>
                                            )}
                                            <div>
                                                <p className="text-sm font-medium text-zinc-900">
                                                    {payment.user.name ?? 'Anonyme'}
                                                </p>
                                                <p className="text-xs text-zinc-400">
                                                    {payment.user.ref}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="py-4 px-6 text-sm text-zinc-600">
                                        {payment.round_name}
                                    </td>
                                    <td className="py-4 px-6 text-sm font-semibold text-right text-zinc-900">
                                        {formatCurrency(payment.share)}
                                    </td>
                                    <td className="py-4 px-6 text-sm font-semibold text-right text-zinc-900">
                                        {formatCurrency(payment.invested_amount)}
                                        <span className="text-xs font-normal text-zinc-500 ml-1">
                                            FCFA
                                        </span>
                                    </td>
                                    <td className="py-4 px-6 text-center">
                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700">
                                            <CheckCircle2 className="w-3 h-3" />
                                            Confirmé
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            ) : (
                <div className="text-center py-12 px-6">
                    <div className="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center mx-auto mb-3">
                        <Inbox className="w-6 h-6 text-zinc-400" />
                    </div>
                    <p className="text-sm text-zinc-500">
                        Aucun investissement enregistré pour le moment.
                    </p>
                </div>
            )}
        </div>
    );
}
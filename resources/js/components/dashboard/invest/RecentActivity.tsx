import { Activity, ArrowUpRight } from 'lucide-react';
import type { PaymentHistoryItem } from '@/types';

interface Props {
    history: PaymentHistoryItem[];
}

export default function RecentActivity({ history }: Props) {
    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('fr-FR').format(value);

    return (
        <div className="bg-white border border-zinc-200 rounded-2xl p-6">
            <div className="flex items-center gap-2 mb-5">
                <Activity className="w-4 h-4 text-zinc-500" />
                <h3 className="font-semibold text-zinc-900">Activité récente</h3>
            </div>

            {history.length > 0 ? (
                <div className="space-y-3">
                    {history.map((item) => (
                        <div key={item.id} className="flex items-start gap-3">
                            <div className="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <ArrowUpRight className="w-3.5 h-3.5 text-emerald-600" />
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm text-zinc-900">
                                    <span className="font-medium">{item.user.ref}</span>
                                    <span className="text-zinc-500"> a investi </span>
                                    <span className="font-semibold">
                                        {formatCurrency(item.invested_amount)} FCFA
                                    </span>
                                </p>
                                <p className="text-xs text-zinc-400 mt-0.5">
                                    {item.created_at_human}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <p className="text-center text-sm text-zinc-500 py-4">
                    Aucune activité récente
                </p>
            )}
        </div>
    );
}
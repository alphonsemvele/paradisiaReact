import { Trophy, Crown, Medal, Award } from 'lucide-react';
import type { TopInvestor } from '@/types';

interface Props {
    investors: TopInvestor[];
}

export default function TopInvestors({ investors }: Props) {
    return (
        <div className="bg-white border border-zinc-200 rounded-2xl p-6">
            <div className="flex items-center gap-2 mb-5">
                <Trophy className="w-4 h-4 text-amber-500" />
                <h3 className="font-semibold text-zinc-900">Top investisseurs</h3>
            </div>

            {investors.length > 0 ? (
                <div className="space-y-2">
                    {investors.map((investor, index) => (
                        <InvestorRow key={investor.id} investor={investor} rank={index} />
                    ))}
                </div>
            ) : (
                <div className="text-center py-6">
                    <Trophy className="w-8 h-8 text-zinc-300 mx-auto mb-2" />
                    <p className="text-sm text-zinc-500">Aucun investisseur pour le moment</p>
                </div>
            )}
        </div>
    );
}

function InvestorRow({ investor, rank }: { investor: TopInvestor; rank: number }) {
    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('fr-FR').format(value);

    const rankIcons = {
        0: { icon: Crown, color: 'text-amber-500', bg: 'bg-amber-50' },
        1: { icon: Medal, color: 'text-zinc-500', bg: 'bg-zinc-100' },
        2: { icon: Award, color: 'text-orange-500', bg: 'bg-orange-50' },
    };

    const rankInfo = rankIcons[rank as keyof typeof rankIcons];

    return (
        <div className="flex items-center gap-3 p-2.5 hover:bg-zinc-50 rounded-lg transition-colors">
            {rankInfo ? (
                <div className={`w-8 h-8 rounded-lg ${rankInfo.bg} flex items-center justify-center flex-shrink-0`}>
                    <rankInfo.icon className={`w-4 h-4 ${rankInfo.color}`} />
                </div>
            ) : (
                <div className="w-8 h-8 rounded-lg bg-zinc-100 flex items-center justify-center text-xs font-bold text-zinc-600 flex-shrink-0">
                    {rank + 1}
                </div>
            )}

            {investor.photo ? (
                <img
                    src={investor.photo}
                    alt={investor.name}
                    className="w-9 h-9 rounded-full object-cover flex-shrink-0"
                />
            ) : (
                <div className="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-sm font-semibold text-brand-700 flex-shrink-0">
                    {investor.name?.charAt(0).toUpperCase() ?? '?'}
                </div>
            )}

            <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-zinc-900 truncate">{investor.ref}</p>
                <p className="text-xs text-zinc-500">
                    {formatCurrency(investor.total_invested)} FCFA
                </p>
            </div>
        </div>
    );
}
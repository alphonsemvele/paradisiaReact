import { Clock, Target, Calendar } from 'lucide-react';
import type { CurrentRound } from '@/types';

interface Props {
    round: CurrentRound | null;
}

export default function CurrentRoundCard({ round }: Props) {
    if (!round) {
        return (
            <div className="bg-white rounded-2xl border border-zinc-200 p-6 flex items-center gap-4">
                <div className="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center flex-shrink-0">
                    <Clock className="w-5 h-5 text-zinc-500" />
                </div>
                <div>
                    <h3 className="font-semibold text-zinc-900">Aucun round actif</h3>
                    <p className="text-sm text-zinc-500 mt-0.5">
                        Le prochain round d'investissement sera bientôt disponible.
                    </p>
                </div>
            </div>
        );
    }

    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('fr-FR').format(value);

    return (
        <div className="bg-zinc-900 rounded-2xl p-6 text-white relative overflow-hidden">
            <div className="absolute top-0 right-0 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl" />
            <div className="absolute bottom-0 left-0 w-48 h-48 bg-accent-500/10 rounded-full blur-3xl" />

            <div className="relative">
                <div className="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-0.5 bg-brand-500/20 text-brand-300 text-[10px] font-bold uppercase tracking-wide rounded-md">
                                Round actif
                            </span>
                            {round.days_remaining !== null && round.days_remaining > 0 && (
                                <span className="text-xs text-zinc-400">
                                    {Math.ceil(round.days_remaining)} jours restants
                                </span>
                            )}
                        </div>

                        <h3 className="text-2xl font-bold flex items-center gap-2">
                            <Target className="w-5 h-5 text-brand-400" />
                            {round.name}
                        </h3>

                        {round.begin && round.end && (
                            <p className="text-sm text-zinc-400 mt-2 flex items-center gap-1.5">
                                <Calendar className="w-3.5 h-3.5" />
                                Du {round.begin} au {round.end}
                            </p>
                        )}
                    </div>

                    <div className="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-4 min-w-[180px]">
                        <p className="text-xs text-zinc-400 uppercase tracking-wide mb-1">
                            Valeur par part
                        </p>
                        <p className="text-2xl font-bold">
                            {formatCurrency(round.amount)}
                            <span className="text-sm font-normal text-zinc-400 ml-1">FCFA</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
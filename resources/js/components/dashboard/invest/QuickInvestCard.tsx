import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Zap, Minus, Plus, ArrowRight, Lock, Clock } from 'lucide-react';
import type { CurrentRound } from '@/types';

interface Props {
    currentRound: CurrentRound | null;
    isAuthenticated: boolean;
    onInvest: (parts: number) => void;
}

export default function QuickInvestCard({ currentRound, isAuthenticated, onInvest }: Props) {
    const [shares, setShares] = useState(1);

    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('fr-FR').format(value);

    const totalAmount = currentRound ? shares * currentRound.amount : 0;

    const handleClick = () => {
        if (!isAuthenticated) {
            router.visit('/login');
            return;
        }
        onInvest(shares);
    };

    if (!currentRound) {
        return (
            <div className="bg-white border border-zinc-200 rounded-2xl p-6">
                <div className="flex items-center gap-3 mb-3">
                    <div className="w-10 h-10 rounded-xl bg-zinc-100 flex items-center justify-center">
                        <Clock className="w-5 h-5 text-zinc-500" />
                    </div>
                    <h3 className="font-semibold text-zinc-900">Round inactif</h3>
                </div>
                <p className="text-sm text-zinc-500">
                    Aucun round d'investissement n'est actuellement disponible. Revenez bientôt.
                </p>
            </div>
        );
    }

    return (
        <div className="bg-white border border-zinc-200 rounded-2xl overflow-hidden">
            <div className="p-6">
                <div className="flex items-center gap-2 mb-1">
                    <Zap className="w-4 h-4 text-accent-600" />
                    <h3 className="font-semibold text-zinc-900">Investir maintenant</h3>
                </div>
                <p className="text-xs text-zinc-500 mb-5">
                    Achetez des parts en quelques clics
                </p>

                {/* Shares selector */}
                <div className="mb-4">
                    <label className="block text-xs font-medium text-zinc-700 mb-2">
                        Nombre de parts
                    </label>
                    <div className="flex items-center gap-2">
                        <button
                            onClick={() => setShares(Math.max(1, shares - 1))}
                            className="w-10 h-10 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-lg flex items-center justify-center transition-colors"
                        >
                            <Minus className="w-4 h-4 text-zinc-700" />
                        </button>
                        <input
                            type="number"
                            value={shares}
                            onChange={(e) => setShares(Math.max(1, Number(e.target.value) || 1))}
                            min={1}
                            className="flex-1 px-3 py-2.5 text-center text-lg font-semibold border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                        />
                        <button
                            onClick={() => setShares(shares + 1)}
                            className="w-10 h-10 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-lg flex items-center justify-center transition-colors"
                        >
                            <Plus className="w-4 h-4 text-zinc-700" />
                        </button>
                    </div>
                    <p className="text-xs text-zinc-500 mt-1.5">
                        Prix par part : {formatCurrency(currentRound.amount)} FCFA
                    </p>
                </div>

                {/* Total */}
                <div className="bg-zinc-50/50 border border-zinc-200 rounded-xl p-4 mb-4">
                    <div className="flex items-center justify-between">
                        <span className="text-xs font-medium text-zinc-500 uppercase tracking-wide">
                            Total
                        </span>
                        <span className="text-xl font-bold text-zinc-900">
                            {formatCurrency(totalAmount)}
                            <span className="text-xs font-normal text-zinc-500 ml-1">FCFA</span>
                        </span>
                    </div>
                </div>

                <button
                    onClick={handleClick}
                    className="w-full flex items-center justify-center gap-2 py-3 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors group"
                >
                    {isAuthenticated ? 'Investir maintenant' : 'Se connecter pour investir'}
                    <ArrowRight className="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                </button>

                <div className="flex items-center justify-center gap-1.5 mt-3 text-xs text-zinc-500">
                    <Lock className="w-3 h-3" />
                    Paiement sécurisé
                </div>
            </div>
        </div>
    );
}
import { TrendingUp, Shield, Coins, Leaf } from 'lucide-react';

const reasons = [
    {
        icon: TrendingUp,
        title: 'Rendement attractif',
        description: '18% de ROI annuel minimum',
    },
    {
        icon: Shield,
        title: 'Sécurisé',
        description: 'Contrats certifiés et audités',
    },
    {
        icon: Coins,
        title: 'Dividendes mensuels',
        description: 'Revenus passifs réguliers',
    },
    {
        icon: Leaf,
        title: 'Impact écologique',
        description: 'Investissement durable',
    },
];

export default function WhyInvest() {
    return (
        <div className="bg-white border border-zinc-200 rounded-2xl p-6">
            <h3 className="font-semibold text-zinc-900 mb-5">Pourquoi investir ?</h3>

            <div className="space-y-4">
                {reasons.map((reason, i) => {
                    const Icon = reason.icon;
                    return (
                        <div key={i} className="flex items-start gap-3">
                            <div className="w-9 h-9 rounded-lg bg-zinc-50 flex items-center justify-center flex-shrink-0">
                                <Icon className="w-4 h-4 text-zinc-700" />
                            </div>
                            <div>
                                <p className="text-sm font-medium text-zinc-900">
                                    {reason.title}
                                </p>
                                <p className="text-xs text-zinc-500 mt-0.5">
                                    {reason.description}
                                </p>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
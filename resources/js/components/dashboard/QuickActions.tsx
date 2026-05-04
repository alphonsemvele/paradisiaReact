import { router } from '@inertiajs/react';
import { TrendingUp, PenSquare, Users, ShoppingBag } from 'lucide-react';

interface Props {
    onCreatePost: () => void;
    isAuthenticated: boolean;
}

export default function QuickActions({ onCreatePost, isAuthenticated }: Props) {
    const handleAction = (action: () => void) => {
        if (!isAuthenticated) {
            router.visit('/login');
            return;
        }
        action();
    };

    const actions = [
        {
            icon: TrendingUp,
            label: 'Investir',
            description: 'Démarrer un investissement',
            color: 'text-brand-700 bg-brand-50',
            onClick: () => router.visit('/invest'),
        },
        {
            icon: PenSquare,
            label: 'Publier',
            description: 'Partager une expérience',
            color: 'text-accent-700 bg-accent-50',
            onClick: onCreatePost,
        },
        {
            icon: ShoppingBag,
            label: 'Boutique',
            description: 'Voir nos produits',
            color: 'text-blue-700 bg-blue-50',
            onClick: () => router.visit('/shop'),
        },
        {
            icon: Users,
            label: 'Communauté',
            description: 'Rejoindre les membres',
            color: 'text-purple-700 bg-purple-50',
            onClick: () => router.visit('/community'),
        },
    ];

    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-5">
            <h3 className="text-sm font-semibold text-zinc-900 mb-4">Actions rapides</h3>
            <div className="space-y-2">
                {actions.map((action, i) => {
                    const Icon = action.icon;
                    return (
                        <button
                            key={i}
                            onClick={() => handleAction(action.onClick)}
                            className="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50 transition-colors group text-left"
                        >
                            <div className={`w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 ${action.color}`}>
                                <Icon className="w-4 h-4" />
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-medium text-zinc-900">{action.label}</p>
                                <p className="text-xs text-zinc-500 truncate">{action.description}</p>
                            </div>
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
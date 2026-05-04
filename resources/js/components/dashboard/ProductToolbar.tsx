import { router } from '@inertiajs/react';
import { LayoutGrid, List, SlidersHorizontal } from 'lucide-react';
import type { ShopFilters } from '@/types';

interface Props {
    total: number;
    viewMode: 'grid' | 'list';
    onViewModeChange: (mode: 'grid' | 'list') => void;
    filters: ShopFilters;
    onOpenMobileFilters: () => void;
}

export default function ProductToolbar({
    total,
    viewMode,
    onViewModeChange,
    filters,
    onOpenMobileFilters,
}: Props) {
    const handleSortChange = (sort: string) => {
        router.get(
            '/shop',
            { ...filters, sort },
            { preserveState: true, preserveScroll: true }
        );
    };

    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-4 mb-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    <button
                        onClick={onOpenMobileFilters}
                        className="lg:hidden flex items-center gap-2 px-3 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-sm font-medium rounded-lg transition-colors"
                    >
                        <SlidersHorizontal className="w-4 h-4" />
                        Filtres
                    </button>

                    <p className="text-sm text-zinc-600">
                        <span className="font-semibold text-zinc-900">{total}</span> produit
                        {total > 1 ? 's' : ''} trouvé{total > 1 ? 's' : ''}
                    </p>
                </div>

                <div className="flex items-center gap-3">
                    <select
                        value={filters.sort}
                        onChange={(e) => handleSortChange(e.target.value)}
                        className="px-3 py-2 text-sm border border-zinc-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                    >
                        <option value="recent">Plus récents</option>
                        <option value="price_asc">Prix croissant</option>
                        <option value="price_desc">Prix décroissant</option>
                        <option value="name">Nom A-Z</option>
                        <option value="popular">Populaires</option>
                    </select>

                    <div className="hidden md:flex items-center bg-zinc-100 rounded-lg p-1">
                        <button
                            onClick={() => onViewModeChange('grid')}
                            className={`p-1.5 rounded-md transition-all ${
                                viewMode === 'grid'
                                    ? 'bg-white shadow-sm text-zinc-900'
                                    : 'text-zinc-500 hover:text-zinc-700'
                            }`}
                        >
                            <LayoutGrid className="w-4 h-4" />
                        </button>
                        <button
                            onClick={() => onViewModeChange('list')}
                            className={`p-1.5 rounded-md transition-all ${
                                viewMode === 'list'
                                    ? 'bg-white shadow-sm text-zinc-900'
                                    : 'text-zinc-500 hover:text-zinc-700'
                            }`}
                        >
                            <List className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
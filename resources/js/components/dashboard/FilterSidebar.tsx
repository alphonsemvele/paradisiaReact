import { router } from '@inertiajs/react';
import { useState } from 'react';
import { RotateCcw, Tag } from 'lucide-react';
import type { Category, ShopFilters } from '@/types';

interface Props {
    categories: Category[];
    filters: ShopFilters;
    totalProducts: number;
}

export default function FilterSidebar({ categories, filters, totalProducts }: Props) {
    const [priceMin, setPriceMin] = useState(filters.price_min);
    const [priceMax, setPriceMax] = useState(filters.price_max);

    const updateFilter = (key: keyof ShopFilters, value: string) => {
        router.get(
            '/shop',
            { ...filters, [key]: value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    };

    const applyPriceFilter = () => {
        router.get(
            '/shop',
            { ...filters, price_min: priceMin, price_max: priceMax },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    };

    const resetFilters = () => {
        router.get('/shop', {}, { preserveState: false });
    };

    return (
        <div className="space-y-4">
            {/* Catégories */}
            <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                <div className="flex items-center gap-2 mb-4">
                    <Tag className="w-4 h-4 text-zinc-500" />
                    <h3 className="text-sm font-semibold text-zinc-900">Catégories</h3>
                </div>

                <div className="space-y-1">
                    <CategoryButton
                        label="Tous les produits"
                        count={totalProducts}
                        active={!filters.category}
                        onClick={() => updateFilter('category', '')}
                    />
                    {categories.map((category) => (
                        <CategoryButton
                            key={category.id}
                            label={category.name}
                            count={category.products_count ?? 0}
                            active={filters.category === String(category.id)}
                            onClick={() => updateFilter('category', String(category.id))}
                        />
                    ))}
                </div>
            </div>

            {/* Prix */}
            <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                <h3 className="text-sm font-semibold text-zinc-900 mb-4">
                    Prix <span className="text-zinc-400 font-normal">(FCFA)</span>
                </h3>

                <div className="space-y-3">
                    <div>
                        <label className="text-xs font-medium text-zinc-600 mb-1 block">
                            Minimum
                        </label>
                        <input
                            type="number"
                            value={priceMin}
                            onChange={(e) => setPriceMin(e.target.value)}
                            placeholder="0"
                            className="w-full px-3 py-2 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                        />
                    </div>
                    <div>
                        <label className="text-xs font-medium text-zinc-600 mb-1 block">
                            Maximum
                        </label>
                        <input
                            type="number"
                            value={priceMax}
                            onChange={(e) => setPriceMax(e.target.value)}
                            placeholder="100 000"
                            className="w-full px-3 py-2 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                        />
                    </div>
                    <button
                        onClick={applyPriceFilter}
                        className="w-full py-2 bg-zinc-900 text-white text-sm font-medium rounded-lg hover:bg-zinc-800 transition-colors"
                    >
                        Appliquer
                    </button>
                </div>
            </div>

            {/* Reset */}
            <button
                onClick={resetFilters}
                className="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-white border border-zinc-200 hover:bg-zinc-50 text-zinc-700 text-sm font-medium rounded-xl transition-colors"
            >
                <RotateCcw className="w-4 h-4" />
                Réinitialiser les filtres
            </button>
        </div>
    );
}

function CategoryButton({
    label,
    count,
    active,
    onClick,
}: {
    label: string;
    count: number;
    active: boolean;
    onClick: () => void;
}) {
    return (
        <button
            onClick={onClick}
            className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-all ${
                active
                    ? 'bg-zinc-900 text-white font-medium'
                    : 'text-zinc-700 hover:bg-zinc-50'
            }`}
        >
            <span>{label}</span>
            <span
                className={`text-xs px-2 py-0.5 rounded-md ${
                    active ? 'bg-white/15' : 'bg-zinc-100'
                }`}
            >
                {count}
            </span>
        </button>
    );
}
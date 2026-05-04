import { useState } from 'react';
import { router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { X } from 'lucide-react';
import type { Category, ShopFilters } from '@/types';

interface Props {
    categories: Category[];
    filters: ShopFilters;
    onClose: () => void;
}

export default function MobileFilters({ categories, filters, onClose }: Props) {
    const [priceMin, setPriceMin] = useState(filters.price_min);
    const [priceMax, setPriceMax] = useState(filters.price_max);
    const [category, setCategory] = useState(filters.category);

    const apply = () => {
        router.get(
            '/shop',
            {
                ...filters,
                category,
                price_min: priceMin,
                price_max: priceMax,
            },
            { preserveState: true, preserveScroll: true }
        );
        onClose();
    };

    const reset = () => {
        router.get('/shop', {});
        onClose();
    };

    return (
        <AnimatePresence>
            <motion.div
                initial={{ opacity: 0, height: 0 }}
                animate={{ opacity: 1, height: 'auto' }}
                exit={{ opacity: 0, height: 0 }}
                className="lg:hidden mb-6 overflow-hidden"
            >
                <div className="bg-white rounded-2xl border border-zinc-200 p-5 space-y-5">
                    <div className="flex items-center justify-between">
                        <h3 className="font-semibold text-zinc-900">Filtres</h3>
                        <button
                            onClick={onClose}
                            className="p-1.5 hover:bg-zinc-100 rounded-lg"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-zinc-700 mb-3">Catégories</h4>
                        <div className="flex flex-wrap gap-2">
                            <button
                                onClick={() => setCategory('')}
                                className={`px-3 py-1.5 rounded-full text-xs font-medium transition-all ${
                                    !category
                                        ? 'bg-zinc-900 text-white'
                                        : 'bg-zinc-100 text-zinc-700'
                                }`}
                            >
                                Tous
                            </button>
                            {categories.map((cat) => (
                                <button
                                    key={cat.id}
                                    onClick={() => setCategory(String(cat.id))}
                                    className={`px-3 py-1.5 rounded-full text-xs font-medium transition-all ${
                                        category === String(cat.id)
                                            ? 'bg-zinc-900 text-white'
                                            : 'bg-zinc-100 text-zinc-700'
                                    }`}
                                >
                                    {cat.name}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-zinc-700 mb-3">
                            Prix (FCFA)
                        </h4>
                        <div className="flex gap-3">
                            <input
                                type="number"
                                value={priceMin}
                                onChange={(e) => setPriceMin(e.target.value)}
                                placeholder="Min"
                                className="flex-1 px-3 py-2 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            />
                            <input
                                type="number"
                                value={priceMax}
                                onChange={(e) => setPriceMax(e.target.value)}
                                placeholder="Max"
                                className="flex-1 px-3 py-2 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            />
                        </div>
                    </div>

                    <div className="flex gap-2 pt-2">
                        <button
                            onClick={reset}
                            className="flex-1 py-2.5 bg-zinc-100 text-zinc-700 text-sm font-medium rounded-lg"
                        >
                            Réinitialiser
                        </button>
                        <button
                            onClick={apply}
                            className="flex-1 py-2.5 bg-zinc-900 text-white text-sm font-medium rounded-lg"
                        >
                            Appliquer
                        </button>
                    </div>
                </div>
            </motion.div>
        </AnimatePresence>
    );
}
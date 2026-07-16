import { router } from '@inertiajs/react';
import { Eye, Plus } from 'lucide-react';
import type { ShopProduct } from '@/types';

interface Props {
    product: ShopProduct;
    onView: () => void;
}

export default function ProductCard({ product, onView }: Props) {
    const formatPrice = (price: number) =>
        new Intl.NumberFormat('fr-FR').format(price);

    const handleAddToCart = (e: React.MouseEvent) => {
        e.stopPropagation();
        router.post(
            '/cart/add',
            { product_id: product.id, quantity: 1 },
            { preserveScroll: true }
        );
    };

    return (
        <div className="group bg-white rounded-2xl border border-zinc-200 overflow-hidden hover:border-zinc-300 hover:shadow-lg hover:shadow-zinc-200/50 transition-all duration-300">
            {/* Image */}
            <div className="relative aspect-square overflow-hidden bg-zinc-50">
                {product.image ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                        <span className="text-7xl">🍹</span>
                    </div>
                )}

                {/* Badge catégorie */}
                {product.category && (
                    <span className="absolute top-3 left-3 px-2.5 py-1 bg-white/90 backdrop-blur-sm text-zinc-900 text-[11px] font-medium rounded-full shadow-sm">
                        {product.category.name}
                    </span>
                )}

                {/* Quick view */}
                <button
                    onClick={(e) => {
                        e.stopPropagation();
                        onView();
                    }}
                    className="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm hover:bg-white rounded-full flex items-center justify-center text-zinc-700 shadow-sm opacity-0 group-hover:opacity-100 transition-all"
                >
                    <Eye className="w-4 h-4" />
                </button>
            </div>

            {/* Content */}
            <div className="p-4">
                <h3
                    onClick={onView}
                    className="font-medium text-zinc-900 mb-1 line-clamp-1 cursor-pointer group-hover:text-brand-700 transition-colors"
                >
                    {product.name}
                </h3>
                <p className="text-xs text-zinc-500 mb-3 line-clamp-2 min-h-[32px]">
                    {product.description}
                </p>

                <div className="flex items-center justify-between gap-2">
                    <div>
                        <p className="text-base font-semibold text-zinc-900">
                            {formatPrice(product.price)}
                            <span className="text-xs font-normal text-zinc-500 ml-1">FCFA</span>
                        </p>
                    </div>

                    <button
                        onClick={handleAddToCart}
                        className="flex items-center gap-1 px-3 py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-lg transition-colors"
                    >
                        <Plus className="w-3.5 h-3.5" />
                        Ajouter
                    </button>
                </div>
            </div>
        </div>
    );
}
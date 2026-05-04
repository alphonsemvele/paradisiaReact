import { router } from '@inertiajs/react';
import { ShoppingBag } from 'lucide-react';
import type { ShopProduct } from '@/types';

interface Props {
    product: ShopProduct;
    onView: () => void;
}

export default function ProductListItem({ product, onView }: Props) {
    const formatPrice = (price: number) =>
        new Intl.NumberFormat('fr-FR').format(price);

    const handleAddToCart = () => {
        router.post(
            '/cart/add',
            { product_id: product.id, quantity: 1 },
            { preserveScroll: true }
        );
    };

    return (
        <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden hover:border-zinc-300 hover:shadow-md transition-all flex flex-col md:flex-row">
            <div
                onClick={onView}
                className="md:w-56 flex-shrink-0 cursor-pointer overflow-hidden"
            >
                {product.image ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        className="w-full h-48 md:h-full object-cover hover:scale-105 transition-transform duration-500"
                    />
                ) : (
                    <div className="w-full h-48 md:h-full bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center">
                        <span className="text-6xl">🍹</span>
                    </div>
                )}
            </div>

            <div className="flex-1 p-5 flex flex-col justify-between">
                <div>
                    {product.category && (
                        <span className="inline-block px-2.5 py-1 bg-zinc-100 text-zinc-700 text-[11px] font-medium rounded-full mb-2">
                            {product.category.name}
                        </span>
                    )}
                    <h3
                        onClick={onView}
                        className="font-semibold text-zinc-900 text-lg mb-2 cursor-pointer hover:text-brand-700 transition-colors"
                    >
                        {product.name}
                    </h3>
                    <p className="text-sm text-zinc-600 mb-4 line-clamp-2">
                        {product.description}
                    </p>
                </div>

                <div className="flex items-center justify-between">
                    <p className="text-xl font-semibold text-zinc-900">
                        {formatPrice(product.price)}
                        <span className="text-sm font-normal text-zinc-500 ml-1">FCFA</span>
                    </p>
                    <div className="flex gap-2">
                        <button
                            onClick={onView}
                            className="px-3 py-2 text-sm font-medium text-zinc-700 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-colors"
                        >
                            Voir détails
                        </button>
                        <button
                            onClick={handleAddToCart}
                            className="flex items-center gap-1.5 px-4 py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            <ShoppingBag className="w-4 h-4" />
                            Ajouter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
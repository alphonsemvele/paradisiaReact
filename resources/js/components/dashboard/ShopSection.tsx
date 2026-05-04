import { Link } from '@inertiajs/react';
import { ShoppingBag } from 'lucide-react';
import type { Product } from '@/types';

interface Props {
    featured: Product[];
    others: Product[];
}

export default function ShopSection({ featured, others }: Props) {
    return (
        <div className="rounded-2xl shadow-2xl overflow-hidden bg-white">
            <div className="p-6">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-2xl font-bold text-black flex items-center gap-2">
                        <span>🛒</span>
                        Boutique PARADISIA
                    </h3>
                    <Link
                        href="/shop"
                        className="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-600 hover:shadow-lg transition-all text-sm"
                    >
                        Voir tout →
                    </Link>
                </div>

                {/* Produits en vedette */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {featured.length > 0 ? (
                        featured.map((product, index) => (
                            <FeaturedProductCard
                                key={product.id}
                                product={product}
                                isPopular={index === 0}
                            />
                        ))
                    ) : (
                        <EmptyProduct />
                    )}
                </div>

                {/* Autres produits */}
                {others.length > 0 && (
                    <div className="mt-6 pt-6 border-t border-gray-200">
                        <h4 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span>✨</span> Autres produits populaires
                        </h4>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                            {others.map((product) => (
                                <SmallProductCard key={product.id} product={product} />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

function FeaturedProductCard({ product, isPopular }: { product: Product; isPopular: boolean }) {
    const formatPrice = (price: number) =>
        new Intl.NumberFormat('fr-FR').format(price);

    return (
        <div className="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-2 cursor-pointer border border-gray-100">
            <div className="relative">
                {product.image ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        className="w-full h-48 object-cover"
                    />
                ) : (
                    <div className="w-full h-48 bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                        <span className="text-6xl">🍹</span>
                    </div>
                )}

                {product.category && (
                    <span className="absolute top-3 left-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                        {product.category.name}
                    </span>
                )}

                {isPopular && (
                    <span className="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                        Populaire 🔥
                    </span>
                )}
            </div>
            <div className="p-4">
                <h4 className="font-bold text-gray-800 mb-2 line-clamp-1">{product.name}</h4>
                <p className="text-sm text-gray-600 mb-3 line-clamp-2">{product.description}</p>
                <div className="flex items-center justify-between">
                    <p className="text-xl font-bold text-emerald-600">
                        {formatPrice(product.price)} FCFA
                    </p>
                    <button className="bg-emerald-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-emerald-600 transition-all text-sm flex items-center gap-1">
                        <ShoppingBag className="w-4 h-4" />
                        Acheter
                    </button>
                </div>
            </div>
        </div>
    );
}

function SmallProductCard({ product }: { product: Product }) {
    const formatPrice = (price: number) =>
        new Intl.NumberFormat('fr-FR').format(price);

    return (
        <div className="bg-white rounded-xl overflow-hidden shadow hover:shadow-lg transition-all cursor-pointer border border-gray-100 group">
            <div className="relative">
                {product.image ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        className="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                ) : (
                    <div className="w-full h-32 bg-gradient-to-br from-emerald-50 to-teal-50 flex items-center justify-center">
                        <span className="text-4xl">🍹</span>
                    </div>
                )}
            </div>
            <div className="p-3">
                <h5 className="font-semibold text-gray-800 text-sm line-clamp-1">
                    {product.name}
                </h5>
                <p className="text-emerald-600 font-bold text-sm mt-1">
                    {formatPrice(product.price)} FCFA
                </p>
            </div>
        </div>
    );
}

function EmptyProduct() {
    return (
        <div className="bg-gray-100 rounded-xl overflow-hidden shadow-lg border border-gray-200 flex items-center justify-center h-72 col-span-full md:col-span-3">
            <div className="text-center p-6">
                <span className="text-5xl mb-4 block">🚫</span>
                <p className="text-gray-500 font-semibold text-lg">Produit indisponible</p>
            </div>
        </div>
    );
}
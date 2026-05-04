import { useState } from 'react';
import { router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { X, Minus, Plus, ShoppingBag } from 'lucide-react';
import type { ShopProduct } from '@/types';

interface Props {
    product: ShopProduct;
    onClose: () => void;
}

export default function ProductModal({ product, onClose }: Props) {
    const [quantity, setQuantity] = useState(1);
    const [processing, setProcessing] = useState(false);

    const formatPrice = (price: number) =>
        new Intl.NumberFormat('fr-FR').format(price);

    const handleAddToCart = () => {
        setProcessing(true);
        router.post(
            '/cart/add',
            { product_id: product.id, quantity },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setProcessing(false);
                    onClose();
                },
                onError: () => setProcessing(false),
            }
        );
    };

    return (
        <div
            className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
            onClick={(e) => e.target === e.currentTarget && onClose()}
        >
            <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.95 }}
                className="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden my-8"
            >
                <button
                    onClick={onClose}
                    className="absolute top-4 right-4 z-10 p-2 bg-white/90 backdrop-blur-sm hover:bg-white rounded-full shadow-sm transition-all"
                >
                    <X className="w-5 h-5 text-zinc-700" />
                </button>

                <div className="flex flex-col md:flex-row">
                    {/* Image */}
                    <div className="md:w-1/2 bg-zinc-50">
                        {product.image ? (
                            <img
                                src={product.image}
                                alt={product.name}
                                className="w-full h-72 md:h-full object-cover"
                            />
                        ) : (
                            <div className="w-full h-72 md:h-full bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center">
                                <span className="text-9xl">🍹</span>
                            </div>
                        )}
                    </div>

                    {/* Détails */}
                    <div className="md:w-1/2 p-8">
                        {product.category && (
                            <span className="inline-block px-2.5 py-1 bg-zinc-100 text-zinc-700 text-xs font-medium rounded-full mb-3">
                                {product.category.name}
                            </span>
                        )}

                        <h2 className="text-2xl font-bold text-zinc-900 mb-3">
                            {product.name}
                        </h2>
                        <p className="text-sm text-zinc-600 mb-6 leading-relaxed">
                            {product.description}
                        </p>

                        <p className="text-3xl font-bold text-zinc-900 mb-6">
                            {formatPrice(product.price)}
                            <span className="text-base font-normal text-zinc-500 ml-2">FCFA</span>
                        </p>

                        {/* Quantité */}
                        <div className="mb-6">
                            <label className="block text-xs font-semibold text-zinc-700 mb-2 uppercase tracking-wide">
                                Quantité
                            </label>
                            <div className="flex items-center gap-3">
                                <button
                                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                                    className="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 rounded-lg flex items-center justify-center transition-colors"
                                >
                                    <Minus className="w-4 h-4" />
                                </button>
                                <span className="text-lg font-semibold text-zinc-900 w-12 text-center">
                                    {quantity}
                                </span>
                                <button
                                    onClick={() => setQuantity(quantity + 1)}
                                    className="w-10 h-10 bg-zinc-100 hover:bg-zinc-200 rounded-lg flex items-center justify-center transition-colors"
                                >
                                    <Plus className="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        {/* Total */}
                        <div className="bg-zinc-50 rounded-xl p-4 mb-6 flex justify-between items-center">
                            <span className="text-sm text-zinc-600">Total</span>
                            <span className="text-xl font-bold text-zinc-900">
                                {formatPrice(product.price * quantity)} FCFA
                            </span>
                        </div>

                        <button
                            onClick={handleAddToCart}
                            disabled={processing}
                            className="w-full flex items-center justify-center gap-2 py-3.5 bg-zinc-900 hover:bg-zinc-800 disabled:bg-zinc-400 text-white font-medium rounded-xl transition-colors"
                        >
                            <ShoppingBag className="w-5 h-5" />
                            {processing ? 'Ajout en cours...' : 'Ajouter au panier'}
                        </button>
                    </div>
                </div>
            </motion.div>
        </div>
    );
}
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Minus, Package, Plus, ShoppingBag } from 'lucide-react';
import AppLayout from '@/components/layouts/AppLayout';
import CartButton from '@/components/dashboard/CartButton';
import CartDrawer from '@/components/dashboard/CartDrawer';
import type { Cart, PageProps } from '@/types';

interface ComponentLine {
    name: string;
    quantity: number;
}

interface ProductShow {
    id: number;
    name: string;
    description: string | null;
    price: number;
    image: string | null;
    images: string[];
    category: { id: number; name: string } | null;
    components: ComponentLine[];
}

interface ShowProps extends PageProps {
    product: ProductShow;
    cart: Cart;
}

export default function ProductShowPage() {
    const { product, cart } = usePage<ShowProps>().props;

    const [quantity, setQuantity] = useState(1);
    const [showCart, setShowCart] = useState(false);
    const [currentImage, setCurrentImage] = useState(0);

    const images = product.images.length > 0 ? product.images : product.image ? [product.image] : [];
    const cartCount = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
    const formatPrice = (price: number) => new Intl.NumberFormat('fr-FR').format(price);

    const addToCart = () => {
        router.post(
            '/cart/add',
            { product_id: product.id, quantity },
            {
                preserveScroll: true,
                onSuccess: () => setShowCart(true),
            }
        );
    };

    return (
        <AppLayout>
            <Head title={product.name} />

            <div className="max-w-5xl mx-auto px-4 sm:px-6 py-8">
                <Link
                    href="/shop"
                    className="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors mb-6"
                >
                    <ArrowLeft className="w-4 h-4" />
                    Retour à la boutique
                </Link>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {/* Images */}
                    <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }}>
                        <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                            {images.length > 0 ? (
                                <img
                                    src={images[currentImage]}
                                    alt={product.name}
                                    className="w-full h-96 object-cover"
                                />
                            ) : (
                                <div className="w-full h-96 bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                                    <span className="text-8xl">🍹</span>
                                </div>
                            )}
                        </div>
                        {images.length > 1 && (
                            <div className="flex gap-2 mt-3">
                                {images.map((img, i) => (
                                    <button
                                        key={i}
                                        onClick={() => setCurrentImage(i)}
                                        className={`w-16 h-16 rounded-lg overflow-hidden border-2 transition-all ${
                                            currentImage === i ? 'border-emerald-500' : 'border-transparent opacity-70'
                                        }`}
                                    >
                                        <img src={img} alt="" loading="lazy" className="w-full h-full object-cover" />
                                    </button>
                                ))}
                            </div>
                        )}
                    </motion.div>

                    {/* Infos */}
                    <motion.div
                        initial={{ opacity: 0, y: 12 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.05 }}
                        className="flex flex-col"
                    >
                        {product.category && (
                            <span className="self-start bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full mb-3">
                                {product.category.name}
                            </span>
                        )}

                        <h1 className="text-3xl font-bold text-zinc-900">{product.name}</h1>

                        {product.description && (
                            <p className="text-zinc-600 mt-3 leading-relaxed">{product.description}</p>
                        )}

                        {/* Composition (produit composé) */}
                        {product.components.length > 0 && (
                            <div className="mt-5 p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                                <p className="flex items-center gap-2 text-sm font-semibold text-emerald-800 mb-2">
                                    <Package className="w-4 h-4" />
                                    Ce carton contient :
                                </p>
                                <ul className="space-y-1">
                                    {product.components.map((c, i) => (
                                        <li key={i} className="text-sm text-emerald-700">
                                            • {c.quantity} × {c.name}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        <div className="mt-auto pt-6">
                            {/* Prix en bas */}
                            <p className="text-3xl font-bold text-emerald-600 mb-5">
                                {formatPrice(product.price)} <span className="text-base font-semibold">FCFA</span>
                            </p>

                            <div className="flex items-center gap-3">
                                {/* Quantité */}
                                <div className="flex items-center border border-zinc-200 rounded-xl overflow-hidden">
                                    <button
                                        onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                                        className="w-11 h-12 flex items-center justify-center hover:bg-zinc-100 transition-colors"
                                    >
                                        <Minus className="w-4 h-4" />
                                    </button>
                                    <span className="w-10 text-center font-semibold">{quantity}</span>
                                    <button
                                        onClick={() => setQuantity((q) => Math.min(99, q + 1))}
                                        className="w-11 h-12 flex items-center justify-center hover:bg-zinc-100 transition-colors"
                                    >
                                        <Plus className="w-4 h-4" />
                                    </button>
                                </div>

                                <button
                                    onClick={addToCart}
                                    className="flex-1 h-12 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-all flex items-center justify-center gap-2"
                                >
                                    <ShoppingBag className="w-5 h-5" />
                                    Ajouter au panier
                                </button>
                            </div>
                        </div>
                    </motion.div>
                </div>
            </div>

            <CartButton count={cartCount} onClick={() => setShowCart(true)} />
            {showCart && <CartDrawer cart={cart} onClose={() => setShowCart(false)} />}
        </AppLayout>
    );
}

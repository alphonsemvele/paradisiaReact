import { router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, ShoppingBag, Trash2, Minus, Plus, ArrowRight } from 'lucide-react';
import type { Cart } from '@/types';

interface Props {
    cart: Cart;
    onClose: () => void;
}

export default function CartDrawer({ cart, onClose }: Props) {
    const items = Object.entries(cart);
    const formatPrice = (price: number) =>
        new Intl.NumberFormat('fr-FR').format(price);

    const total = Object.values(cart).reduce(
        (sum, item) => sum + item.price * item.quantity,
        0
    );
    const count = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);

    const updateQty = (cartKey: string, quantity: number) => {
        router.patch(
            '/cart/update',
            { cart_key: cartKey, quantity },
            { preserveScroll: true }
        );
    };

    const remove = (cartKey: string) => {
        router.delete('/cart/remove', {
            data: { cart_key: cartKey },
            preserveScroll: true,
        });
    };

    const checkout = () => {
        router.visit('/checkout');
    };

    return (
        <AnimatePresence>
            <div className="fixed inset-0 z-50 overflow-hidden">
                {/* Overlay */}
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    onClick={onClose}
                    className="absolute inset-0 bg-black/50 backdrop-blur-sm"
                />

                {/* Drawer */}
                <motion.div
                    initial={{ x: '100%' }}
                    animate={{ x: 0 }}
                    exit={{ x: '100%' }}
                    transition={{ type: 'spring', damping: 30, stiffness: 300 }}
                    className="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col"
                >
                    {/* Header */}
                    <div className="p-5 border-b border-zinc-200 flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-zinc-900 flex items-center justify-center">
                                <ShoppingBag className="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h3 className="font-semibold text-zinc-900">Mon panier</h3>
                                <p className="text-xs text-zinc-500">
                                    {count} {count > 1 ? 'articles' : 'article'}
                                </p>
                            </div>
                        </div>
                        <button
                            onClick={onClose}
                            className="p-2 hover:bg-zinc-100 rounded-lg transition-colors"
                        >
                            <X className="w-5 h-5 text-zinc-500" />
                        </button>
                    </div>

                    {/* Content */}
                    <div className="flex-1 overflow-y-auto p-5">
                        {items.length > 0 ? (
                            <div className="space-y-3">
                                {items.map(([key, item]) => (
                                    <CartItemCard
                                        key={key}
                                        cartKey={key}
                                        item={item}
                                        onUpdate={updateQty}
                                        onRemove={remove}
                                    />
                                ))}
                            </div>
                        ) : (
                            <EmptyCart onClose={onClose} />
                        )}
                    </div>

                    {/* Footer */}
                    {items.length > 0 && (
                        <div className="p-5 border-t border-zinc-200 bg-zinc-50/50 space-y-3">
                            <div className="space-y-1.5">
                                <div className="flex justify-between text-sm text-zinc-600">
                                    <span>Sous-total</span>
                                    <span>{formatPrice(total)} FCFA</span>
                                </div>
                                <div className="flex justify-between text-sm text-zinc-600">
                                    <span>Livraison</span>
                                    <span>Calculé au paiement</span>
                                </div>
                            </div>

                            <div className="flex justify-between pt-3 border-t border-zinc-200">
                                <span className="text-base font-semibold text-zinc-900">
                                    Total
                                </span>
                                <span className="text-lg font-bold text-zinc-900">
                                    {formatPrice(total)} FCFA
                                </span>
                            </div>

                            <button
                                onClick={checkout}
                                className="w-full flex items-center justify-center gap-2 py-3.5 bg-zinc-900 hover:bg-zinc-800 text-white font-medium rounded-xl transition-colors group"
                            >
                                Passer la commande
                                <ArrowRight className="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                            </button>
                        </div>
                    )}
                </motion.div>
            </div>
        </AnimatePresence>
    );
}

interface CartItemProps {
    cartKey: string;
    item: { id: number; name: string; price: number; image: string | null; quantity: number };
    onUpdate: (key: string, qty: number) => void;
    onRemove: (key: string) => void;
}

function CartItemCard({ cartKey, item, onUpdate, onRemove }: CartItemProps) {
    const formatPrice = (price: number) =>
        new Intl.NumberFormat('fr-FR').format(price);

    return (
        <div className="flex gap-3 p-3 bg-white border border-zinc-200 rounded-xl hover:border-zinc-300 transition-colors">
            <div className="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-zinc-50">
                {item.image ? (
                    <img
                        src={item.image}
                        alt={item.name}
                        className="w-full h-full object-cover"
                    />
                ) : (
                    <div className="w-full h-full bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center">
                        <span className="text-3xl">🍹</span>
                    </div>
                )}
            </div>

            <div className="flex-1 min-w-0">
                <h4 className="text-sm font-medium text-zinc-900 line-clamp-1 mb-1">
                    {item.name}
                </h4>
                <p className="text-xs text-zinc-500 mb-2">
                    {formatPrice(item.price)} FCFA × {item.quantity}
                </p>
                <div className="flex items-center gap-1.5">
                    <button
                        onClick={() => onUpdate(cartKey, item.quantity - 1)}
                        className="w-7 h-7 bg-zinc-100 hover:bg-zinc-200 rounded-md flex items-center justify-center transition-colors"
                    >
                        <Minus className="w-3 h-3" />
                    </button>
                    <span className="w-8 text-center text-sm font-medium">
                        {item.quantity}
                    </span>
                    <button
                        onClick={() => onUpdate(cartKey, item.quantity + 1)}
                        className="w-7 h-7 bg-zinc-100 hover:bg-zinc-200 rounded-md flex items-center justify-center transition-colors"
                    >
                        <Plus className="w-3 h-3" />
                    </button>
                </div>
            </div>

            <div className="flex flex-col items-end justify-between">
                <button
                    onClick={() => onRemove(cartKey)}
                    className="p-1 text-zinc-400 hover:text-red-500 transition-colors"
                >
                    <Trash2 className="w-4 h-4" />
                </button>
                <p className="text-sm font-semibold text-zinc-900">
                    {formatPrice(item.price * item.quantity)} FCFA
                </p>
            </div>
        </div>
    );
}

function EmptyCart({ onClose }: { onClose: () => void }) {
    return (
        <div className="text-center py-12">
            <div className="w-16 h-16 rounded-2xl bg-zinc-100 flex items-center justify-center mx-auto mb-4">
                <ShoppingBag className="w-7 h-7 text-zinc-400" />
            </div>
            <h4 className="text-base font-semibold text-zinc-900 mb-1">
                Votre panier est vide
            </h4>
            <p className="text-sm text-zinc-500 mb-6">
                Découvrez nos délicieux produits !
            </p>
            <button
                onClick={onClose}
                className="inline-flex items-center gap-2 px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors"
            >
                Continuer mes achats
            </button>
        </div>
    );
}
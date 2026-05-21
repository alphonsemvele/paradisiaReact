import { Head, Link, useForm } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { useState, useMemo } from 'react';
import {
    ArrowLeft,
    Search,
    Plus,
    Minus,
    X,
    ShoppingCart,
    Save,
    Banknote,
    Smartphone,
    CreditCard,
    Package,
    Store,
    User,
    Phone,
    Calendar,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

const formatFCFA = (n: number) =>
    new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';

interface CartItem {
    id: number;
    name: string;
    price: number;
    image: string | null;
    quantity: number;
}

export default function SaleCreate({ products, pointsDeVente }: any) {
    const [search, setSearch] = useState('');
    const [cart, setCart] = useState<CartItem[]>([]);

    // Date par défaut : maintenant au format YYYY-MM-DDTHH:MM
    const now = new Date();
    const tzOffset = now.getTimezoneOffset() * 60000;
    const defaultDate = new Date(now.getTime() - tzOffset).toISOString().slice(0, 16);

    const { data, setData, post, processing, errors } = useForm({
        sale_date: defaultDate,
        id_point_de_vente: '',
        customer_name: '',
        customer_phone: '',
        payment_method: 'cash',
        discount: '0',
        notes: '',
        items: [] as any[],
    });

    const filteredProducts = useMemo(() => {
        if (!search) return products;
        return products.filter((p: any) =>
            p.name.toLowerCase().includes(search.toLowerCase()),
        );
    }, [search, products]);

    const addToCart = (product: any) => {
        setCart((current) => {
            const existing = current.find((i) => i.id === product.id);
            if (existing) {
                return current.map((i) =>
                    i.id === product.id ? { ...i, quantity: i.quantity + 1 } : i,
                );
            }
            return [
                ...current,
                {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: 1,
                },
            ];
        });
    };

    const updateQuantity = (id: number, delta: number) => {
        setCart((current) =>
            current
                .map((i) =>
                    i.id === id ? { ...i, quantity: Math.max(0, i.quantity + delta) } : i,
                )
                .filter((i) => i.quantity > 0),
        );
    };

    const removeFromCart = (id: number) => {
        setCart((c) => c.filter((i) => i.id !== id));
    };

    const subtotal = cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
    const discount = parseFloat(data.discount) || 0;
    const total = Math.max(0, subtotal - discount);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (cart.length === 0) {
            alert('Ajoutez au moins un produit au panier');
            return;
        }

        const items = cart.map((i) => ({
            id_product: i.id,
            quantity: i.quantity,
            unit_price: i.price,
        }));

        setData('items', items);

        setTimeout(() => {
            post('/admin/sales', {
                onSuccess: () => setCart([]),
                forceFormData: false,
            });
        }, 50);
    };

    return (
        <AdminLayout title="Nouvelle vente">
            <Head title="Admin - Nouvelle vente" />

            <div className="space-y-4">
                <Link
                    href="/admin/sales"
                    className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900 w-fit"
                >
                    <ArrowLeft className="w-4 h-4" />
                    Retour aux statistiques
                </Link>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {/* ============ Produits (gauche) ============ */}
                    <div className="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                        <div className="mb-4">
                            <h2 className="font-semibold text-zinc-900 mb-3">Produits</h2>
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                                <input
                                    type="search"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Rechercher un produit..."
                                    className="w-full pl-10 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[600px] overflow-y-auto pr-1">
                            {filteredProducts.length === 0 && (
                                <p className="col-span-full text-center py-8 text-sm text-zinc-500">
                                    Aucun produit trouvé
                                </p>
                            )}
                            {filteredProducts.map((product: any) => (
                                <button
                                    key={product.id}
                                    type="button"
                                    onClick={() => addToCart(product)}
                                    className="bg-zinc-50 hover:bg-emerald-50 hover:border-emerald-300 border border-zinc-200 rounded-xl p-3 text-left transition-all"
                                >
                                    <div className="aspect-square bg-white rounded-lg mb-2 overflow-hidden flex items-center justify-center">
                                        {product.image ? (
                                            <img
                                                src={product.image}
                                                alt={product.name}
                                                className="w-full h-full object-cover"
                                            />
                                        ) : (
                                            <Package className="w-8 h-8 text-zinc-300" />
                                        )}
                                    </div>
                                    <p className="text-xs font-medium text-zinc-900 truncate">
                                        {product.name}
                                    </p>
                                    <p className="text-xs font-bold text-emerald-600 mt-0.5">
                                        {formatFCFA(product.price)}
                                    </p>
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* ============ Panier (droite) ============ */}
                    <div className="lg:col-span-1">
                        <form
                            onSubmit={submit}
                            className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5 sticky top-20 space-y-4"
                        >
                            <div className="flex items-center gap-2 pb-3 border-b border-zinc-100">
                                <ShoppingCart className="w-5 h-5 text-emerald-600" />
                                <h2 className="font-semibold text-zinc-900">Panier</h2>
                                <span className="ml-auto text-xs px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded">
                                    {cart.length} article{cart.length > 1 ? 's' : ''}
                                </span>
                            </div>

                            {/* Items */}
                            <div className="space-y-2 max-h-64 overflow-y-auto">
                                {cart.length === 0 && (
                                    <p className="text-center py-6 text-sm text-zinc-400">
                                        Cliquez sur un produit pour l'ajouter
                                    </p>
                                )}
                                <AnimatePresence>
                                    {cart.map((item) => (
                                        <motion.div
                                            key={item.id}
                                            initial={{ opacity: 0, x: 20 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            exit={{ opacity: 0, x: -20 }}
                                            className="flex items-center gap-2 p-2 bg-zinc-50 rounded-lg"
                                        >
                                            {item.image ? (
                                                <img
                                                    src={item.image}
                                                    alt=""
                                                    className="w-10 h-10 rounded object-cover"
                                                />
                                            ) : (
                                                <div className="w-10 h-10 rounded bg-zinc-200 flex items-center justify-center">
                                                    <Package className="w-4 h-4 text-zinc-400" />
                                                </div>
                                            )}
                                            <div className="flex-1 min-w-0">
                                                <p className="text-xs font-medium text-zinc-900 truncate">
                                                    {item.name}
                                                </p>
                                                <p className="text-[10px] text-zinc-500">
                                                    {formatFCFA(item.price)}
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <button
                                                    type="button"
                                                    onClick={() => updateQuantity(item.id, -1)}
                                                    className="w-6 h-6 rounded bg-white border border-zinc-200 flex items-center justify-center hover:bg-zinc-50"
                                                >
                                                    <Minus className="w-3 h-3" />
                                                </button>
                                                <span className="text-xs font-semibold w-5 text-center">
                                                    {item.quantity}
                                                </span>
                                                <button
                                                    type="button"
                                                    onClick={() => updateQuantity(item.id, 1)}
                                                    className="w-6 h-6 rounded bg-white border border-zinc-200 flex items-center justify-center hover:bg-zinc-50"
                                                >
                                                    <Plus className="w-3 h-3" />
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => removeFromCart(item.id)}
                                                    className="w-6 h-6 rounded hover:bg-red-50 text-red-500 flex items-center justify-center ml-1"
                                                >
                                                    <X className="w-3 h-3" />
                                                </button>
                                            </div>
                                        </motion.div>
                                    ))}
                                </AnimatePresence>
                            </div>

                            {/* Client / Date */}
                            <div className="space-y-2 pt-3 border-t border-zinc-100">
                                {/* 🆕 Date de la vente */}
                                <div>
                                    <label className="block text-[10px] font-medium text-zinc-500 mb-1 flex items-center gap-1">
                                        <Calendar className="w-3 h-3" />
                                        Date de la vente
                                    </label>
                                    <input
                                        type="datetime-local"
                                        value={data.sale_date}
                                        onChange={(e) => setData('sale_date', e.target.value)}
                                        className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs"
                                    />
                                    {errors.sale_date && (
                                        <p className="text-[10px] text-red-600 mt-0.5">{errors.sale_date}</p>
                                    )}
                                </div>

                                <div className="relative">
                                    <Store className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                                    <select
                                        value={data.id_point_de_vente}
                                        onChange={(e) =>
                                            setData('id_point_de_vente', e.target.value)
                                        }
                                        className="w-full pl-9 pr-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs"
                                    >
                                        <option value="">Vente particulier (sans point)</option>
                                        {pointsDeVente.map((p: any) => (
                                            <option key={p.id} value={p.id}>
                                                {p.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="relative">
                                    <User className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                                    <input
                                        type="text"
                                        value={data.customer_name}
                                        onChange={(e) => setData('customer_name', e.target.value)}
                                        placeholder="Nom du client (optionnel)"
                                        className="w-full pl-9 pr-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs"
                                    />
                                </div>

                                <div className="relative">
                                    <Phone className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                                    <input
                                        type="tel"
                                        value={data.customer_phone}
                                        onChange={(e) => setData('customer_phone', e.target.value)}
                                        placeholder="Téléphone (optionnel)"
                                        className="w-full pl-9 pr-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs"
                                    />
                                </div>
                            </div>

                            {/* Méthode de paiement */}
                            <div className="grid grid-cols-3 gap-1">
                                {[
                                    { key: 'cash', label: 'Espèces', icon: Banknote },
                                    { key: 'mobile_money', label: 'Mobile', icon: Smartphone },
                                    { key: 'card', label: 'Carte', icon: CreditCard },
                                ].map((m) => {
                                    const Icon = m.icon;
                                    return (
                                        <button
                                            key={m.key}
                                            type="button"
                                            onClick={() => setData('payment_method', m.key)}
                                            className={`flex flex-col items-center gap-0.5 py-2 rounded-lg text-[10px] font-medium transition-all ${
                                                data.payment_method === m.key
                                                    ? 'bg-emerald-500 text-white'
                                                    : 'bg-zinc-50 text-zinc-600 hover:bg-zinc-100'
                                            }`}
                                        >
                                            <Icon className="w-4 h-4" />
                                            {m.label}
                                        </button>
                                    );
                                })}
                            </div>

                            {/* Remise */}
                            <div>
                                <label className="block text-[10px] font-medium text-zinc-500 mb-1">
                                    Remise (FCFA)
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    value={data.discount}
                                    onChange={(e) => setData('discount', e.target.value)}
                                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs"
                                />
                            </div>

                            {/* Total */}
                            <div className="pt-3 border-t border-zinc-100 space-y-1">
                                <div className="flex justify-between text-xs text-zinc-500">
                                    <span>Sous-total</span>
                                    <span>{formatFCFA(subtotal)}</span>
                                </div>
                                {discount > 0 && (
                                    <div className="flex justify-between text-xs text-red-500">
                                        <span>Remise</span>
                                        <span>- {formatFCFA(discount)}</span>
                                    </div>
                                )}
                                <div className="flex justify-between text-lg font-bold text-zinc-900 pt-1 border-t border-zinc-100">
                                    <span>Total</span>
                                    <span className="text-emerald-600">{formatFCFA(total)}</span>
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={processing || cart.length === 0}
                                className="w-full flex items-center justify-center gap-2 py-3 bg-emerald-500 hover:bg-emerald-600 disabled:bg-zinc-300 text-white font-semibold rounded-lg transition-colors"
                            >
                                <Save className="w-4 h-4" />
                                {processing ? 'Enregistrement...' : 'Valider la vente'}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
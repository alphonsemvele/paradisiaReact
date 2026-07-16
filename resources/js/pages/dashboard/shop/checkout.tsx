import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { ArrowLeft, MapPin, Phone, ShoppingBag, User, Wallet } from 'lucide-react';
import AppLayout from '@/components/layouts/AppLayout';
import type { Cart, PageProps } from '@/types';

interface PointDeVente {
    id: number;
    name: string;
    address: string | null;
    phone: string | null;
}

interface CheckoutProps extends PageProps {
    cart: Cart;
    pointsDeVente: PointDeVente[];
    customer: { name: string; phone: string };
}

export default function Checkout() {
    const { cart, pointsDeVente, customer } = usePage<CheckoutProps>().props;

    const items = Object.values(cart);
    const total = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const formatPrice = (price: number) => new Intl.NumberFormat('fr-FR').format(price);

    const { data, setData, post, processing, errors } = useForm({
        customer_name: customer.name || '',
        customer_phone: customer.phone || '',
        id_point_de_vente: '' as string | number,
        payment_method: 'cash',
        notes: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/checkout');
    };

    return (
        <AppLayout>
            <Head title="Commander" />

            <div className="max-w-5xl mx-auto px-4 sm:px-6 py-8">
                <Link
                    href="/shop"
                    className="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors mb-6"
                >
                    <ArrowLeft className="w-4 h-4" />
                    Retour à la boutique
                </Link>

                <h1 className="text-2xl font-bold text-zinc-900 mb-6 flex items-center gap-3">
                    <span className="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center">
                        <ShoppingBag className="w-5 h-5 text-white" />
                    </span>
                    Finaliser ma commande
                </h1>

                <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    {/* Formulaire */}
                    <motion.form
                        initial={{ opacity: 0, y: 12 }}
                        animate={{ opacity: 1, y: 0 }}
                        onSubmit={submit}
                        className="lg:col-span-3 bg-white rounded-2xl border border-zinc-200 p-6 space-y-5"
                    >
                        <Field label="Votre nom" icon={<User className="w-4 h-4" />} error={errors.customer_name}>
                            <input
                                type="text"
                                value={data.customer_name}
                                onChange={(e) => setData('customer_name', e.target.value)}
                                required
                                className="input-checkout"
                                placeholder="Nom complet"
                            />
                        </Field>

                        <Field label="Votre téléphone" icon={<Phone className="w-4 h-4" />} error={errors.customer_phone}>
                            <input
                                type="tel"
                                value={data.customer_phone}
                                onChange={(e) => setData('customer_phone', e.target.value)}
                                required
                                className="input-checkout"
                                placeholder="6XX XX XX XX"
                            />
                        </Field>

                        <Field
                            label="Point de vente (retrait)"
                            icon={<MapPin className="w-4 h-4" />}
                            error={errors.id_point_de_vente}
                        >
                            <select
                                value={data.id_point_de_vente}
                                onChange={(e) => setData('id_point_de_vente', e.target.value)}
                                className="input-checkout"
                            >
                                <option value="">— Choisir plus tard —</option>
                                {pointsDeVente.map((p) => (
                                    <option key={p.id} value={p.id}>
                                        {p.name}
                                        {p.address ? ` — ${p.address}` : ''}
                                    </option>
                                ))}
                            </select>
                        </Field>

                        <Field label="Mode de paiement" icon={<Wallet className="w-4 h-4" />} error={errors.payment_method}>
                            <div className="grid grid-cols-2 gap-3">
                                <PaymentOption
                                    selected={data.payment_method === 'cash'}
                                    onClick={() => setData('payment_method', 'cash')}
                                    title="À la livraison / retrait"
                                    subtitle="Espèces"
                                />
                                <PaymentOption
                                    selected={data.payment_method === 'mobile_money'}
                                    onClick={() => setData('payment_method', 'mobile_money')}
                                    title="Mobile Money"
                                    subtitle="OM / MoMo"
                                />
                            </div>
                        </Field>

                        <Field label="Notes (facultatif)" error={errors.notes}>
                            <textarea
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                rows={3}
                                className="input-checkout resize-none"
                                placeholder="Précisions sur la commande, adresse de livraison..."
                            />
                        </Field>

                        <button
                            type="submit"
                            disabled={processing || items.length === 0}
                            className="w-full py-3.5 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            {processing ? 'Envoi en cours…' : `Confirmer la commande — ${formatPrice(total)} FCFA`}
                        </button>
                    </motion.form>

                    {/* Récap panier */}
                    <motion.aside
                        initial={{ opacity: 0, y: 12 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.05 }}
                        className="lg:col-span-2 bg-white rounded-2xl border border-zinc-200 p-6 h-fit"
                    >
                        <h2 className="font-semibold text-zinc-900 mb-4">
                            Mon panier ({items.length})
                        </h2>
                        <ul className="space-y-4">
                            {items.map((item) => (
                                <li key={item.id} className="flex items-center gap-3">
                                    {item.image ? (
                                        <img
                                            src={item.image}
                                            alt={item.name}
                                            loading="lazy"
                                            className="w-14 h-14 rounded-lg object-cover border border-zinc-100"
                                        />
                                    ) : (
                                        <div className="w-14 h-14 rounded-lg bg-emerald-50 flex items-center justify-center text-2xl">
                                            🍹
                                        </div>
                                    )}
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium text-zinc-900 truncate">{item.name}</p>
                                        <p className="text-xs text-zinc-500">
                                            {item.quantity} × {formatPrice(item.price)} F
                                        </p>
                                    </div>
                                    <p className="text-sm font-semibold text-zinc-900">
                                        {formatPrice(item.price * item.quantity)} F
                                    </p>
                                </li>
                            ))}
                        </ul>
                        <div className="mt-5 pt-4 border-t border-zinc-200 flex items-center justify-between">
                            <span className="text-zinc-500 text-sm">Total</span>
                            <span className="text-xl font-bold text-emerald-600">{formatPrice(total)} FCFA</span>
                        </div>
                    </motion.aside>
                </div>
            </div>

            <style>{`
                .input-checkout {
                    width: 100%;
                    padding: 0.7rem 1rem;
                    border: 1px solid #e4e4e7;
                    border-radius: 0.75rem;
                    font-size: 0.875rem;
                    outline: none;
                    transition: border-color 0.15s, box-shadow 0.15s;
                    background: white;
                }
                .input-checkout:focus {
                    border-color: #10b981;
                    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
                }
            `}</style>
        </AppLayout>
    );
}

function Field({
    label,
    icon,
    error,
    children,
}: {
    label: string;
    icon?: React.ReactNode;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <div>
            <label className="flex items-center gap-1.5 text-sm font-medium text-zinc-700 mb-1.5">
                {icon}
                {label}
            </label>
            {children}
            {error && <p className="text-xs text-red-500 mt-1">{error}</p>}
        </div>
    );
}

function PaymentOption({
    selected,
    onClick,
    title,
    subtitle,
}: {
    selected: boolean;
    onClick: () => void;
    title: string;
    subtitle: string;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`p-3.5 rounded-xl border text-left transition-all ${
                selected
                    ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500/20'
                    : 'border-zinc-200 hover:border-zinc-300'
            }`}
        >
            <p className="text-sm font-semibold text-zinc-900">{title}</p>
            <p className="text-xs text-zinc-500 mt-0.5">{subtitle}</p>
        </button>
    );
}

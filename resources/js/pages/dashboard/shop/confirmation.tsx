import { Head, Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { CheckCircle2, MapPin, Phone, ShoppingBag } from 'lucide-react';
import AppLayout from '@/components/layouts/AppLayout';
import type { PageProps } from '@/types';

interface OrderItem {
    name: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
}

interface ConfirmationProps extends PageProps {
    order: {
        ref: string;
        date: string;
        customer_name: string;
        customer_phone: string;
        payment_method: string;
        status: string;
        total: number;
        notes: string | null;
        point_de_vente: { name: string; address: string | null; phone: string | null } | null;
        items: OrderItem[];
    };
}

export default function OrderConfirmation() {
    const { order } = usePage<ConfirmationProps>().props;
    const formatPrice = (price: number) => new Intl.NumberFormat('fr-FR').format(price);

    return (
        <AppLayout>
            <Head title={`Commande ${order.ref}`} />

            <div className="max-w-2xl mx-auto px-4 sm:px-6 py-12">
                <motion.div
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className="bg-white rounded-2xl border border-zinc-200 overflow-hidden"
                >
                    {/* En-tête succès */}
                    <div className="bg-emerald-500 p-8 text-center">
                        <motion.div
                            initial={{ scale: 0 }}
                            animate={{ scale: 1 }}
                            transition={{ type: 'spring', delay: 0.15 }}
                        >
                            <CheckCircle2 className="w-16 h-16 text-white mx-auto mb-3" />
                        </motion.div>
                        <h1 className="text-2xl font-bold text-white">Commande reçue !</h1>
                        <p className="text-emerald-50 mt-1 text-sm">
                            Référence <span className="font-mono font-semibold">{order.ref}</span> — {order.date}
                        </p>
                    </div>

                    <div className="p-6 space-y-6">
                        <p className="text-sm text-zinc-600 text-center">
                            Merci {order.customer_name} ! Nous vous contacterons au{' '}
                            <span className="font-semibold text-zinc-900">{order.customer_phone}</span> pour
                            confirmer votre commande.
                        </p>

                        {/* Articles */}
                        <div>
                            <h2 className="text-sm font-semibold text-zinc-900 mb-3">Votre commande</h2>
                            <ul className="divide-y divide-zinc-100 border border-zinc-100 rounded-xl overflow-hidden">
                                {order.items.map((item, i) => (
                                    <li key={i} className="flex items-center justify-between px-4 py-3 text-sm">
                                        <span className="text-zinc-700">
                                            {item.quantity} × {item.name}
                                        </span>
                                        <span className="font-medium text-zinc-900">
                                            {formatPrice(item.subtotal)} F
                                        </span>
                                    </li>
                                ))}
                                <li className="flex items-center justify-between px-4 py-3 bg-zinc-50">
                                    <span className="font-semibold text-zinc-900">Total</span>
                                    <span className="font-bold text-emerald-600">
                                        {formatPrice(order.total)} FCFA
                                    </span>
                                </li>
                            </ul>
                        </div>

                        {/* Infos retrait / paiement */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div className="p-4 rounded-xl bg-zinc-50 border border-zinc-100">
                                <p className="text-xs text-zinc-500 mb-1">Paiement</p>
                                <p className="font-medium text-zinc-900">
                                    {order.payment_method === 'mobile_money'
                                        ? 'Mobile Money'
                                        : 'À la livraison / retrait'}
                                </p>
                            </div>
                            <div className="p-4 rounded-xl bg-zinc-50 border border-zinc-100">
                                <p className="text-xs text-zinc-500 mb-1 flex items-center gap-1">
                                    <MapPin className="w-3 h-3" /> Point de vente
                                </p>
                                <p className="font-medium text-zinc-900">
                                    {order.point_de_vente?.name ?? 'À préciser avec vous'}
                                </p>
                                {order.point_de_vente?.address && (
                                    <p className="text-xs text-zinc-500 mt-0.5">{order.point_de_vente.address}</p>
                                )}
                            </div>
                        </div>

                        {/* Contact entreprise */}
                        <a
                            href="https://wa.me/237687984282"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="flex items-center justify-center gap-2 w-full py-3 rounded-xl border border-emerald-500 text-emerald-600 font-medium hover:bg-emerald-50 transition-colors text-sm"
                        >
                            <Phone className="w-4 h-4" />
                            Nous joindre sur WhatsApp : +237 687 98 42 82
                        </a>

                        <Link
                            href="/shop"
                            className="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-zinc-900 text-white font-medium hover:bg-zinc-800 transition-colors text-sm"
                        >
                            <ShoppingBag className="w-4 h-4" />
                            Continuer mes achats
                        </Link>
                    </div>
                </motion.div>
            </div>
        </AppLayout>
    );
}

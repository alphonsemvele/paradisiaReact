import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    ArrowLeft,
    Printer,
    Trash2,
    User,
    Store,
    Calendar,
    Receipt,
    Banknote,
    Smartphone,
    CreditCard,
    Package,
} from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

const formatFCFA = (n: number) =>
    new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';

const PAYMENT_CONFIG: any = {
    cash: { label: 'Espèces', icon: Banknote },
    mobile_money: { label: 'Mobile Money', icon: Smartphone },
    card: { label: 'Carte bancaire', icon: CreditCard },
};

export default function SaleShow({ sale }: any) {
    const [confirmDelete, setConfirmDelete] = useState(false);

    const handleDelete = () => {
        router.delete(`/admin/sales/${sale.id}`);
    };

    const handlePrint = () => {
        window.print();
    };

    const payment = PAYMENT_CONFIG[sale.payment_method];
    const PaymentIcon = payment?.icon || Banknote;

    return (
        <AdminLayout title={`Vente ${sale.ref}`}>
            <Head title={`Admin - ${sale.ref}`} />

            <div className="space-y-4">
                {/* Actions */}
                <div className="flex items-center justify-between flex-wrap gap-3 print:hidden">
                    <Link
                        href="/admin/sales/list"
                        className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Retour aux ventes
                    </Link>

                    <div className="flex gap-2 flex-wrap">
                        <button
                            onClick={handlePrint}
                            className="flex items-center gap-1.5 px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-700 hover:bg-zinc-50"
                        >
                            <Printer className="w-4 h-4" />
                            Imprimer
                        </button>
                        <button
                            onClick={() => setConfirmDelete(true)}
                            className="flex items-center gap-1.5 px-3 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600"
                        >
                            <Trash2 className="w-4 h-4" />
                            Supprimer
                        </button>
                    </div>
                </div>

                {/* Ticket */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden"
                >
                    {/* Header */}
                    <div className="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white p-6 text-center">
                        <div className="w-16 h-16 mx-auto rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-3">
                            <Receipt className="w-8 h-8" />
                        </div>
                        <h2 className="text-xl font-bold mb-1">PARADISIA</h2>
                        <p className="text-sm text-emerald-50">Reçu de vente</p>
                        <p className="text-xs font-mono mt-2 text-emerald-100">{sale.ref}</p>
                    </div>

                    {/* Infos */}
                    <div className="p-6 space-y-4">
                        <div className="grid grid-cols-2 gap-3 text-sm">
                            <InfoBlock
                                icon={Calendar}
                                label="Date"
                                value={sale.sale_date}
                            />
                            <InfoBlock
                                icon={PaymentIcon}
                                label="Paiement"
                                value={payment?.label || sale.payment_method}
                            />
                            {sale.point_de_vente && (
                                <InfoBlock
                                    icon={Store}
                                    label="Point de vente"
                                    value={sale.point_de_vente.name}
                                    sub={sale.point_de_vente.address}
                                />
                            )}
                            {sale.vendor && (
                                <InfoBlock
                                    icon={User}
                                    label="Vendeur"
                                    value={sale.vendor.name}
                                />
                            )}
                            {sale.customer_name && (
                                <InfoBlock
                                    icon={User}
                                    label="Client"
                                    value={sale.customer_name}
                                    sub={sale.customer_phone}
                                />
                            )}
                        </div>

                        {/* Items */}
                        <div className="border-t border-zinc-100 pt-4">
                            <h3 className="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">
                                Articles ({sale.items.length})
                            </h3>
                            <div className="space-y-2">
                                {sale.items.map((item: any) => (
                                    <div
                                        key={item.id}
                                        className="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg"
                                    >
                                        {item.product_image ? (
                                            <img
                                                src={item.product_image}
                                                alt=""
                                                className="w-12 h-12 rounded-lg object-cover"
                                            />
                                        ) : (
                                            <div className="w-12 h-12 rounded-lg bg-zinc-200 flex items-center justify-center">
                                                <Package className="w-5 h-5 text-zinc-400" />
                                            </div>
                                        )}
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-medium text-zinc-900 truncate">
                                                {item.product_name}
                                            </p>
                                            <p className="text-xs text-zinc-500">
                                                {item.quantity} × {formatFCFA(item.unit_price)}
                                            </p>
                                        </div>
                                        <p className="text-sm font-semibold text-zinc-900">
                                            {formatFCFA(item.subtotal)}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Totaux */}
                        <div className="border-t border-zinc-100 pt-4 space-y-1.5">
                            <div className="flex justify-between text-sm text-zinc-600">
                                <span>Sous-total</span>
                                <span>{formatFCFA(sale.subtotal)}</span>
                            </div>
                            {sale.discount > 0 && (
                                <div className="flex justify-between text-sm text-red-500">
                                    <span>Remise</span>
                                    <span>- {formatFCFA(sale.discount)}</span>
                                </div>
                            )}
                            <div className="flex justify-between text-lg font-bold text-zinc-900 pt-2 border-t border-zinc-100">
                                <span>TOTAL</span>
                                <span className="text-emerald-600">{formatFCFA(sale.total)}</span>
                            </div>
                        </div>

                        {sale.notes && (
                            <div className="border-t border-zinc-100 pt-4">
                                <h3 className="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">
                                    Notes
                                </h3>
                                <p className="text-sm text-zinc-600 whitespace-pre-wrap">{sale.notes}</p>
                            </div>
                        )}
                    </div>

                    <div className="bg-zinc-50 px-6 py-4 text-center border-t border-zinc-100">
                        <p className="text-xs text-zinc-500">Merci pour votre achat ! 🌴</p>
                        <p className="text-[10px] text-zinc-400 mt-1">paradisia-africa.com</p>
                    </div>
                </motion.div>
            </div>

            {/* Modal Delete */}
            {confirmDelete && (
                <div
                    className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 print:hidden"
                    onClick={() => setConfirmDelete(false)}
                >
                    <motion.div
                        initial={{ scale: 0.9 }}
                        animate={{ scale: 1 }}
                        onClick={(e) => e.stopPropagation()}
                        className="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full"
                    >
                        <div className="w-12 h-12 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <Trash2 className="w-6 h-6 text-red-600" />
                        </div>
                        <h3 className="text-lg font-semibold text-zinc-900 text-center mb-2">
                            Supprimer cette vente ?
                        </h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">
                            Cette action est irréversible.
                        </p>
                        <div className="flex gap-3">
                            <button
                                onClick={() => setConfirmDelete(false)}
                                className="flex-1 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg"
                            >
                                Annuler
                            </button>
                            <button
                                onClick={handleDelete}
                                className="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg"
                            >
                                Supprimer
                            </button>
                        </div>
                    </motion.div>
                </div>
            )}
        </AdminLayout>
    );
}

function InfoBlock({ icon: Icon, label, value, sub }: any) {
    return (
        <div className="bg-zinc-50 rounded-lg p-3">
            <p className="text-[10px] font-semibold text-zinc-500 uppercase tracking-wider flex items-center gap-1 mb-1">
                <Icon className="w-3 h-3" />
                {label}
            </p>
            <p className="text-sm font-semibold text-zinc-900">{value}</p>
            {sub && <p className="text-[10px] text-zinc-500 mt-0.5">{sub}</p>}
        </div>
    );
}
import { useMemo, useState } from 'react';
import { Head } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Plus, Minus, ShoppingBag, Trash2, MapPin, User, Phone, MessageSquare,
    CheckCircle2, Download, Search, Package,
} from 'lucide-react';

interface Produit {
    id: number;
    name: string;
    description: string | null;
    price: number;
    image: string | null;
    category: string | null;
}

const FRAIS_LIVRAISON = 1000;
const nf = (n: number) => new Intl.NumberFormat('fr-FR').format(n);
const csrf = () => decodeURIComponent(document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? '');

export default function OrderLinkPage({ produits }: { produits: Produit[] }) {
    const [panier, setPanier] = useState<Record<number, number>>({});
    const [recherche, setRecherche] = useState('');
    const [form, setForm] = useState({ customer_name: '', customer_phone: '', delivery_location: '', notes: '' });
    const [erreurs, setErreurs] = useState<Record<string, string>>({});
    const [envoi, setEnvoi] = useState(false);
    const [succes, setSucces] = useState<any>(null);

    const setQte = (id: number, q: number) =>
        setPanier((p) => {
            const n = { ...p };
            if (q <= 0) delete n[id];
            else n[id] = q;
            return n;
        });

    const produitsFiltres = useMemo(() => {
        const q = recherche.trim().toLowerCase();
        return q ? produits.filter((p) => p.name.toLowerCase().includes(q)) : produits;
    }, [recherche, produits]);

    const lignes = useMemo(
        () => Object.entries(panier).map(([id, qte]) => {
            const p = produits.find((x) => x.id === Number(id))!;
            return { ...p, qte, sousTotal: p.price * qte };
        }),
        [panier, produits]
    );

    const sousTotal = lignes.reduce((s, l) => s + l.sousTotal, 0);
    const total = lignes.length ? sousTotal + FRAIS_LIVRAISON : 0;
    const nbArticles = lignes.reduce((s, l) => s + l.qte, 0);

    const valider = async () => {
        setErreurs({});
        const errs: Record<string, string> = {};
        if (!form.customer_name.trim()) errs.customer_name = 'Votre nom est requis.';
        if (!form.customer_phone.trim()) errs.customer_phone = 'Votre téléphone est requis.';
        if (!form.delivery_location.trim()) errs.delivery_location = 'Le lieu de livraison est requis.';
        if (lignes.length === 0) errs.items = 'Ajoutez au moins un produit.';
        if (Object.keys(errs).length) { setErreurs(errs); return; }

        setEnvoi(true);
        try {
            const res = await fetch('/commander', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': csrf() },
                body: JSON.stringify({
                    ...form,
                    items: lignes.map((l) => ({ id: l.id, quantity: l.qte })),
                }),
            });
            const data = await res.json();
            if (data.ok) {
                setSucces(data);
                setPanier({});
                setForm({ customer_name: '', customer_phone: '', delivery_location: '', notes: '' });
            } else {
                setErreurs(data.errors ?? { global: data.message ?? 'Une erreur est survenue.' });
            }
        } catch {
            setErreurs({ global: 'Connexion impossible. Réessayez.' });
        } finally {
            setEnvoi(false);
        }
    };

    return (
        <div className="min-h-screen bg-zinc-50">
            <Head title="Passer commande — PARADISIA" />

            <header className="bg-gradient-to-r from-emerald-600 to-teal-600 text-white">
                <div className="max-w-5xl mx-auto px-4 py-6">
                    <div className="text-xl font-extrabold tracking-wide">PARADISIA <span className="text-emerald-200 text-xs align-top">AFRICA</span></div>
                    <p className="text-emerald-50 text-sm mt-1">Choisissez vos produits, indiquez où livrer — un agent vous rappelle.</p>
                </div>
            </header>

            <div className="max-w-5xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Produits */}
                <div className="lg:col-span-2">
                    <div className="relative mb-4">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                        <input value={recherche} onChange={(e) => setRecherche(e.target.value)}
                            placeholder="Rechercher un produit…"
                            className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        {produitsFiltres.map((p) => {
                            const q = panier[p.id] ?? 0;
                            return (
                                <div key={p.id} className="bg-white rounded-xl border border-zinc-100 overflow-hidden flex flex-col">
                                    <div className="aspect-square bg-zinc-100">
                                        {p.image ? <img src={p.image} alt={p.name} loading="lazy" className="w-full h-full object-cover" />
                                            : <div className="w-full h-full flex items-center justify-center"><Package className="w-8 h-8 text-zinc-300" /></div>}
                                    </div>
                                    <div className="p-2.5 flex flex-col flex-1">
                                        <p className="text-sm font-semibold text-zinc-800 line-clamp-2 flex-1">{p.name}</p>
                                        <p className="text-emerald-600 font-bold text-sm mt-1">{nf(p.price)} FCFA</p>
                                        {q === 0 ? (
                                            <button onClick={() => setQte(p.id, 1)}
                                                className="mt-2 w-full py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold flex items-center justify-center gap-1">
                                                <Plus className="w-3.5 h-3.5" /> Ajouter
                                            </button>
                                        ) : (
                                            <div className="mt-2 flex items-center justify-between bg-emerald-50 rounded-lg p-1">
                                                <button onClick={() => setQte(p.id, q - 1)} className="w-7 h-7 rounded-md bg-white flex items-center justify-center"><Minus className="w-3.5 h-3.5" /></button>
                                                <span className="text-sm font-bold text-emerald-700">{q}</span>
                                                <button onClick={() => setQte(p.id, q + 1)} className="w-7 h-7 rounded-md bg-white flex items-center justify-center"><Plus className="w-3.5 h-3.5" /></button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Panier + formulaire */}
                <div className="lg:col-span-1">
                    <div className="bg-white rounded-2xl border border-zinc-200 p-4 lg:sticky lg:top-4">
                        <h2 className="flex items-center gap-2 font-bold text-zinc-900 mb-3">
                            <ShoppingBag className="w-5 h-5 text-emerald-600" /> Ma commande
                            {nbArticles > 0 && <span className="ml-auto text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{nbArticles} article(s)</span>}
                        </h2>

                        {lignes.length === 0 ? (
                            <p className="text-sm text-zinc-400 text-center py-6">Votre panier est vide.<br />Ajoutez des produits à gauche.</p>
                        ) : (
                            <div className="space-y-2 mb-3 max-h-52 overflow-y-auto">
                                {lignes.map((l) => (
                                    <div key={l.id} className="flex items-center gap-2 text-sm">
                                        <span className="flex-1 truncate text-zinc-700">{l.qte}× {l.name}</span>
                                        <span className="font-semibold text-zinc-900">{nf(l.sousTotal)}</span>
                                        <button onClick={() => setQte(l.id, 0)} className="text-zinc-300 hover:text-red-500"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                        )}

                        {lignes.length > 0 && (
                            <div className="border-t border-zinc-100 pt-3 space-y-1 text-sm">
                                <div className="flex justify-between text-zinc-500"><span>Sous-total</span><span>{nf(sousTotal)} FCFA</span></div>
                                <div className="flex justify-between text-zinc-500"><span>Livraison</span><span>{nf(FRAIS_LIVRAISON)} FCFA</span></div>
                                <div className="flex justify-between font-bold text-zinc-900 text-base pt-1"><span>Total</span><span className="text-emerald-600">{nf(total)} FCFA</span></div>
                            </div>
                        )}

                        {/* Coordonnées */}
                        <div className="mt-4 space-y-3">
                            <Champ icon={User} error={erreurs.customer_name}>
                                <input value={form.customer_name} onChange={(e) => setForm({ ...form, customer_name: e.target.value })}
                                    placeholder="Votre nom complet" className="ipt" />
                            </Champ>
                            <Champ icon={Phone} error={erreurs.customer_phone}>
                                <input value={form.customer_phone} onChange={(e) => setForm({ ...form, customer_phone: e.target.value })}
                                    placeholder="Téléphone (WhatsApp)" className="ipt" />
                            </Champ>
                            <Champ icon={MapPin} error={erreurs.delivery_location}>
                                <input value={form.delivery_location} onChange={(e) => setForm({ ...form, delivery_location: e.target.value })}
                                    placeholder="Lieu de livraison (quartier, ville)" className="ipt" />
                            </Champ>
                            <Champ icon={MessageSquare}>
                                <textarea value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })}
                                    placeholder="Précisions (optionnel)" rows={2} className="ipt resize-none" />
                            </Champ>
                        </div>

                        {(erreurs.global || erreurs.items) && (
                            <p className="mt-2 text-xs text-red-600">{erreurs.global || erreurs.items}</p>
                        )}

                        <button onClick={valider} disabled={envoi || lignes.length === 0}
                            className="mt-4 w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 disabled:bg-zinc-300 text-white font-bold transition-colors">
                            {envoi ? 'Envoi…' : 'Valider ma commande'}
                        </button>
                    </div>
                </div>
            </div>

            {/* Confirmation */}
            <AnimatePresence>
                {succes && (
                    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                        className="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
                        <motion.div initial={{ scale: 0.9, y: 20 }} animate={{ scale: 1, y: 0 }}
                            className="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
                            <div className="px-6 pt-8 pb-6 text-center bg-gradient-to-b from-emerald-50 to-white">
                                <motion.div initial={{ scale: 0 }} animate={{ scale: 1 }} transition={{ delay: 0.1, type: 'spring' }}
                                    className="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                                    <CheckCircle2 className="w-11 h-11 text-emerald-600" />
                                </motion.div>
                                <h3 className="text-xl font-bold text-zinc-900">Commande enregistrée !</h3>
                                <p className="mt-2 text-sm text-zinc-600">{succes.message}</p>
                                <div className="mt-4 inline-block bg-zinc-100 rounded-lg px-4 py-2">
                                    <p className="text-xs text-zinc-500">Code de commande</p>
                                    <p className="font-bold text-zinc-900">{succes.reference}</p>
                                </div>
                                <p className="mt-3 text-sm text-zinc-500">Total : <strong className="text-emerald-600">{succes.total_formate}</strong> (dont {succes.livraison_formate} de livraison)</p>
                            </div>
                            <div className="px-6 pb-6 flex flex-col gap-2">
                                <a href={succes.facture_url} target="_blank" rel="noopener noreferrer"
                                    className="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold">
                                    <Download className="w-4 h-4" /> Télécharger la facture (PDF)
                                </a>
                                <button onClick={() => setSucces(null)}
                                    className="py-2.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-sm font-semibold">
                                    Passer une nouvelle commande
                                </button>
                            </div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>

            <style>{`.ipt{width:100%;padding:.6rem .75rem .6rem 2.3rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.875rem}
            .ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </div>
    );
}

function Champ({ icon: Icon, error, children }: { icon: any; error?: string; children: React.ReactNode }) {
    return (
        <div>
            <div className="relative">
                <Icon className="absolute left-2.5 top-3 w-4 h-4 text-zinc-400" />
                {children}
            </div>
            {error && <p className="text-xs text-red-600 mt-1">{error}</p>}
        </div>
    );
}

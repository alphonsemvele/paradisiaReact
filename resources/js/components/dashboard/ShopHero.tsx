import { useState, FormEvent } from 'react';
import { router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Search, ShoppingBag, Sparkles } from 'lucide-react';
import type { ShopFilters } from '@/types';

interface Props {
    filters: ShopFilters;
    totalProducts: number;
}

export default function ShopHero({ filters, totalProducts }: Props) {
    const [search, setSearch] = useState(filters.search);

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get(
            '/shop',
            { ...filters, search },
            { preserveState: true, preserveScroll: true }
        );
    };

    return (
        <section className="relative overflow-hidden h-[500px] md:h-[560px] bg-emerald-900">
            {/* Image de fond - FRUITS TROPICAUX */}
            <div
                className="absolute inset-0 bg-cover bg-center"
                style={{
                    backgroundImage: `url('https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=1920&q=85&auto=format&fit=crop')`,
                }}
            />

            {/* Overlay sombre pour la lisibilité */}
            <div className="absolute inset-0 bg-gradient-to-r from-black/85 via-black/65 to-black/40" />
            <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-black/30" />

            {/* Effets décoratifs colorés */}
            <div className="absolute inset-0 pointer-events-none">
                <div className="absolute top-1/4 right-1/4 w-96 h-96 bg-orange-500/20 rounded-full blur-3xl" />
                <div className="absolute bottom-0 right-0 w-[500px] h-[500px] bg-amber-400/10 rounded-full blur-3xl" />
            </div>

            {/* Contenu */}
            <div className="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6 }}
                    className="max-w-2xl w-full"
                >
                    {/* Badge */}
                    <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2 }}
                        className="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white/10 backdrop-blur-md border border-white/30 rounded-full text-xs font-medium text-white mb-6 shadow-lg"
                    >
                        <Sparkles className="w-3.5 h-3.5 text-amber-300" />
                        <span>Boutique Paradisia</span>
                        <span className="ml-1 px-2 py-0.5 bg-white/20 rounded-md text-[10px] font-semibold">
                            {totalProducts} produits
                        </span>
                    </motion.div>

                    {/* Titre */}
                    <motion.h1
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.3 }}
                        className="text-4xl md:text-5xl lg:text-6xl font-bold text-white tracking-tight mb-5 leading-[1.1]"
                        style={{
                            textShadow: '0 4px 20px rgba(0,0,0,0.5)',
                        }}
                    >
                        L'expérience tropicale
                        <br />
                        <span className="bg-gradient-to-r from-amber-300 via-orange-400 to-orange-500 bg-clip-text text-transparent">
                            dans chaque gorgée.
                        </span>
                    </motion.h1>

                    {/* Description */}
                    <motion.p
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.4 }}
                        className="text-base md:text-lg text-white/90 mb-8 max-w-xl"
                        style={{
                            textShadow: '0 2px 10px rgba(0,0,0,0.4)',
                        }}
                    >
                        Découvrez nos jus naturels préparés avec amour pour votre bien-être.
                    </motion.p>

                    {/* Recherche */}
                    <motion.form
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.5 }}
                        onSubmit={handleSearch}
                        className="max-w-xl"
                    >
                        <div className="relative">
                            <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400 z-10" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Rechercher un produit..."
                                className="w-full pl-12 pr-32 py-4 rounded-2xl bg-white shadow-2xl shadow-black/30 focus:outline-none focus:ring-4 focus:ring-white/20 transition-all text-sm text-zinc-900 placeholder:text-zinc-400 border-0"
                            />
                            <button
                                type="submit"
                                className="absolute right-2 top-1/2 -translate-y-1/2 px-5 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5 shadow-lg"
                            >
                                <ShoppingBag className="w-4 h-4" />
                                <span className="hidden sm:inline">Rechercher</span>
                            </button>
                        </div>
                    </motion.form>

                    {/* Stats / Trust badges */}
                    <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.6 }}
                        className="flex items-center gap-6 mt-8"
                    >
                        <TrustBadge value="100%" label="Naturel" />
                        <div className="w-px h-8 bg-white/20" />
                        <TrustBadge value="🚚" label="Livraison rapide" />
                        <div className="w-px h-8 bg-white/20" />
                        <TrustBadge value="⭐ 4.8" label="+200 avis" />
                    </motion.div>
                </motion.div>
            </div>
        </section>
    );
}

function TrustBadge({ value, label }: { value: string; label: string }) {
    return (
        <div>
            <p className="text-base font-bold text-white">{value}</p>
            <p className="text-xs text-white/70">{label}</p>
        </div>
    );
}
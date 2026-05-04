import { motion } from 'framer-motion';
import { Sparkles, TrendingUp, ArrowUpRight, Wallet } from 'lucide-react';
import { Link } from '@inertiajs/react';
import type { UserInvestment } from '@/types';

interface Props {
    userInvestment: UserInvestment;
    isAuthenticated: boolean;
}

export default function InvestHero({ userInvestment, isAuthenticated }: Props) {
    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('fr-FR', {
            maximumFractionDigits: 0,
        }).format(Math.round(value));

    return (
        <section className="relative overflow-hidden h-[480px] md:h-[520px] bg-emerald-900">
            {/* Image de fond */}
            <div
                className="absolute inset-0 bg-cover bg-center"
                style={{
                    backgroundImage: `url('https://images.unsplash.com/photo-1579621970795-87facc2f976d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')`,
                }}
            />

            {/* Overlay PLUS FORT à gauche pour le texte */}
            <div className="absolute inset-0 bg-gradient-to-r from-emerald-950/95 via-emerald-900/80 to-emerald-900/30" />
            <div className="absolute inset-0 bg-gradient-to-t from-emerald-950/50 via-transparent to-transparent" />

            {/* Effets décoratifs colorés */}
            <div className="absolute inset-0 pointer-events-none">
                <div className="absolute top-1/4 right-1/4 w-96 h-96 bg-orange-400/15 rounded-full blur-3xl" />
                <div className="absolute bottom-0 right-0 w-[500px] h-[500px] bg-amber-300/10 rounded-full blur-3xl" />
            </div>

            {/* Contenu */}
            <div className="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center w-full">
                    {/* Left side */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5 }}
                    >
                        <motion.div
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.2 }}
                            className="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white/15 backdrop-blur-md border border-white/30 rounded-full text-xs font-medium text-white mb-5 shadow-lg"
                        >
                            <Sparkles className="w-3.5 h-3.5 text-amber-300" />
                            <span>Espace Investissement</span>
                            <span className="ml-1 px-2 py-0.5 bg-amber-400/30 rounded-md text-[10px] font-bold">
                                +18% / an
                            </span>
                        </motion.div>

                        <motion.h1
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.3 }}
                            className="text-4xl md:text-5xl lg:text-6xl font-bold text-white tracking-tight mb-4 leading-[1.1]"
                            style={{
                                textShadow:
                                    '0 4px 24px rgba(0,0,0,0.6), 0 2px 4px rgba(0,0,0,0.4)',
                            }}
                        >
                            Cultivez votre
                            <br />
                            <span className="bg-gradient-to-r from-amber-300 via-orange-400 to-orange-500 bg-clip-text text-transparent">
                                avenir fruité.
                            </span>
                        </motion.h1>

                        <motion.p
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.4 }}
                            className="text-base md:text-lg text-white max-w-md font-medium"
                            style={{
                                textShadow:
                                    '0 2px 12px rgba(0,0,0,0.7), 0 1px 3px rgba(0,0,0,0.5)',
                            }}
                        >
                            Investissez dans nos plantations et récoltez les fruits du succès. 🥭
                        </motion.p>
                    </motion.div>

                    {/* Right side - User capital card */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5, delay: 0.15 }}
                        className="lg:justify-self-end w-full max-w-md"
                    >
                        {isAuthenticated ? (
                            <div className="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-2xl shadow-black/30 border border-white/40 relative overflow-hidden">
                                <div className="absolute -top-12 -right-12 w-40 h-40 bg-gradient-to-br from-emerald-400/30 to-orange-300/30 rounded-full blur-2xl" />

                                <div className="relative">
                                    <div className="flex items-center gap-2 mb-3">
                                        <div className="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                            <Wallet className="w-4 h-4 text-emerald-700" />
                                        </div>
                                        <span className="text-xs font-semibold text-zinc-600 uppercase tracking-wide">
                                            Votre capital investi
                                        </span>
                                    </div>

                                    <div className="flex items-baseline gap-2 mb-5">
                                        <span className="text-4xl lg:text-5xl font-bold tracking-tight text-zinc-900">
                                            {formatCurrency(userInvestment.total)}
                                        </span>
                                        <span className="text-sm text-zinc-500 font-medium">
                                            FCFA
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-4 pt-4 border-t border-zinc-200">
                                        <div className="flex-1">
                                            <p className="text-xs text-zinc-500 mb-1">Vos parts</p>
                                            <p className="text-base font-bold text-zinc-900">
                                                {formatCurrency(userInvestment.shares)}
                                            </p>
                                        </div>
                                        <div className="w-px h-10 bg-zinc-200" />
                                        <div className="flex-1">
                                            <p className="text-xs text-zinc-500 mb-1">
                                                Rendement
                                            </p>
                                            <p className="text-base font-bold text-emerald-600 flex items-center gap-1">
                                                <ArrowUpRight className="w-4 h-4" />
                                                +18%
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="bg-white/95 backdrop-blur-md border border-white/40 rounded-3xl p-6 shadow-2xl shadow-black/30 relative overflow-hidden">
                                <div className="absolute -top-12 -right-12 w-40 h-40 bg-gradient-to-br from-emerald-400/30 to-orange-300/30 rounded-full blur-2xl" />

                                <div className="relative">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                                            <TrendingUp className="w-5 h-5 text-white" />
                                        </div>
                                        <div>
                                            <h3 className="font-bold text-zinc-900">
                                                Démarrez l'aventure
                                            </h3>
                                            <p className="text-xs text-zinc-500">
                                                Connectez-vous pour investir
                                            </p>
                                        </div>
                                    </div>

                                    <p className="text-sm text-zinc-600 mb-5">
                                        Rejoignez la grande famille Paradisia 🌱
                                    </p>

                                    <Link
                                        href="/login"
                                        className="block w-full text-center px-4 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-emerald-500/30"
                                    >
                                        Se connecter
                                    </Link>
                                </div>
                            </div>
                        )}
                    </motion.div>
                </div>
            </div>
        </section>
    );
}
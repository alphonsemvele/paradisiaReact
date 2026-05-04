import { motion } from 'framer-motion';
import { Sparkles, ArrowRight } from 'lucide-react';
import { Link } from '@inertiajs/react';

export default function HeroSection() {
    return (
        <motion.section
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="relative overflow-hidden rounded-3xl h-[320px] md:h-[380px] mb-8 bg-emerald-900"
        >
            {/* Image de fond - fruits tropicaux frais */}
            <div
                className="absolute inset-0 bg-cover bg-center"
                style={{
                    backgroundImage: `url('https://images.unsplash.com/photo-1619566636858-adf3ef46400b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')`,
                }}
            />

            {/* Overlay sombre dégradé */}
            <div className="absolute inset-0 bg-gradient-to-r from-emerald-950/90 via-emerald-900/70 to-emerald-800/40" />
            <div className="absolute inset-0 bg-gradient-to-t from-emerald-950/40 via-transparent to-transparent" />

            {/* Effets décoratifs */}
            <div className="absolute inset-0 pointer-events-none">
                <div className="absolute top-0 right-0 w-96 h-96 bg-orange-400/20 rounded-full blur-3xl" />
                <div className="absolute bottom-0 left-1/4 w-80 h-80 bg-amber-300/15 rounded-full blur-3xl" />
            </div>

            {/* Contenu */}
            <div className="relative h-full flex items-center px-8 md:px-12">
                <div className="max-w-2xl">
                    <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2 }}
                        className="inline-flex items-center gap-2 px-3 py-1.5 bg-white/15 backdrop-blur-md border border-white/30 rounded-full text-xs font-medium text-white mb-5 shadow-lg"
                    >
                        <Sparkles className="w-3.5 h-3.5 text-amber-300" />
                        Bienvenue sur Paradisia
                    </motion.div>

                    <motion.h1
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.3 }}
                        className="text-3xl md:text-4xl lg:text-5xl font-bold text-white tracking-tight mb-4 leading-[1.1]"
                        style={{
                            textShadow: '0 4px 24px rgba(0,0,0,0.5), 0 2px 4px rgba(0,0,0,0.3)',
                        }}
                    >
                        L'expérience tropicale,
                        <br />
                        <span className="bg-gradient-to-r from-amber-300 via-orange-400 to-orange-500 bg-clip-text text-transparent">
                            à portée de main.
                        </span>
                    </motion.h1>

                    <motion.p
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.4 }}
                        className="text-base md:text-lg text-white/90 mb-6 max-w-xl"
                        style={{
                            textShadow: '0 2px 10px rgba(0,0,0,0.4)',
                        }}
                    >
                        Découvrez nos jus naturels, investissez dans notre franchise, et rejoignez
                        une communauté passionnée. 🌴
                    </motion.p>

                    <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.5 }}
                        className="flex flex-wrap gap-3"
                    >
                        <Link
                            href="/shop"
                            className="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-zinc-900 font-medium text-sm rounded-xl hover:bg-zinc-100 transition-all shadow-lg shadow-black/10 group"
                        >
                            Découvrir la boutique
                            <ArrowRight className="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                        </Link>
                        <Link
                            href="/invest"
                            className="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 backdrop-blur-md border border-white/30 text-white font-medium text-sm rounded-xl hover:bg-white/20 transition-all"
                        >
                            Investir
                        </Link>
                    </motion.div>
                </div>
            </div>
        </motion.section>
    );
}
import { motion } from 'framer-motion';
import { Rocket, X, Mail } from 'lucide-react';

interface Props {
    onClose: () => void;
}

export default function InvestMessageModal({ onClose }: Props) {
    return (
        <div
            className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
            onClick={(e) => e.target === e.currentTarget && onClose()}
        >
            <motion.div
                initial={{ opacity: 0, scale: 0.95, y: 20 }}
                animate={{ opacity: 1, scale: 1, y: 0 }}
                exit={{ opacity: 0, scale: 0.95 }}
                className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative"
            >
                <button
                    onClick={onClose}
                    className="absolute top-4 right-4 p-1.5 hover:bg-zinc-100 rounded-lg transition-colors"
                >
                    <X className="w-4 h-4 text-zinc-500" />
                </button>

                <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center mb-5 shadow-lg shadow-brand-500/20">
                    <Rocket className="w-7 h-7 text-white" />
                </div>

                <h3 className="text-xl font-bold text-zinc-900 mb-2">
                    Bientôt disponible
                </h3>
                <p className="text-sm text-zinc-600 leading-relaxed mb-5">
                    L'investissement via{' '}
                    <span className="font-semibold text-zinc-900">Malpay</span> sera
                    bientôt disponible. Nous travaillons activement pour vous offrir cette
                    fonctionnalité dans les plus brefs délais.
                </p>

                <div className="bg-zinc-50 border border-zinc-200 rounded-xl p-4 mb-5 flex items-start gap-3">
                    <Mail className="w-4 h-4 text-zinc-500 mt-0.5 flex-shrink-0" />
                    <p className="text-xs text-zinc-600">
                        Vous serez notifié dès que le service sera opérationnel.
                    </p>
                </div>

                <button
                    onClick={onClose}
                    className="w-full py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    J'ai compris
                </button>
            </motion.div>
        </div>
    );
}
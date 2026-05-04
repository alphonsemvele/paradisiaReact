import { motion } from 'framer-motion';
import { ShoppingBag } from 'lucide-react';

interface Props {
    count: number;
    onClick: () => void;
}

export default function CartButton({ count, onClick }: Props) {
    return (
        <motion.button
            initial={{ scale: 0, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ type: 'spring', stiffness: 300, damping: 25 }}
            onClick={onClick}
            className="fixed bottom-6 right-6 z-40 w-14 h-14 bg-zinc-900 hover:bg-zinc-800 text-white rounded-2xl shadow-xl shadow-zinc-900/20 flex items-center justify-center transition-colors group"
        >
            <ShoppingBag className="w-6 h-6 group-hover:scale-110 transition-transform" />
            {count > 0 && (
                <motion.span
                    key={count}
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    className="absolute -top-1.5 -right-1.5 min-w-[22px] h-[22px] px-1.5 bg-accent-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center ring-2 ring-white"
                >
                    {count > 99 ? '99+' : count}
                </motion.span>
            )}
        </motion.button>
    );
}
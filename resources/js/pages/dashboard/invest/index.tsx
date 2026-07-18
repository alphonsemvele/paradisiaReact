import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { motion } from 'framer-motion';
import AppLayout from '@/components/layouts/AppLayout';
import InvestHero from '@/components/dashboard/invest/InvestHero';
import StatsGrid from '@/components/dashboard/invest/StatsGrid';
import CurrentRoundCard from '@/components/dashboard/invest/CurrentRoundCard';
import ROISimulator from '@/components/dashboard/invest/ROISimulator';
import PaymentHistory from '@/components/dashboard/invest/PaymentHistory';
import QuickInvestCard from '@/components/dashboard/invest/QuickInvestCard';
import TopInvestors from '@/components/dashboard/invest/TopInvestors';
import RecentActivity from '@/components/dashboard/invest/RecentActivity';
import WhyInvest from '@/components/dashboard/invest/WhyInvest';
import MalaPayModal from '@/components/dashboard/invest/MalaPayModal';
import type {
    InvestStats,
    CurrentRound,
    TopInvestor,
    UserInvestment,
    PaymentHistoryItem,
    MonthlyStats,
    PageProps,
} from '@/types';

interface InvestProps extends PageProps {
    stats: InvestStats;
    currentRound: CurrentRound | null;
    topInvestors: TopInvestor[];
    userInvestment: UserInvestment;
    paymentHistory: PaymentHistoryItem[];
    monthlyStats: MonthlyStats;
}

export default function InvestIndex() {
    const {
        stats,
        currentRound,
        topInvestors,
        userInvestment,
        paymentHistory,
        monthlyStats,
        auth,
    } = usePage<InvestProps>().props;

    // Parts à payer : null tant que le modal de paiement est fermé
    const [partsAPayer, setPartsAPayer] = useState<number | null>(null);

    return (
        <AppLayout>
            <Head title="Investir" />

            {/* Hero */}
            <InvestHero
                userInvestment={userInvestment}
                isAuthenticated={!!auth.user}
            />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Stats globales */}
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.4 }}
                    className="mb-8"
                >
                    <StatsGrid stats={stats} monthlyStats={monthlyStats} />
                </motion.div>

                <div className="grid grid-cols-12 gap-6">
                    {/* Section principale */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4, delay: 0.1 }}
                        className="col-span-12 lg:col-span-8 space-y-6"
                    >
                        <CurrentRoundCard round={currentRound} />
                        <ROISimulator />
                        <PaymentHistory history={paymentHistory} />
                    </motion.div>

                    {/* Sidebar */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4, delay: 0.2 }}
                        className="col-span-12 lg:col-span-4 space-y-6"
                    >
                        <QuickInvestCard
                            currentRound={currentRound}
                            isAuthenticated={!!auth.user}
                            onInvest={(parts) => setPartsAPayer(parts)}
                        />
                        <TopInvestors investors={topInvestors} />
                        <RecentActivity history={paymentHistory.slice(0, 4)} />
                        <WhyInvest />
                    </motion.div>
                </div>
            </div>

            {partsAPayer !== null && currentRound && (
                <MalaPayModal
                    parts={partsAPayer}
                    prixPart={currentRound.amount}
                    onClose={() => setPartsAPayer(null)}
                />
            )}
        </AppLayout>
    );
}
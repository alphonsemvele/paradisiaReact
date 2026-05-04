import { Wallet, Users, PieChart, Globe2, ArrowUp, ArrowDown } from 'lucide-react';
import type { InvestStats, MonthlyStats } from '@/types';

interface Props {
    stats: InvestStats;
    monthlyStats: MonthlyStats;
}

export default function StatsGrid({ stats, monthlyStats }: Props) {
    const formatNumber = (value: number) =>
        new Intl.NumberFormat('fr-FR', {
            maximumFractionDigits: 0,
        }).format(Math.round(value));

    return (
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
            <StatCard
                icon={Wallet}
                label="Total investi"
                value={formatNumber(stats.total_invested)}
                suffix="FCFA"
                trend={monthlyStats.growth}
                accent="emerald"
            />
            <StatCard
                icon={Users}
                label="Investisseurs"
                value={formatNumber(stats.total_investors)}
                description="Actifs"
                accent="orange"
            />
            <StatCard
                icon={PieChart}
                label="Parts émises"
                value={formatNumber(stats.total_shares)}
                description="Total des parts"
                accent="amber"
            />
            <StatCard
                icon={Globe2}
                label="Pays"
                value={String(stats.countries_count)}
                description="Présence mondiale"
                accent="teal"
            />
        </div>
    );
}

interface StatCardProps {
    icon: React.ElementType;
    label: string;
    value: string;
    suffix?: string;
    description?: string;
    trend?: number;
    accent: 'emerald' | 'orange' | 'amber' | 'teal';
}

function StatCard({
    icon: Icon,
    label,
    value,
    suffix,
    description,
    trend,
    accent,
}: StatCardProps) {
    const isPositive = trend !== undefined && trend > 0;
    const isNegative = trend !== undefined && trend < 0;

    const accentColors = {
        emerald: 'bg-emerald-50 text-emerald-600',
        orange: 'bg-orange-50 text-orange-600',
        amber: 'bg-amber-50 text-amber-600',
        teal: 'bg-teal-50 text-teal-600',
    };

    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-5 hover:border-emerald-300 hover:shadow-md transition-all">
            <div className="flex items-center justify-between mb-4">
                <div
                    className={`w-10 h-10 rounded-xl flex items-center justify-center ${accentColors[accent]}`}
                >
                    <Icon className="w-5 h-5" />
                </div>
                {trend !== undefined && trend !== 0 && (
                    <div
                        className={`flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold ${
                            isPositive
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-red-50 text-red-700'
                        }`}
                    >
                        {isPositive ? (
                            <ArrowUp className="w-3 h-3" />
                        ) : (
                            <ArrowDown className="w-3 h-3" />
                        )}
                        {Math.abs(trend)}%
                    </div>
                )}
            </div>

            <p className="text-xs font-medium text-zinc-500 mb-1.5 uppercase tracking-wide">
                {label}
            </p>
            <div className="flex items-baseline gap-1.5">
                <p className="text-2xl font-bold text-zinc-900 tracking-tight">{value}</p>
                {suffix && <span className="text-xs text-zinc-500">{suffix}</span>}
            </div>
            {description && trend === undefined && (
                <p className="text-xs text-zinc-500 mt-2">{description}</p>
            )}
        </div>
    );
}
import { useState, useMemo } from 'react';
import { Calculator, TrendingUp, Calendar, Wallet } from 'lucide-react';

const ROI_RATE = 0.18; // 18%

export default function ROISimulator() {
    const [amount, setAmount] = useState(100000);
    const [duration, setDuration] = useState(3);

    const formatCurrency = (value: number) =>
        new Intl.NumberFormat('fr-FR').format(Math.round(value));

    const projection = useMemo(() => {
        const finalCapital = amount * Math.pow(1 + ROI_RATE, duration);
        const totalGains = finalCapital - amount;
        const monthlyGains = totalGains / (duration * 12);

        const yearlyData = Array.from({ length: duration }, (_, i) => {
            const year = i + 1;
            const yearCapital = amount * Math.pow(1 + ROI_RATE, year);
            const previousCapital = amount * Math.pow(1 + ROI_RATE, year - 1);
            const yearGain = yearCapital - previousCapital;

            return {
                year,
                capital: yearCapital,
                gain: yearGain,
                returnPercent: ((yearCapital / amount - 1) * 100).toFixed(1),
            };
        });

        return { finalCapital, totalGains, monthlyGains, yearlyData };
    }, [amount, duration]);

    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-6 lg:p-8">
            <div className="flex items-start justify-between mb-6 flex-wrap gap-3">
                <div>
                    <div className="flex items-center gap-2 mb-1">
                        <Calculator className="w-5 h-5 text-zinc-500" />
                        <h2 className="text-xl font-semibold text-zinc-900">
                            Simulateur de rendement
                        </h2>
                    </div>
                    <p className="text-sm text-zinc-500">
                        Calculez vos gains potentiels avec un rendement annuel de 18%
                    </p>
                </div>
                <div className="px-3 py-1.5 bg-brand-50 text-brand-700 text-xs font-semibold rounded-lg">
                    18% par an
                </div>
            </div>

            {/* Inputs */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label className="block text-xs font-semibold text-zinc-700 uppercase tracking-wide mb-2">
                        Montant à investir
                    </label>
                    <div className="relative">
                        <input
                            type="number"
                            value={amount}
                            onChange={(e) => setAmount(Number(e.target.value) || 0)}
                            min={10000}
                            step={10000}
                            className="w-full pl-4 pr-16 py-3 text-lg font-semibold border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all"
                        />
                        <span className="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-zinc-400">
                            FCFA
                        </span>
                    </div>
                    <input
                        type="range"
                        min={10000}
                        max={5000000}
                        step={10000}
                        value={amount}
                        onChange={(e) => setAmount(Number(e.target.value))}
                        className="w-full mt-3 h-1.5 bg-zinc-100 rounded-full appearance-none cursor-pointer accent-zinc-900"
                    />
                    <div className="flex justify-between text-[10px] text-zinc-400 mt-1.5">
                        <span>10K</span>
                        <span>2.5M</span>
                        <span>5M</span>
                    </div>
                </div>

                <div>
                    <label className="block text-xs font-semibold text-zinc-700 uppercase tracking-wide mb-2">
                        Durée
                    </label>
                    <div className="relative">
                        <input
                            type="number"
                            value={duration}
                            onChange={(e) => setDuration(Math.max(1, Math.min(10, Number(e.target.value) || 1)))}
                            min={1}
                            max={10}
                            className="w-full pl-4 pr-16 py-3 text-lg font-semibold border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all"
                        />
                        <span className="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-zinc-400">
                            ans
                        </span>
                    </div>
                    <input
                        type="range"
                        min={1}
                        max={10}
                        value={duration}
                        onChange={(e) => setDuration(Number(e.target.value))}
                        className="w-full mt-3 h-1.5 bg-zinc-100 rounded-full appearance-none cursor-pointer accent-zinc-900"
                    />
                    <div className="flex justify-between text-[10px] text-zinc-400 mt-1.5">
                        <span>1 an</span>
                        <span>5 ans</span>
                        <span>10 ans</span>
                    </div>
                </div>
            </div>

            {/* Results */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                <ResultCard
                    icon={Wallet}
                    label="Capital final"
                    value={formatCurrency(projection.finalCapital)}
                    accent="emerald"
                />
                <ResultCard
                    icon={TrendingUp}
                    label="Gains totaux"
                    value={formatCurrency(projection.totalGains)}
                    accent="brand"
                />
                <ResultCard
                    icon={Calendar}
                    label="Gains mensuels"
                    value={formatCurrency(projection.monthlyGains)}
                    accent="zinc"
                />
            </div>

            {/* Yearly projection */}
            <div className="bg-zinc-50/50 border border-zinc-200 rounded-xl p-5">
                <h4 className="text-sm font-semibold text-zinc-900 mb-4">
                    Projection annuelle
                </h4>
                <div className="space-y-2">
                    {projection.yearlyData.map((data) => (
                        <div
                            key={data.year}
                            className="flex items-center justify-between p-3 bg-white border border-zinc-200 rounded-lg"
                        >
                            <div className="flex items-center gap-3">
                                <div className="w-8 h-8 rounded-lg bg-zinc-100 flex items-center justify-center text-xs font-bold text-zinc-700">
                                    {data.year}
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-zinc-900">
                                        Année {data.year}
                                    </p>
                                    <p className="text-xs text-zinc-500">
                                        +{data.returnPercent}% cumulé
                                    </p>
                                </div>
                            </div>
                            <div className="text-right">
                                <p className="text-sm font-semibold text-zinc-900">
                                    {formatCurrency(data.capital)} FCFA
                                </p>
                                <p className="text-xs text-emerald-600">
                                    +{formatCurrency(data.gain)} FCFA
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

function ResultCard({
    icon: Icon,
    label,
    value,
    accent,
}: {
    icon: React.ElementType;
    label: string;
    value: string;
    accent: 'emerald' | 'brand' | 'zinc';
}) {
    const accentColors = {
        emerald: 'bg-emerald-50 text-emerald-700',
        brand: 'bg-brand-50 text-brand-700',
        zinc: 'bg-zinc-100 text-zinc-700',
    };

    return (
        <div className="bg-white border border-zinc-200 rounded-xl p-4">
            <div className="flex items-center gap-2 mb-2">
                <div className={`w-7 h-7 rounded-lg flex items-center justify-center ${accentColors[accent]}`}>
                    <Icon className="w-3.5 h-3.5" />
                </div>
                <p className="text-xs font-medium text-zinc-500 uppercase tracking-wide">
                    {label}
                </p>
            </div>
            <p className="text-xl font-bold text-zinc-900 tracking-tight">
                {value}
                <span className="text-xs font-normal text-zinc-500 ml-1.5">FCFA</span>
            </p>
        </div>
    );
}
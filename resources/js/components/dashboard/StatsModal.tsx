import { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import {
    X,
    Eye,
    Users,
    Heart,
    MessageCircle,
    Share2,
    TrendingUp,
    Loader2,
    AlertCircle,
} from 'lucide-react';

interface Stats {
    views_total: number;
    views_unique: number;
    likes: number;
    comments: number;
    shares: number;
    interactions: number;
    engagement_rate: number;
    serie: { date: string; label: string; total: number }[];
    published_at: string;
    published_human: string;
}

interface Props {
    publicationId: number;
    onClose: () => void;
}

const formatNumber = (n: number) => new Intl.NumberFormat('fr-FR').format(n);

export default function StatsModal({ publicationId, onClose }: Props) {
    const [stats, setStats] = useState<Stats | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let cancelled = false;

        fetch(`/publications/${publicationId}/stats`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((res) => {
                if (!res.ok) throw new Error(String(res.status));
                return res.json();
            })
            .then((data) => !cancelled && setStats(data))
            .catch(() => !cancelled && setError('Impossible de charger les statistiques.'));

        return () => {
            cancelled = true;
        };
    }, [publicationId]);

    const maxDay = stats ? Math.max(...stats.serie.map((d) => d.total), 1) : 1;

    return (
        <div
            className="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4"
            onClick={onClose}
        >
            <motion.div
                initial={{ opacity: 0, scale: 0.96, y: 10 }}
                animate={{ opacity: 1, scale: 1, y: 0 }}
                onClick={(e) => e.stopPropagation()}
                className="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl"
            >
                {/* En-tête */}
                <div className="flex items-center justify-between px-5 py-4 border-b border-zinc-100 sticky top-0 bg-white rounded-t-2xl">
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <TrendingUp className="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <h3 className="font-semibold text-zinc-900">Statistiques</h3>
                            <p className="text-xs text-zinc-500">
                                {stats ? `Publié ${stats.published_human}` : 'Chargement…'}
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={onClose}
                        className="p-2 hover:bg-zinc-100 rounded-full transition-colors"
                    >
                        <X className="w-5 h-5 text-zinc-500" />
                    </button>
                </div>

                <div className="p-5">
                    {/* État de chargement explicite */}
                    {!stats && !error && (
                        <div className="py-10 flex flex-col items-center gap-3">
                            <Loader2 className="w-7 h-7 text-emerald-500 animate-spin" />
                            <p className="text-sm text-zinc-500">Calcul des statistiques en cours…</p>
                            <div className="w-full grid grid-cols-2 gap-3 mt-4">
                                {[0, 1, 2, 3].map((i) => (
                                    <div key={i} className="h-20 rounded-xl bg-zinc-100 animate-pulse" />
                                ))}
                            </div>
                        </div>
                    )}

                    {error && (
                        <div className="py-10 flex flex-col items-center gap-2 text-center">
                            <AlertCircle className="w-7 h-7 text-red-500" />
                            <p className="text-sm text-zinc-600">{error}</p>
                        </div>
                    )}

                    {stats && (
                        <>
                            {/* Vues en évidence */}
                            <div className="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-5 mb-4">
                                <div className="flex items-center gap-2 text-emerald-50 text-sm mb-1">
                                    <Eye className="w-4 h-4" />
                                    Vues de la publication
                                </div>
                                <p className="text-4xl font-bold">{formatNumber(stats.views_total)}</p>
                                <p className="text-emerald-50 text-sm mt-1">
                                    dont {formatNumber(stats.views_unique)} personne
                                    {stats.views_unique > 1 ? 's' : ''} différente
                                    {stats.views_unique > 1 ? 's' : ''}
                                </p>
                            </div>

                            {/* Interactions */}
                            <div className="grid grid-cols-2 gap-3">
                                <StatTile icon={Heart} label="J'aime" value={stats.likes} tone="rose" />
                                <StatTile icon={MessageCircle} label="Commentaires" value={stats.comments} tone="blue" />
                                <StatTile icon={Share2} label="Partages" value={stats.shares} tone="violet" />
                                <StatTile icon={Users} label="Interactions" value={stats.interactions} tone="amber" />
                            </div>

                            {/* Taux d'engagement */}
                            <div className="mt-4 rounded-xl border border-zinc-200 p-4">
                                <div className="flex items-center justify-between mb-2">
                                    <span className="text-sm font-medium text-zinc-700">
                                        Taux d'engagement
                                    </span>
                                    <span className="text-lg font-bold text-emerald-600">
                                        {stats.engagement_rate} %
                                    </span>
                                </div>
                                <div className="h-2 bg-zinc-100 rounded-full overflow-hidden">
                                    <div
                                        className="h-full bg-emerald-500 rounded-full transition-all"
                                        style={{ width: `${Math.min(stats.engagement_rate, 100)}%` }}
                                    />
                                </div>
                                <p className="text-xs text-zinc-500 mt-2">
                                    Part des vues ayant donné lieu à une réaction, un commentaire ou un partage.
                                </p>
                            </div>

                            {/* Vues des 7 derniers jours */}
                            <div className="mt-4 rounded-xl border border-zinc-200 p-4">
                                <p className="text-sm font-medium text-zinc-700 mb-3">
                                    Vues des 7 derniers jours
                                </p>
                                <div className="flex items-end justify-between gap-2 h-28">
                                    {stats.serie.map((jour) => (
                                        <div key={jour.date} className="flex-1 flex flex-col items-center gap-1.5">
                                            <span className="text-[11px] font-medium text-zinc-500">
                                                {jour.total > 0 ? jour.total : ''}
                                            </span>
                                            <div
                                                className="w-full bg-emerald-500 rounded-t-md min-h-[3px] transition-all"
                                                style={{ height: `${(jour.total / maxDay) * 100}%` }}
                                                title={`${jour.total} vue${jour.total > 1 ? 's' : ''}`}
                                            />
                                            <span className="text-[11px] text-zinc-400 capitalize">
                                                {jour.label}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <p className="text-xs text-zinc-400 mt-4 text-center">
                                Publiée le {stats.published_at}
                            </p>
                        </>
                    )}
                </div>
            </motion.div>
        </div>
    );
}

function StatTile({
    icon: Icon,
    label,
    value,
    tone,
}: {
    icon: typeof Heart;
    label: string;
    value: number;
    tone: 'rose' | 'blue' | 'violet' | 'amber';
}) {
    const tones = {
        rose: 'bg-rose-50 text-rose-600',
        blue: 'bg-blue-50 text-blue-600',
        violet: 'bg-violet-50 text-violet-600',
        amber: 'bg-amber-50 text-amber-600',
    };

    return (
        <div className="rounded-xl border border-zinc-200 p-3.5">
            <div className={`w-8 h-8 rounded-lg flex items-center justify-center mb-2 ${tones[tone]}`}>
                <Icon className="w-4 h-4" />
            </div>
            <p className="text-2xl font-bold text-zinc-900">{formatNumber(value)}</p>
            <p className="text-xs text-zinc-500">{label}</p>
        </div>
    );
}

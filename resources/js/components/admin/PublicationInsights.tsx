import { Link } from '@inertiajs/react';
import { Eye, Users, UserCheck, Globe, TrendingUp } from 'lucide-react';

interface Stats {
    views_total: number;
    views_unique: number;
    views_identified: number;
    views_anonymous: number;
    likes: number;
    comments: number;
    shares: number;
    interactions: number;
    engagement_rate: number;
    serie: { date: string; label: string; total: number }[];
}

interface Viewer {
    type: 'membre' | 'visiteur';
    id: number | null;
    nom: string;
    email: string | null;
    photo: string | null;
    ip?: string;
    vues: number;
    derniere_vue: string;
    derniere_vue_date: string;
}

const nf = (n: number) => new Intl.NumberFormat('fr-FR').format(n);

/**
 * Vue d'ensemble d'une publication pour l'administration : audience,
 * engagement et liste nominative des personnes qui l'ont vue.
 */
export default function PublicationInsights({
    stats,
    viewers,
}: {
    stats: Stats;
    viewers: Viewer[];
}) {
    const maxDay = Math.max(...stats.serie.map((d) => d.total), 1);

    return (
        <div className="space-y-4">
            {/* Audience */}
            <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                    <TrendingUp className="w-4 h-4 text-emerald-500" />
                    <h3 className="font-semibold text-zinc-900">Audience</h3>
                </div>

                <div className="p-5">
                    <div className="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-4 mb-4">
                        <div className="flex items-center gap-2 text-emerald-50 text-xs mb-1">
                            <Eye className="w-4 h-4" />
                            Vues totales
                        </div>
                        <p className="text-3xl font-bold">{nf(stats.views_total)}</p>
                        <p className="text-emerald-50 text-xs mt-1">
                            {nf(stats.views_unique)} personne{stats.views_unique > 1 ? 's' : ''} différente
                            {stats.views_unique > 1 ? 's' : ''}
                        </p>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <Tile
                            icon={UserCheck}
                            label="Membres identifiés"
                            value={stats.views_identified}
                            tone="bg-blue-50 text-blue-600"
                        />
                        <Tile
                            icon={Globe}
                            label="Visiteurs anonymes"
                            value={stats.views_anonymous}
                            tone="bg-amber-50 text-amber-600"
                        />
                        <Tile
                            icon={Users}
                            label="Interactions"
                            value={stats.interactions}
                            tone="bg-violet-50 text-violet-600"
                        />
                        <div className="rounded-xl border border-zinc-200 p-3">
                            <p className="text-xs text-zinc-500 mb-1">Engagement</p>
                            <p className="text-2xl font-bold text-emerald-600">
                                {stats.engagement_rate} %
                            </p>
                            <div className="h-1.5 bg-zinc-100 rounded-full overflow-hidden mt-2">
                                <div
                                    className="h-full bg-emerald-500 rounded-full"
                                    style={{ width: `${Math.min(stats.engagement_rate, 100)}%` }}
                                />
                            </div>
                        </div>
                    </div>

                    {/* 7 derniers jours */}
                    <div className="mt-4 rounded-xl border border-zinc-200 p-4">
                        <p className="text-sm font-medium text-zinc-700 mb-3">
                            Vues des 7 derniers jours
                        </p>
                        <div className="flex items-end justify-between gap-2 h-24">
                            {stats.serie.map((jour) => (
                                <div key={jour.date} className="flex-1 flex flex-col items-center gap-1">
                                    <span className="text-[10px] font-medium text-zinc-500">
                                        {jour.total > 0 ? jour.total : ''}
                                    </span>
                                    <div
                                        className="w-full bg-emerald-500 rounded-t-md min-h-[3px]"
                                        style={{ height: `${(jour.total / maxDay) * 100}%` }}
                                        title={`${jour.total} vue${jour.total > 1 ? 's' : ''}`}
                                    />
                                    <span className="text-[10px] text-zinc-400 capitalize">
                                        {jour.label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Qui a vu la publication */}
            <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                    <Eye className="w-4 h-4 text-emerald-500" />
                    <h3 className="font-semibold text-zinc-900">
                        Personnes ayant vu ({viewers.length})
                    </h3>
                </div>

                <div className="divide-y divide-zinc-100 max-h-96 overflow-y-auto">
                    {viewers.length === 0 && (
                        <p className="px-5 py-6 text-sm text-zinc-500 text-center">
                            Aucune vue enregistrée pour le moment
                        </p>
                    )}

                    {viewers.map((v, i) => (
                        <div key={`${v.type}-${v.id ?? v.ip}-${i}`} className="px-5 py-3 flex items-center gap-3">
                            {v.type === 'membre' ? (
                                <img
                                    src={
                                        v.photo ||
                                        `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                            v.nom
                                        )}&background=10b981&color=fff`
                                    }
                                    alt={v.nom}
                                    className="w-8 h-8 rounded-full object-cover flex-shrink-0"
                                />
                            ) : (
                                <div className="w-8 h-8 rounded-full bg-zinc-100 flex items-center justify-center flex-shrink-0">
                                    <Globe className="w-4 h-4 text-zinc-400" />
                                </div>
                            )}

                            <div className="flex-1 min-w-0">
                                {v.type === 'membre' && v.id ? (
                                    <Link
                                        href={`/admin/users/${v.id}`}
                                        className="text-sm font-medium text-zinc-900 hover:text-emerald-600 truncate block"
                                    >
                                        {v.nom}
                                    </Link>
                                ) : (
                                    <p className="text-sm font-medium text-zinc-700 truncate">{v.nom}</p>
                                )}
                                <p className="text-[11px] text-zinc-500 truncate">
                                    {v.type === 'membre' ? v.email : v.ip} · {v.derniere_vue}
                                </p>
                            </div>

                            <span
                                className="text-[11px] font-medium text-zinc-500 bg-zinc-100 px-2 py-0.5 rounded-full flex-shrink-0"
                                title={`Dernière vue : ${v.derniere_vue_date}`}
                            >
                                {v.vues} vue{v.vues > 1 ? 's' : ''}
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

function Tile({
    icon: Icon,
    label,
    value,
    tone,
}: {
    icon: typeof Eye;
    label: string;
    value: number;
    tone: string;
}) {
    return (
        <div className="rounded-xl border border-zinc-200 p-3">
            <div className={`w-7 h-7 rounded-lg flex items-center justify-center mb-2 ${tone}`}>
                <Icon className="w-3.5 h-3.5" />
            </div>
            <p className="text-2xl font-bold text-zinc-900">{nf(value)}</p>
            <p className="text-xs text-zinc-500">{label}</p>
        </div>
    );
}

import { Fragment, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { History, Ban, Search, ChevronDown, ChevronRight, Users, AlertTriangle } from 'lucide-react';

interface Utilisateur { id: number; nom: string; email: string; bloque: boolean }
interface Connexion { ip: string; visites: number; comptes: number; derniere: string; bannie: boolean; utilisateurs: Utilisateur[] }
interface Props { connexions: Connexion[]; recherche: string }

export default function Connexions({ connexions, recherche }: Props) {
    const flash = (usePage().props as any).flash?.success as string | undefined;
    const [q, setQ] = useState(recherche);
    const [ouvert, setOuvert] = useState<string | null>(null);

    const chercher = () => router.get('/admin/securite/connexions', q ? { q } : {}, { preserveScroll: true, preserveState: true });

    const bannir = (ip: string) => {
        if (!confirm(`Bannir l'adresse IP ${ip} ? Elle ne pourra plus accéder au site (sauf les administrateurs).`)) return;
        router.post('/admin/securite/ips', { ip, raison: 'Historique de connexion' }, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Historique de connexion">
            <Head title="Historique de connexion — Admin" />

            <div className="flex items-center gap-3 mb-5">
                <div className="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center"><History className="w-6 h-6 text-indigo-600" /></div>
                <div>
                    <h1 className="text-xl font-bold text-zinc-900">Historique de connexion</h1>
                    <p className="text-sm text-zinc-500">Adresses IP des 90 derniers jours. Une IP avec plusieurs comptes = possible faux comptes.</p>
                </div>
            </div>

            {flash && <div className="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}

            {/* Recherche */}
            <div className="flex gap-2 mb-4 max-w-md">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-2.5 w-4 h-4 text-zinc-400" />
                    <input value={q} onChange={(e) => setQ(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && chercher()}
                        placeholder="Filtrer par IP…" className="w-full pl-9 pr-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <button onClick={chercher} className="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-semibold">Filtrer</button>
            </div>

            <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-zinc-50 text-zinc-500 text-xs uppercase">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Adresse IP</th>
                                <th className="text-left px-4 py-3 font-medium">Comptes</th>
                                <th className="text-left px-4 py-3 font-medium">Visites</th>
                                <th className="text-left px-4 py-3 font-medium">Dernière activité</th>
                                <th className="text-right px-4 py-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-zinc-100">
                            {connexions.map((c) => {
                                const suspect = c.comptes > 1;
                                const estOuvert = ouvert === c.ip;
                                return (
                                    <Fragment key={c.ip}>
                                        <tr className={`hover:bg-zinc-50 ${suspect ? 'bg-amber-50/40' : ''}`}>
                                            <td className="px-4 py-3">
                                                <button onClick={() => setOuvert(estOuvert ? null : c.ip)} className="inline-flex items-center gap-1.5 font-mono font-medium text-zinc-900">
                                                    {c.utilisateurs.length > 0 && (estOuvert ? <ChevronDown className="w-4 h-4 text-zinc-400" /> : <ChevronRight className="w-4 h-4 text-zinc-400" />)}
                                                    {c.ip}
                                                    {c.bannie && <span className="text-[11px] font-semibold text-red-600 bg-red-50 rounded-full px-2 py-0.5">bannie</span>}
                                                </button>
                                            </td>
                                            <td className="px-4 py-3">
                                                <span className={`inline-flex items-center gap-1 font-semibold ${suspect ? 'text-amber-700' : 'text-zinc-600'}`}>
                                                    {suspect && <AlertTriangle className="w-3.5 h-3.5" />}
                                                    <Users className="w-3.5 h-3.5" />{c.comptes}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-zinc-600">{c.visites}</td>
                                            <td className="px-4 py-3 text-zinc-400 whitespace-nowrap">{c.derniere}</td>
                                            <td className="px-4 py-3 text-right">
                                                {c.bannie ? (
                                                    <span className="text-xs text-zinc-400">déjà bannie</span>
                                                ) : (
                                                    <button onClick={() => bannir(c.ip)} className="inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-800">
                                                        <Ban className="w-4 h-4" /> Bannir
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                        {estOuvert && c.utilisateurs.length > 0 && (
                                            <tr className="bg-zinc-50/60">
                                                <td colSpan={5} className="px-6 py-3">
                                                    <p className="text-xs font-medium text-zinc-500 mb-2">Comptes vus depuis cette IP :</p>
                                                    <div className="flex flex-wrap gap-2">
                                                        {c.utilisateurs.map((u) => (
                                                            <Link key={u.id} href={`/admin/users/${u.id}`}
                                                                className="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs hover:border-indigo-300">
                                                                <span className="font-medium text-zinc-800">{u.nom}</span>
                                                                <span className="text-zinc-400">{u.email}</span>
                                                                {u.bloque && <span className="text-[10px] font-semibold text-red-600">bloqué</span>}
                                                            </Link>
                                                        ))}
                                                    </div>
                                                </td>
                                            </tr>
                                        )}
                                    </Fragment>
                                );
                            })}
                            {connexions.length === 0 && (
                                <tr><td colSpan={5} className="px-4 py-12 text-center text-zinc-400">Aucune connexion enregistrée.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}

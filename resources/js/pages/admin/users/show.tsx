import { Head, Link, router, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import {
    ArrowLeft,
    Mail,
    Phone,
    MapPin,
    Calendar,
    Shield,
    Ban,
    CheckCircle,
    XCircle,
    Trash2,
    Edit3,
    FileText,
    MessageSquare,
    Heart,
    Eye,
    Save,
    X,
} from 'lucide-react';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer,
    CartesianGrid,
} from 'recharts';
import AdminLayout from '@/components/layouts/AdminLayout';

interface UserDetail {
    id: number;
    ref: string;
    name: string;
    last_name: string | null;
    email: string;
    phone: string | null;
    photo: string | null;
    cover_img: string | null;
    role: string | null;
    country: string | null;
    country_code: string | number | null;
    ville: string | null;
    sexe: string | null;
    birth: string | null;
    description: string | null;
    valid: boolean;
    email_verifie: boolean;
    confirmed: boolean;
    status: string | null;
    is_blocked: boolean;
    referral_code: string | null;
    last_active: string | null;
    last_active_human: string | null;
    created_at: string;
    created_at_human: string;
}

interface Props {
    user: UserDetail;
    userStats: {
        publications: number;
        comments: number;
        likes_given: number;
        visits: number;
    };
    recentPublications: Array<{
        id: number;
        text: string;
        created_at_human: string;
    }>;
    commentaires: Array<{
        id: number;
        body: string;
        date: string | null;
        publication: { id: number; lien: string; auteur: string; extrait: string } | null;
    }>;
    activityChart: Array<{ label: string; visits: number }>;
    connexions: Array<{ ip: string; nombre: number; derniere: string; bannie: boolean }>;
    comptesLies: Array<{ id: number; nom: string; email: string; phone: string | null; bloque: boolean; meme_phone: boolean; meme_email: boolean }>;
    parrainage: {
        code: string | null;
        parrain: { id: number; nom: string; code: string | null } | null;
        filleuls: Array<{ id: number; nom: string; code: string | null; date: string }>;
        nb_filleuls: number;
    };
}

function countryCodeToFlag(code: string | number | null): string {
    if (!code) return '🌍';

    const codeStr = String(code).trim();

    if (/^\d+$/.test(codeStr)) return '📞';
    if (codeStr.length !== 2 || !/^[a-zA-Z]{2}$/.test(codeStr)) return '🌍';

    return codeStr
        .toUpperCase()
        .replace(/./g, (c) => String.fromCodePoint(127397 + c.charCodeAt(0)));
}

export default function UserShow({
    user,
    userStats,
    recentPublications,
    commentaires,
    activityChart,
    connexions,
    comptesLies,
    parrainage,
}: Props) {
    const [editMode, setEditMode] = useState(false);
    const [confirmDelete, setConfirmDelete] = useState(false);

    const bannirIp = (ip: string) => {
        if (!confirm(`Bannir l'adresse IP ${ip} ? Elle ne pourra plus accéder au site (sauf les administrateurs).`)) return;
        router.post('/admin/securite/ips', { ip, raison: `Utilisateur #${user.id} — ${user.name}` }, { preserveScroll: true });
    };

    const { data, setData, patch, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        phone: user.phone || '',
        role: user.role || 'user',
        country: user.country || '',
        ville: user.ville || '',
        description: user.description || '',
        password: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(`/admin/users/${user.id}`, {
            onSuccess: () => setEditMode(false),
            preserveScroll: true,
        });
    };

    const handleToggleStatus = () => {
        router.patch(
            `/admin/users/${user.id}/toggle-status`,
            {},
            { preserveScroll: true },
        );
    };

    const handleToggleValidation = () => {
        router.patch(
            `/admin/users/${user.id}/toggle-validation`,
            {},
            { preserveScroll: true },
        );
    };

    const handleToggleEmailVerified = () => {
        router.patch(`/admin/users/${user.id}/toggle-email-verified`, {}, { preserveScroll: true });
    };

    const handleDelete = () => {
        router.delete(`/admin/users/${user.id}`);
    };

    return (
        <AdminLayout title={user.name}>
            <Head title={`Admin - ${user.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between flex-wrap gap-3">
                    <Link
                        href="/admin/users"
                        className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900 transition-colors"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Retour à la liste
                    </Link>

                    {!editMode && (
                        <div className="flex gap-2 flex-wrap">
                            <button
                                onClick={() => setEditMode(true)}
                                className="flex items-center gap-1.5 px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-700 hover:bg-zinc-50 transition-colors"
                            >
                                <Edit3 className="w-4 h-4" />
                                Modifier
                            </button>
                            <button
                                onClick={handleToggleValidation}
                                className={`flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition-colors ${
                                    user.valid
                                        ? 'bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50'
                                        : 'bg-blue-500 text-white hover:bg-blue-600'
                                }`}
                            >
                                {user.valid ? (
                                    <XCircle className="w-4 h-4" />
                                ) : (
                                    <CheckCircle className="w-4 h-4" />
                                )}
                                {user.valid ? 'Dévalider' : 'Valider'}
                            </button>
                            <button
                                onClick={handleToggleStatus}
                                className={`flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition-colors ${
                                    user.is_blocked
                                        ? 'bg-emerald-500 text-white hover:bg-emerald-600'
                                        : 'bg-orange-500 text-white hover:bg-orange-600'
                                }`}
                            >
                                <Ban className="w-4 h-4" />
                                {user.is_blocked ? 'Débloquer' : 'Bloquer'}
                            </button>
                            <button
                                onClick={handleToggleEmailVerified}
                                title="Confirmer / retirer la vérification de l'e-mail"
                                className={`flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition-colors ${
                                    user.email_verifie
                                        ? 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
                                        : 'bg-sky-500 text-white hover:bg-sky-600'
                                }`}
                            >
                                <Mail className="w-4 h-4" />
                                {user.email_verifie ? 'E-mail vérifié' : 'Confirmer l\'e-mail'}
                            </button>
                            <button
                                onClick={() => setConfirmDelete(true)}
                                className="flex items-center gap-1.5 px-3 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition-colors"
                            >
                                <Trash2 className="w-4 h-4" />
                                Supprimer
                            </button>
                        </div>
                    )}
                </div>

                {/* Header Card */}
                <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                    <div
                        className="h-32 bg-gradient-to-br from-emerald-500 to-emerald-700"
                        style={
                            user.cover_img
                                ? {
                                      backgroundImage: `url(${user.cover_img})`,
                                      backgroundSize: 'cover',
                                      backgroundPosition: 'center',
                                  }
                                : {}
                        }
                    />
                    <div className="px-6 pb-6 -mt-12">
                        <img
                            src={
                                user.photo ||
                                `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                    user.name,
                                )}&background=10b981&color=fff&size=128`
                            }
                            alt={user.name}
                            className="w-24 h-24 rounded-full object-cover ring-4 ring-white shadow-lg"
                        />

                        <div className="mt-4 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 className="text-2xl font-bold text-zinc-900">
                                    {user.name}{' '}
                                    {user.last_name && (
                                        <span className="text-zinc-500 font-normal">
                                            {user.last_name}
                                        </span>
                                    )}
                                </h2>
                                <p className="text-sm text-zinc-500 mt-0.5">
                                    Ref: {user.ref || `#${user.id}`}
                                </p>

                                <div className="flex flex-wrap gap-1.5 mt-3">
                                    {user.role && user.role !== 'user' && (
                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded uppercase">
                                            <Shield className="w-3 h-3" />
                                            {user.role}
                                        </span>
                                    )}
                                    {user.is_blocked ? (
                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded uppercase">
                                            <Ban className="w-3 h-3" />
                                            Bloqué
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded uppercase">
                                            <CheckCircle className="w-3 h-3" />
                                            Actif
                                        </span>
                                    )}
                                    {user.valid && (
                                        <span className="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded uppercase">
                                            ✓ Validé
                                        </span>
                                    )}
                                    {user.confirmed && (
                                        <span className="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded uppercase">
                                            ✓ Confirmé
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>

                        {user.description && (
                            <p className="mt-4 text-sm text-zinc-600 leading-relaxed">
                                {user.description}
                            </p>
                        )}
                    </div>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatBox
                        icon={FileText}
                        label="Publications"
                        value={userStats.publications}
                        color="emerald"
                    />
                    <StatBox
                        icon={MessageSquare}
                        label="Commentaires"
                        value={userStats.comments}
                        color="blue"
                    />
                    <StatBox
                        icon={Heart}
                        label="Likes donnés"
                        value={userStats.likes_given}
                        color="rose"
                    />
                    <StatBox
                        icon={Eye}
                        label="Visites"
                        value={userStats.visits}
                        color="orange"
                    />
                </div>

                {/* Info + Activity */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div className="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                        {!editMode ? (
                            <>
                                <h3 className="font-semibold text-zinc-900 mb-4">Informations</h3>
                                <div className="space-y-3">
                                    <InfoRow icon={Mail} label="Email" value={user.email} />
                                    <InfoRow icon={Phone} label="Téléphone" value={user.phone || '—'} />
                                    <InfoRow
                                        icon={MapPin}
                                        label="Pays"
                                        value={
                                            user.country
                                                ? `${countryCodeToFlag(user.country_code)} ${user.country}`
                                                : '—'
                                        }
                                    />
                                    <InfoRow icon={MapPin} label="Ville" value={user.ville || '—'} />
                                    <InfoRow
                                        icon={Calendar}
                                        label="Inscrit le"
                                        value={`${user.created_at} (${user.created_at_human})`}
                                    />
                                    {user.last_active && (
                                        <InfoRow
                                            icon={Calendar}
                                            label="Dernière activité"
                                            value={user.last_active_human || ''}
                                        />
                                    )}
                                    {user.sexe && (
                                        <InfoRow label="Sexe" value={user.sexe === 'H' ? 'Homme' : 'Femme'} />
                                    )}
                                    {user.referral_code && (
                                        <InfoRow label="Code parrainage" value={user.referral_code} mono />
                                    )}
                                </div>
                            </>
                        ) : (
                            <form onSubmit={handleSubmit} className="space-y-3">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="font-semibold text-zinc-900">Modifier</h3>
                                    <button
                                        type="button"
                                        onClick={() => setEditMode(false)}
                                        className="p-1 hover:bg-zinc-100 rounded"
                                    >
                                        <X className="w-4 h-4" />
                                    </button>
                                </div>

                                <EditField
                                    label="Nom"
                                    value={data.name}
                                    onChange={(v) => setData('name', v)}
                                    error={errors.name}
                                />
                                <EditField
                                    label="Email"
                                    type="email"
                                    value={data.email}
                                    onChange={(v) => setData('email', v)}
                                    error={errors.email}
                                />
                                <EditField
                                    label="Téléphone"
                                    value={data.phone}
                                    onChange={(v) => setData('phone', v)}
                                    error={errors.phone}
                                />
                                <div>
                                    <label className="block text-xs font-medium text-zinc-500 mb-1">
                                        Rôle
                                    </label>
                                    <select
                                        value={data.role}
                                        onChange={(e) => setData('role', e.target.value)}
                                        className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="user">Utilisateur</option>
                                        <option value="admin">Administrateur</option>
                                        <option value="super-admin">Super Admin</option>
                                    </select>
                                </div>
                                <EditField
                                    label="Pays"
                                    value={data.country}
                                    onChange={(v) => setData('country', v)}
                                />
                                <EditField
                                    label="Ville"
                                    value={data.ville}
                                    onChange={(v) => setData('ville', v)}
                                />
                                <div>
                                    <label className="block text-xs font-medium text-zinc-500 mb-1">
                                        Description
                                    </label>
                                    <textarea
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        rows={3}
                                        className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                                    />
                                </div>
                                <EditField
                                    label="Nouveau mot de passe (optionnel)"
                                    type="password"
                                    value={data.password}
                                    onChange={(v) => setData('password', v)}
                                    error={errors.password}
                                />

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full flex items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-lg transition-colors disabled:bg-emerald-300 mt-4"
                                >
                                    <Save className="w-4 h-4" />
                                    {processing ? 'Enregistrement...' : 'Enregistrer'}
                                </button>
                            </form>
                        )}
                    </div>

                    <div className="lg:col-span-2 space-y-4">
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
                            <h3 className="font-semibold text-zinc-900 mb-1">Activité (30j)</h3>
                            <p className="text-xs text-zinc-500 mb-4">
                                Visites de l'utilisateur sur la plateforme
                            </p>
                            <ResponsiveContainer width="100%" height={200}>
                                <BarChart data={activityChart}>
                                    <CartesianGrid
                                        strokeDasharray="3 3"
                                        stroke="#e4e4e7"
                                        vertical={false}
                                    />
                                    <XAxis
                                        dataKey="label"
                                        stroke="#a1a1aa"
                                        fontSize={10}
                                        tickLine={false}
                                        axisLine={false}
                                    />
                                    <YAxis
                                        stroke="#a1a1aa"
                                        fontSize={11}
                                        tickLine={false}
                                        axisLine={false}
                                        allowDecimals={false}
                                    />
                                    <Tooltip
                                        contentStyle={{
                                            backgroundColor: '#18181b',
                                            border: 'none',
                                            borderRadius: 8,
                                            color: '#fff',
                                            fontSize: 12,
                                        }}
                                    />
                                    <Bar dataKey="visits" fill="#10b981" radius={[4, 4, 0, 0]} />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>

                        {comptesLies.length > 0 && (
                            <div className="bg-white rounded-2xl shadow-sm border border-amber-200">
                                <div className="px-5 py-4 border-b border-amber-100 flex items-center gap-2 bg-amber-50/60 rounded-t-2xl">
                                    <Ban className="w-4 h-4 text-amber-600" />
                                    <h3 className="font-semibold text-amber-900">Comptes liés (même numéro ou e-mail)</h3>
                                    <span className="ml-auto text-xs font-semibold text-amber-700 bg-amber-100 rounded-full px-2 py-0.5">{comptesLies.length}</span>
                                </div>
                                <div className="divide-y divide-zinc-100">
                                    {comptesLies.map((c) => (
                                        <Link key={c.id} href={`/admin/users/${c.id}`} className="flex items-center gap-3 px-5 py-3 hover:bg-zinc-50">
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium text-zinc-900 truncate">
                                                    {c.nom}
                                                    {c.bloque && <span className="ml-2 text-[10px] font-semibold text-red-600">bloqué</span>}
                                                </p>
                                                <p className="text-xs text-zinc-500 truncate">{c.email}{c.phone ? ' · ' + c.phone : ''}</p>
                                            </div>
                                            <div className="flex gap-1">
                                                {c.meme_phone && <span className="text-[10px] font-semibold text-amber-700 bg-amber-100 rounded px-1.5 py-0.5">même n°</span>}
                                                {c.meme_email && <span className="text-[10px] font-semibold text-amber-700 bg-amber-100 rounded px-1.5 py-0.5">même e-mail</span>}
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Parrainage (admin voit tout) */}
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                            <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                                <Shield className="w-4 h-4 text-emerald-500" />
                                <h3 className="font-semibold text-zinc-900">Parrainage</h3>
                                <span className="ml-auto text-xs text-zinc-500">Code : <span className="font-mono font-semibold text-zinc-700">{parrainage.code ?? '—'}</span></span>
                            </div>
                            <div className="px-5 py-3 text-sm">
                                <p className="text-zinc-600">
                                    Parrain :{' '}
                                    {parrainage.parrain
                                        ? <Link href={`/admin/users/${parrainage.parrain.id}`} className="font-medium text-emerald-700 hover:underline">{parrainage.parrain.nom}</Link>
                                        : <span className="text-zinc-400">aucun</span>}
                                </p>
                            </div>
                            <div className="px-5 pb-3">
                                <p className="text-xs font-medium text-zinc-500 mb-2">Filleuls ({parrainage.nb_filleuls})</p>
                                {parrainage.filleuls.length === 0 ? (
                                    <p className="text-xs text-zinc-400">Aucun filleul.</p>
                                ) : (
                                    <div className="space-y-1.5 max-h-56 overflow-y-auto">
                                        {parrainage.filleuls.map((f) => (
                                            <Link key={f.id} href={`/admin/users/${f.id}`} className="flex items-center gap-2 text-sm hover:bg-zinc-50 rounded-lg px-2 py-1.5">
                                                <span className="flex-1 text-zinc-800 truncate">{f.nom}</span>
                                                <span className="font-mono text-xs text-zinc-500">{f.code ?? '—'}</span>
                                                <span className="text-xs text-zinc-400 whitespace-nowrap">{f.date}</span>
                                            </Link>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                            <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                                <Shield className="w-4 h-4 text-zinc-400" />
                                <h3 className="font-semibold text-zinc-900">Historique de connexion (IP)</h3>
                            </div>
                            <div className="divide-y divide-zinc-100">
                                {connexions.length === 0 && (
                                    <p className="px-5 py-6 text-sm text-zinc-500 text-center">Aucune connexion enregistrée</p>
                                )}
                                {connexions.map((c) => (
                                    <div key={c.ip} className="px-5 py-3 flex items-center gap-3">
                                        <div className="flex-1 min-w-0">
                                            <p className="font-mono text-sm font-medium text-zinc-900">
                                                {c.ip}
                                                {c.bannie && <span className="ml-2 text-[11px] font-semibold text-red-600 bg-red-50 rounded-full px-2 py-0.5">bannie</span>}
                                            </p>
                                            <p className="text-xs text-zinc-500">{c.nombre} visite(s) · dernière {c.derniere}</p>
                                        </div>
                                        {c.bannie ? (
                                            <span className="text-xs text-zinc-400">déjà bannie</span>
                                        ) : (
                                            <button onClick={() => bannirIp(c.ip)}
                                                className="inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-800">
                                                <Ban className="w-4 h-4" /> Bannir
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                            <div className="px-5 py-4 border-b border-zinc-100">
                                <h3 className="font-semibold text-zinc-900">Dernières publications</h3>
                            </div>
                            <div className="divide-y divide-zinc-100">
                                {recentPublications.length === 0 && (
                                    <p className="px-5 py-6 text-sm text-zinc-500 text-center">
                                        Aucune publication
                                    </p>
                                )}
                                {recentPublications.map((pub) => (
                                    <Link key={pub.id} href={`/p/${pub.id}`} className="block px-5 py-3 hover:bg-zinc-50">
                                        <p className="text-sm text-zinc-700">
                                            {pub.text || (
                                                <span className="italic text-zinc-400">
                                                    Publication sans texte
                                                </span>
                                            )}
                                        </p>
                                        <p className="text-xs text-zinc-400 mt-1">
                                            {pub.created_at_human}
                                        </p>
                                    </Link>
                                ))}
                            </div>
                        </div>

                        {/* Commentaires de l'utilisateur */}
                        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200">
                            <div className="px-5 py-4 border-b border-zinc-100 flex items-center gap-2">
                                <MessageSquare className="w-4 h-4 text-zinc-400" />
                                <h3 className="font-semibold text-zinc-900">Commentaires de l'utilisateur</h3>
                            </div>
                            <div className="divide-y divide-zinc-100 max-h-[28rem] overflow-y-auto">
                                {commentaires.length === 0 && (
                                    <p className="px-5 py-6 text-sm text-zinc-500 text-center">Aucun commentaire</p>
                                )}
                                {commentaires.map((c) => (
                                    <div key={c.id} className="px-5 py-3">
                                        <p className="text-sm text-zinc-800">« {c.body} »</p>
                                        <div className="mt-1.5 flex items-center gap-2 text-xs text-zinc-400">
                                            <span>{c.date}</span>
                                            {c.publication && (
                                                <>
                                                    <span>·</span>
                                                    <Link href={c.publication.lien} className="inline-flex items-center gap-1 text-emerald-600 hover:underline truncate max-w-[60%]">
                                                        sur la publication de {c.publication.auteur} : “{c.publication.extrait}”
                                                    </Link>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {confirmDelete && (
                <div
                    className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                    onClick={() => setConfirmDelete(false)}
                >
                    <motion.div
                        initial={{ scale: 0.9, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        onClick={(e) => e.stopPropagation()}
                        className="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full"
                    >
                        <div className="w-12 h-12 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <Trash2 className="w-6 h-6 text-red-600" />
                        </div>
                        <h3 className="text-lg font-semibold text-zinc-900 text-center mb-2">
                            Supprimer {user.name} ?
                        </h3>
                        <p className="text-sm text-zinc-600 text-center mb-6">
                            Cette action est irréversible.
                        </p>
                        <div className="flex gap-3">
                            <button
                                onClick={() => setConfirmDelete(false)}
                                className="flex-1 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg"
                            >
                                Annuler
                            </button>
                            <button
                                onClick={handleDelete}
                                className="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg"
                            >
                                Supprimer
                            </button>
                        </div>
                    </motion.div>
                </div>
            )}
        </AdminLayout>
    );
}

function InfoRow({
    icon: Icon,
    label,
    value,
    mono,
}: {
    icon?: any;
    label: string;
    value: string;
    mono?: boolean;
}) {
    return (
        <div>
            <p className="text-xs text-zinc-500 mb-0.5 flex items-center gap-1.5">
                {Icon && <Icon className="w-3 h-3" />}
                {label}
            </p>
            <p className={`text-sm text-zinc-900 ${mono ? 'font-mono' : ''} break-words`}>
                {value}
            </p>
        </div>
    );
}

function StatBox({
    icon: Icon,
    label,
    value,
    color,
}: {
    icon: any;
    label: string;
    value: number;
    color: 'emerald' | 'blue' | 'rose' | 'orange';
}) {
    const colors = {
        emerald: 'bg-emerald-50 text-emerald-600',
        blue: 'bg-blue-50 text-blue-600',
        rose: 'bg-rose-50 text-rose-600',
        orange: 'bg-orange-50 text-orange-600',
    };
    return (
        <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5">
            <div
                className={`w-9 h-9 rounded-lg flex items-center justify-center mb-3 ${colors[color]}`}
            >
                <Icon className="w-4 h-4" />
            </div>
            <p className="text-xs text-zinc-500 mb-0.5">{label}</p>
            <p className="text-2xl font-bold text-zinc-900 tabular-nums">
                {value.toLocaleString('fr-FR')}
            </p>
        </div>
    );
}

function EditField({
    label,
    type = 'text',
    value,
    onChange,
    error,
}: {
    label: string;
    type?: string;
    value: string;
    onChange: (v: string) => void;
    error?: string;
}) {
    return (
        <div>
            <label className="block text-xs font-medium text-zinc-500 mb-1">{label}</label>
            <input
                type={type}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                className={`w-full px-3 py-2 bg-zinc-50 border ${
                    error ? 'border-red-300' : 'border-zinc-200'
                } rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500`}
            />
            {error && <p className="text-xs text-red-600 mt-1">{error}</p>}
        </div>
    );
}
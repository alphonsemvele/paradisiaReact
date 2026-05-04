import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import type { User } from '@/types';

interface Props {
    user: User | null;
}

export default function ProfileCard({ user }: Props) {
    const avatarUrl = user
        ? user.photo ||
          `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=0d9488&color=fff&size=128`
        : null;

    if (!user) {
        return <GuestCard />;
    }

    return (
        <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
            {/* Cover gradient */}
            <div className="h-20 bg-gradient-to-br from-brand-400 via-brand-500 to-brand-700" />

            {/* Profile content */}
            <div className="px-5 pb-5 -mt-10">
                <div className="flex items-end justify-between mb-4">
                    <img
                        src={avatarUrl!}
                        alt={user.name}
                        className="w-20 h-20 rounded-2xl border-4 border-white object-cover shadow-sm"
                    />
                </div>

                <h3 className="text-base font-semibold text-zinc-900">{user.name}</h3>
                <p className="text-xs text-zinc-500 mt-0.5">@{user.name.toLowerCase().replace(/\s/g, '')}</p>

                {/* Stats */}
                <div className="grid grid-cols-3 gap-2 mt-5 pt-5 border-t border-zinc-100">
                    <Stat value={0} label="Posts" />
                    <Stat value={0} label="Suivis" />
                    <Stat value={0} label="Abonnés" />
                </div>

                <Link
                    href="/profile"
                    className="mt-4 flex items-center justify-between w-full px-3 py-2.5 bg-zinc-50 hover:bg-zinc-100 rounded-lg text-sm font-medium text-zinc-700 transition-colors"
                >
                    Voir mon profil
                    <ChevronRight className="w-4 h-4" />
                </Link>
            </div>
        </div>
    );
}

function GuestCard() {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-6 text-center">
            <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h3 className="font-semibold text-zinc-900 mb-1">Bienvenue !</h3>
            <p className="text-sm text-zinc-500 mb-5">Connectez-vous pour profiter de toutes les fonctionnalités.</p>
            <div className="space-y-2">
                <Link
                    href="/login"
                    className="block w-full px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    Se connecter
                </Link>
                <Link
                    href="/register"
                    className="block w-full px-4 py-2.5 bg-zinc-50 hover:bg-zinc-100 text-zinc-700 text-sm font-medium rounded-lg transition-colors"
                >
                    Créer un compte
                </Link>
            </div>
        </div>
    );
}

function Stat({ value, label }: { value: number; label: string }) {
    return (
        <div className="text-center">
            <p className="text-base font-semibold text-zinc-900">{value}</p>
            <p className="text-[11px] text-zinc-500 mt-0.5">{label}</p>
        </div>
    );
}
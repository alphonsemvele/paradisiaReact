import { FormEvent, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Eye, EyeOff, Mail, Lock } from 'lucide-react';

interface Props {
    status?: string;
    canResetPassword: boolean;
}

interface LoginData {
    email: string;
    password: string;
    remember: boolean;
    [key: string]: any;
}

export default function Login({ status, canResetPassword }: Props) {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors } = useForm<LoginData>({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Connexion - Paradisia" />

            <div className="min-h-screen relative overflow-hidden flex items-center justify-center px-4 py-12">
                {/* ===== Image de fond plein écran ===== */}
                <div className="absolute inset-0">
                    <img
                        src="https://images.unsplash.com/photo-1546173159-315724a31696?w=1920&q=85&auto=format&fit=crop"
                        alt="Paradisia tropical"
                        className="w-full h-full object-cover"
                    />
                    {/* Overlay sombre + dégradé vert */}
                    <div className="absolute inset-0 bg-gradient-to-br from-emerald-950/80 via-emerald-900/60 to-orange-900/40" />
                </div>

                {/* ===== Card centrale ===== */}
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5 }}
                    className="relative z-10 w-full max-w-md"
                >
                    {/* Logo Paradisia (cliquable -> retour accueil) */}
                    <div className="flex justify-center mb-6">
                        <Link href="/" className="block">
                            <div className="w-24 h-24 rounded-full bg-white shadow-2xl flex items-center justify-center p-2 hover:scale-105 transition-transform">
                                <img
                                    src="/images/logo-paradisia.png"
                                    alt="Paradisia"
                                    className="w-full h-full object-contain rounded-full"
                                    onError={(e) => {
                                        e.currentTarget.style.display = 'none';
                                        e.currentTarget.parentElement!.innerHTML =
                                            '<div class="w-full h-full rounded-full bg-gradient-to-br from-emerald-500 to-orange-500 flex items-center justify-center"><span class="text-white font-bold text-3xl">P</span></div>';
                                    }}
                                />
                            </div>
                        </Link>
                    </div>

                    {/* Card formulaire */}
                    <div className="bg-white rounded-2xl shadow-2xl p-8">
                        <div className="mb-6 text-center">
                            <h1 className="text-2xl font-bold text-zinc-900 mb-1">
                                Bon retour ! 👋
                            </h1>
                            <p className="text-sm text-zinc-500">
                                Connectez-vous à votre compte Paradisia
                            </p>
                        </div>

                        {/* Status message */}
                        {status && (
                            <div className="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
                                {status}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            {/* Email */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Email
                                </label>
                                <div className="relative">
                                    <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        autoComplete="username"
                                        autoFocus
                                        required
                                        className={`w-full pl-10 pr-4 py-2.5 bg-zinc-50 border ${
                                            errors.email
                                                ? 'border-red-300'
                                                : 'border-zinc-200'
                                        } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all`}
                                        placeholder="vous@exemple.com"
                                    />
                                </div>
                                {errors.email && (
                                    <p className="mt-1 text-xs text-red-600">{errors.email}</p>
                                )}
                            </div>

                            {/* Password */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Mot de passe
                                </label>
                                <div className="relative">
                                    <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        autoComplete="current-password"
                                        required
                                        className={`w-full pl-10 pr-10 py-2.5 bg-zinc-50 border ${
                                            errors.password
                                                ? 'border-red-300'
                                                : 'border-zinc-200'
                                        } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all`}
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                                    >
                                        {showPassword ? (
                                            <EyeOff className="w-5 h-5" />
                                        ) : (
                                            <Eye className="w-5 h-5" />
                                        )}
                                    </button>
                                </div>
                                {errors.password && (
                                    <p className="mt-1 text-xs text-red-600">{errors.password}</p>
                                )}
                            </div>

                            {/* Mot de passe oublié */}
                            {canResetPassword && (
                                <div className="flex justify-end">
                                    <Link
                                        href="/forgot-password"
                                        className="text-sm text-emerald-600 hover:text-emerald-700 font-medium underline-offset-2 hover:underline"
                                    >
                                        Mot de passe oublié ?
                                    </Link>
                                </div>
                            )}

                            {/* Bouton submit */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-3 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-300 text-white font-semibold rounded-lg transition-colors shadow-sm hover:shadow-md mt-2"
                            >
                                {processing ? 'Connexion...' : 'Se connecter'}
                            </button>
                        </form>

                        {/* Lien vers register */}
                        <p className="mt-6 text-center text-sm text-zinc-600">
                            Pas encore de compte ?{' '}
                            <Link
                                href="/register"
                                className="text-emerald-600 hover:text-emerald-700 font-semibold"
                            >
                                Créer un compte
                            </Link>
                        </p>
                    </div>

                    {/* Footer */}
                    <p className="text-center text-xs text-white/70 mt-6">
                        © {new Date().getFullYear()} Paradisia Africa. Tous droits réservés.
                    </p>
                </motion.div>
            </div>
        </>
    );
}
import { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Mail } from 'lucide-react';

interface Props { status?: string }

export default function ForgotPassword({ status }: Props) {
    const { data, setData, post, processing, errors } = useForm<{ email: string; [key: string]: any }>({ email: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <>
            <Head title="Mot de passe oublié - Paradisia" />
            <div className="min-h-screen relative overflow-hidden flex items-center justify-center px-4 py-12">
                <div className="absolute inset-0">
                    <img src="https://images.unsplash.com/photo-1546173159-315724a31696?w=1920&q=85&auto=format&fit=crop" alt="Paradisia" className="w-full h-full object-cover" />
                    <div className="absolute inset-0 bg-gradient-to-br from-emerald-950/80 via-emerald-900/60 to-orange-900/40" />
                </div>

                <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.5 }} className="relative z-10 w-full max-w-md">
                    <div className="flex justify-center mb-6">
                        <Link href="/" className="block">
                            <div className="w-20 h-20 rounded-full bg-white shadow-2xl flex items-center justify-center p-2">
                                <img src="/images/logo-paradisia.png" alt="Paradisia" className="w-full h-full object-contain rounded-full"
                                    onError={(e) => { e.currentTarget.style.display = 'none'; e.currentTarget.parentElement!.innerHTML = '<div class="w-full h-full rounded-full bg-gradient-to-br from-emerald-500 to-orange-500 flex items-center justify-center"><span class="text-white font-bold text-3xl">P</span></div>'; }} />
                            </div>
                        </Link>
                    </div>

                    <div className="bg-white rounded-2xl shadow-2xl p-8">
                        <div className="mb-6 text-center">
                            <h1 className="text-2xl font-bold text-zinc-900 mb-1">Mot de passe oublié ?</h1>
                            <p className="text-sm text-zinc-500">Saisis ton e-mail : on t'envoie un lien pour en choisir un nouveau.</p>
                        </div>

                        {status && (
                            <div className="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">{status}</div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">Email</label>
                                <div className="relative">
                                    <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                    <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} autoFocus required
                                        className={`w-full pl-10 pr-4 py-2.5 bg-zinc-50 border ${errors.email ? 'border-red-300' : 'border-zinc-200'} rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all`}
                                        placeholder="vous@exemple.com" />
                                </div>
                                {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                            </div>

                            <button type="submit" disabled={processing}
                                className="w-full py-3 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-300 text-white font-semibold rounded-lg transition-colors">
                                {processing ? 'Envoi…' : 'Envoyer le lien'}
                            </button>
                        </form>

                        <p className="mt-6 text-center text-sm text-zinc-600">
                            <Link href="/login" className="text-emerald-600 hover:text-emerald-700 font-semibold">Retour à la connexion</Link>
                        </p>
                    </div>
                </motion.div>
            </div>
        </>
    );
}

import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/components/layouts/AppLayout';
import { Gift, Copy, Check, Share2, Users, Ticket } from 'lucide-react';

interface Filleul { code: string; date: string }
interface Props { code: string; lien: string; filleuls: Filleul[]; nb_filleuls: number }

export default function Parrainage({ code, lien, filleuls, nb_filleuls }: Props) {
    const [copie, setCopie] = useState<'code' | 'lien' | null>(null);

    const copier = (valeur: string, quoi: 'code' | 'lien') => {
        navigator.clipboard?.writeText(valeur).then(() => {
            setCopie(quoi);
            setTimeout(() => setCopie(null), 1800);
        });
    };

    const texte = `Rejoins-moi sur Paradisia ! Inscris-toi avec mon lien de parrainage : ${lien}`;
    const whatsapp = `https://wa.me/?text=${encodeURIComponent(texte)}`;

    return (
        <AppLayout>
            <Head title="Parrainage — Paradisia" />

            <div className="max-w-2xl mx-auto px-4 py-6">
                {/* Héro */}
                <div className="rounded-3xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white p-6 text-center shadow-lg">
                    <div className="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center mx-auto mb-3"><Gift className="w-7 h-7" /></div>
                    <h1 className="text-2xl font-bold">Parraine tes proches</h1>
                    <p className="mt-1 text-sm text-emerald-50/90">Partage ton code ou ton lien. Ceux qui s'inscrivent avec deviennent tes filleuls.</p>

                    {/* Code */}
                    <div className="mt-5 bg-white/10 rounded-2xl p-4">
                        <p className="text-xs text-emerald-50/80 uppercase tracking-wide mb-1">Mon code</p>
                        <div className="flex items-center justify-center gap-2">
                            <span className="text-2xl font-extrabold tracking-wider font-mono">{code}</span>
                            <button onClick={() => copier(code, 'code')} className="p-2 rounded-lg bg-white/15 hover:bg-white/25" title="Copier le code">
                                {copie === 'code' ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
                            </button>
                        </div>
                    </div>
                </div>

                {/* Lien + partage */}
                <div className="mt-4 bg-white rounded-2xl border border-zinc-200 p-4">
                    <p className="text-xs font-medium text-zinc-500 mb-1.5">Mon lien de parrainage</p>
                    <div className="flex items-center gap-2">
                        <input readOnly value={lien} className="flex-1 min-w-0 px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-sm text-zinc-700" />
                        <button onClick={() => copier(lien, 'lien')} className="px-3 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-semibold flex items-center gap-1.5">
                            {copie === 'lien' ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />} {copie === 'lien' ? 'Copié' : 'Copier'}
                        </button>
                    </div>
                    <a href={whatsapp} target="_blank" rel="noopener noreferrer"
                        className="mt-3 w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold">
                        <Share2 className="w-5 h-5" /> Partager sur WhatsApp
                    </a>
                </div>

                {/* Filleuls */}
                <div className="mt-6">
                    <div className="flex items-center justify-between mb-3">
                        <h2 className="text-sm font-bold text-zinc-500 uppercase tracking-wide">Mes filleuls</h2>
                        <span className="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700 bg-emerald-100 rounded-full px-3 py-1">
                            <Users className="w-4 h-4" /> {nb_filleuls}
                        </span>
                    </div>

                    <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                        <div className="px-4 py-2.5 bg-amber-50 border-b border-amber-100 text-xs text-amber-800">
                            🔒 Pour préserver leur confidentialité, seuls les codes de tes filleuls sont affichés.
                        </div>
                        {filleuls.length === 0 ? (
                            <p className="text-center text-zinc-400 py-12 text-sm">Aucun filleul pour l'instant.<br />Partage ton lien pour commencer !</p>
                        ) : (
                            <div className="divide-y divide-zinc-100">
                                {filleuls.map((f, i) => (
                                    <div key={i} className="flex items-center gap-3 px-4 py-3">
                                        <div className="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><Ticket className="w-4 h-4" /></div>
                                        <span className="flex-1 font-mono font-semibold text-sm text-zinc-800">{f.code}</span>
                                        <span className="text-xs text-zinc-400">{f.date}</span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

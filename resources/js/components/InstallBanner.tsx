import { useEffect, useState } from 'react';
import { X, Download, MessageCircle } from 'lucide-react';
import { usePwaInstall } from '@/lib/pwa';

const CLE = 'pwa_chat_dismiss';
const CACHE_MS = 1000 * 60 * 60 * 24 * 5; // ré-affiche 5 jours après fermeture

/** Bannière globale « Installe le tchat » (visible tant que non installé). */
export default function InstallBanner() {
    const { installe, peutProposer, iOS, installer } = usePwaInstall();
    const [ferme, setFerme] = useState(true);
    const [aide, setAide] = useState(false);

    useEffect(() => {
        const ts = Number(localStorage.getItem(CLE) || 0);
        setFerme(Date.now() - ts < CACHE_MS);
    }, []);

    if (installe || !peutProposer || ferme) return null;

    const fermer = () => { localStorage.setItem(CLE, String(Date.now())); setFerme(true); };
    const cliquer = async () => {
        const r = await installer();
        if (r === 'accepted') fermer();
        else if (r === 'ios' || r === 'manuel') setAide(true);
    };

    return (
        <>
            <div className="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white">
                <div className="max-w-7xl mx-auto px-4 py-2.5 flex items-center gap-3">
                    <span className="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center flex-shrink-0">
                        <MessageCircle className="w-4.5 h-4.5" />
                    </span>
                    <p className="flex-1 text-sm leading-snug">
                        <b>Installe le tchat Paradisia</b> sur ton téléphone — accès direct, plein écran, comme une app.
                    </p>
                    <button onClick={cliquer}
                        className="flex-shrink-0 inline-flex items-center gap-1.5 bg-white text-emerald-700 text-sm font-bold rounded-full px-3.5 py-1.5 hover:bg-emerald-50 transition-colors">
                        <Download className="w-4 h-4" /> Installer
                    </button>
                    <button onClick={fermer} aria-label="Fermer" className="flex-shrink-0 p-1.5 hover:bg-white/20 rounded-full">
                        <X className="w-4 h-4" />
                    </button>
                </div>
            </div>

            {aide && (
                <div className="fixed inset-0 z-[70] bg-black/50 flex items-end sm:items-center justify-center p-4" onClick={() => setAide(false)}>
                    <div className="bg-white rounded-2xl w-full max-w-sm p-6 text-center" onClick={(e) => e.stopPropagation()}>
                        <div className="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-3">
                            <Download className="w-7 h-7 text-emerald-600" />
                        </div>
                        <h3 className="font-bold text-zinc-900 text-lg">Installer le tchat</h3>
                        {iOS ? (
                            <p className="mt-2 text-sm text-zinc-600">
                                Sur iPhone : appuie sur le bouton <b>Partager</b> ⬆️ de Safari,
                                puis <b>« Sur l'écran d'accueil »</b>.
                            </p>
                        ) : (
                            <p className="mt-2 text-sm text-zinc-600">
                                Ouvre le menu de ton navigateur (⋮) puis <b>« Installer l'application »</b> / <b>« Ajouter à l'écran d'accueil »</b>.
                            </p>
                        )}
                        <button onClick={() => setAide(false)} className="mt-5 w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm">
                            J'ai compris
                        </button>
                    </div>
                </div>
            )}
        </>
    );
}

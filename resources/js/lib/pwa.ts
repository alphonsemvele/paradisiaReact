import { useEffect, useState } from 'react';

// Gestion centralisée de l'installation PWA (« tchat »). Le module s'exécute
// au chargement : il enregistre le service worker et capture l'événement
// d'installation, pour que le bouton / la bannière puissent le proposer
// n'importe où sur le site.

let deferred: any = null;
let installe = false;
const abonnes = new Set<() => void>();
const prevenir = () => abonnes.forEach((s) => s());

function estStandalone(): boolean {
    if (typeof window === 'undefined') return false;
    return window.matchMedia?.('(display-mode: standalone)').matches || (navigator as any).standalone === true;
}

export function estIOS(): boolean {
    if (typeof navigator === 'undefined') return false;
    return /iphone|ipad|ipod/i.test(navigator.userAgent) && !(navigator as any).standalone;
}

if (typeof window !== 'undefined') {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }
    window.addEventListener('beforeinstallprompt', (e: any) => {
        e.preventDefault();
        deferred = e;
        prevenir();
    });
    window.addEventListener('appinstalled', () => {
        installe = true;
        deferred = null;
        prevenir();
    });
    installe = estStandalone();
}

/** Déclenche l'installation. Retourne le résultat (ou 'ios' / 'manuel' si pas de prompt natif). */
export async function installerPwa(): Promise<'accepted' | 'dismissed' | 'ios' | 'manuel'> {
    if (deferred) {
        deferred.prompt();
        const { outcome } = await deferred.userChoice;
        if (outcome === 'accepted') installe = true;
        deferred = null;
        prevenir();
        return outcome;
    }
    return estIOS() ? 'ios' : 'manuel';
}

/** Hook React : état d'installation réactif. */
export function usePwaInstall() {
    const [, forcer] = useState(0);
    useEffect(() => {
        const s = () => forcer((n) => n + 1);
        abonnes.add(s);
        return () => { abonnes.delete(s); };
    }, []);

    return {
        installe: installe || estStandalone(),
        peutProposer: !!deferred || estIOS(), // prompt natif dispo OU iOS (aide manuelle)
        iOS: estIOS(),
        installer: installerPwa,
    };
}

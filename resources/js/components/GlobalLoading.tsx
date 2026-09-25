import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';

/**
 * Overlay « traitement en cours » global : s'affiche pendant toute soumission
 * (POST/PUT/PATCH/DELETE — création de compte, inscription, paiement Inertia…)
 * pour signaler à l'utilisateur qu'une procédure est en attente. Les simples
 * navigations (GET) gardent la barre fine du haut, sans voile bloquant.
 */
export default function GlobalLoading() {
    const [visible, setVisible] = useState(false);
    const [percent, setPercent] = useState<number | null>(null);

    useEffect(() => {
        let timer: number | undefined;
        let actif = false;

        const offStart = router.on('start', (e: any) => {
            const methode = String(e?.detail?.visit?.method ?? 'get').toLowerCase();
            if (methode === 'get') return; // navigation : la barre du haut suffit
            actif = true;
            setPercent(null);
            // Petit délai : rien ne clignote pour une action quasi instantanée.
            timer = window.setTimeout(() => { if (actif) setVisible(true); }, 300);
        });

        const offProgress = router.on('progress', (e: any) => {
            const p = e?.detail?.progress?.percentage;
            if (typeof p === 'number') setPercent(Math.round(p));
        });

        const terminer = () => {
            actif = false;
            if (timer) window.clearTimeout(timer);
            setVisible(false);
            setPercent(null);
        };
        const offFinish = router.on('finish', terminer);

        return () => { offStart(); offProgress(); offFinish(); if (timer) window.clearTimeout(timer); };
    }, []);

    if (!visible) return null;

    return (
        <div style={{ position: 'fixed', inset: 0, zIndex: 99999, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 24, background: 'rgba(6,40,26,.45)', backdropFilter: 'blur(2px)' }} role="dialog" aria-modal="true" aria-live="polite">
            <div style={{ width: '100%', maxWidth: 320, background: '#fff', borderRadius: 16, padding: '22px 22px 20px', boxShadow: '0 20px 50px rgba(0,0,0,.3)', textAlign: 'center', fontFamily: 'inherit' }}>
                <div style={{ width: 44, height: 44, margin: '0 auto 12px', border: '3px solid #d1fae5', borderTopColor: '#10b981', borderRadius: '50%', animation: 'glSpin .8s linear infinite' }} />
                <p style={{ fontWeight: 800, color: '#18211b', fontSize: 15, margin: 0 }}>Traitement en cours…</p>
                <p style={{ color: '#6b7280', fontSize: 13, marginTop: 4 }}>Une procédure est en attente, merci de patienter.</p>
                <div style={{ marginTop: 14, height: 8, borderRadius: 999, background: '#eef2f0', overflow: 'hidden' }}>
                    {percent === null ? (
                        <div style={{ height: '100%', width: '40%', borderRadius: 999, background: '#10b981', animation: 'glSlide 1.1s ease-in-out infinite' }} />
                    ) : (
                        <div style={{ height: '100%', width: `${percent}%`, borderRadius: 999, background: '#10b981', transition: 'width .2s' }} />
                    )}
                </div>
                {percent !== null && <p style={{ marginTop: 6, fontSize: 11, color: '#9ca3af', margin: '6px 0 0' }}>{percent}%</p>}
            </div>
            <style>{`@keyframes glSpin{to{transform:rotate(360deg)}}@keyframes glSlide{0%{margin-left:-40%}100%{margin-left:100%}}`}</style>
        </div>
    );
}

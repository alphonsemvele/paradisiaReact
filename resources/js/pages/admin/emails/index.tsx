import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Mail, Send, Trash2, Users, CheckCircle2, AlertTriangle } from 'lucide-react';

interface Campagne {
    id: number;
    sujet: string;
    total: number;
    envoyes: number;
    echecs: number;
    restant: number;
    statut: string;
    progression: number;
    date: string;
}

interface Props {
    campagnes: Campagne[];
    nb_destinataires: number;
}

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
const post = (url: string, body?: unknown) =>
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: body ? JSON.stringify(body) : undefined,
    }).then((r) => r.json());

export default function EmailsIndex({ campagnes, nb_destinataires }: Props) {
    const [sujet, setSujet] = useState('');
    const [contenu, setContenu] = useState('');
    const [enCours, setEnCours] = useState<Campagne | null>(null);
    const [erreur, setErreur] = useState('');

    // Envoie les lots en boucle jusqu'à épuisement (progression visible).
    const boucleEnvoi = async (id: number) => {
        while (true) {
            const d = await post(`/admin/emails/${id}/lot`);
            if (!d.ok) break;
            setEnCours(d.campagne);
            if (d.termine) break;
        }
        router.reload({ only: ['campagnes'] });
    };

    const lancer = async () => {
        setErreur('');
        if (!sujet.trim() || !contenu.trim()) { setErreur('Sujet et message requis.'); return; }
        if (!confirm(`Envoyer cet e-mail à ${nb_destinataires} utilisateur(s) ?`)) return;

        const d = await post('/admin/emails', { sujet, contenu });
        if (!d.ok) { setErreur(d.message ?? 'Erreur'); return; }
        setSujet(''); setContenu('');
        setEnCours(d.campagne);
        boucleEnvoi(d.campagne.id);
    };

    const carte: React.CSSProperties = { background: '#fff', borderRadius: 16, border: '1.5px solid #e8f0eb', boxShadow: '0 2px 12px rgba(20,83,45,.06)' };

    return (
        <AdminLayout title="E-mailing">
            <Head title="E-mailing" />
            <div style={{ display: 'grid', gap: 20, maxWidth: 780, margin: '0 auto' }}>

                {/* Composer */}
                <div style={carte}>
                    <div style={{ padding: '16px 20px', borderBottom: '1px solid #f0f7f2', display: 'flex', alignItems: 'center', gap: 10 }}>
                        <Mail size={18} color="#059669" />
                        <div>
                            <p style={{ fontWeight: 800, color: '#14532d', margin: 0 }}>Nouvel e-mail à tous les utilisateurs</p>
                            <p style={{ fontSize: 12, color: '#9db8a4', margin: '2px 0 0', display: 'flex', alignItems: 'center', gap: 4 }}>
                                <Users size={13} /> {new Intl.NumberFormat('fr-FR').format(nb_destinataires)} destinataire(s)
                            </p>
                        </div>
                    </div>
                    <div style={{ padding: 20, display: 'grid', gap: 14 }}>
                        <input value={sujet} onChange={(e) => setSujet(e.target.value)} placeholder="Sujet de l'e-mail"
                            style={{ width: '100%', padding: '11px 13px', border: '1px solid #d7e5dc', borderRadius: 10, fontSize: 14 }} />
                        <textarea value={contenu} onChange={(e) => setContenu(e.target.value)} rows={9}
                            placeholder="Votre message… (les sauts de ligne sont conservés)"
                            style={{ width: '100%', padding: '11px 13px', border: '1px solid #d7e5dc', borderRadius: 10, fontSize: 14, resize: 'vertical', fontFamily: 'inherit' }} />
                        {erreur && <p style={{ color: '#dc2626', fontSize: 13, margin: 0 }}>{erreur}</p>}
                        <button onClick={lancer} disabled={!!enCours && enCours.restant > 0}
                            style={{ padding: '12px', borderRadius: 10, border: 0, background: '#14532d', color: '#fff', fontWeight: 700, fontSize: 14, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8 }}>
                            <Send size={16} /> Envoyer à tous
                        </button>
                    </div>
                </div>

                {/* Progression de l'envoi en cours */}
                {enCours && enCours.restant > 0 && (
                    <div style={{ ...carte, padding: 20 }}>
                        <p style={{ fontWeight: 700, color: '#14532d', margin: '0 0 8px' }}>Envoi en cours — ne fermez pas cette page</p>
                        <div style={{ height: 12, background: '#eef3f0', borderRadius: 8, overflow: 'hidden' }}>
                            <div style={{ height: '100%', width: `${enCours.progression}%`, background: 'linear-gradient(90deg,#059669,#0d9488)', transition: 'width .3s' }} />
                        </div>
                        <p style={{ fontSize: 13, color: '#5b7566', margin: '8px 0 0' }}>
                            {enCours.envoyes} envoyé(s) · {enCours.restant} restant(s){enCours.echecs > 0 && ` · ${enCours.echecs} échec(s)`} ({enCours.progression}%)
                        </p>
                    </div>
                )}

                {/* Historique */}
                <div style={carte}>
                    <div style={{ padding: '16px 20px', borderBottom: '1px solid #f0f7f2' }}>
                        <p style={{ fontWeight: 800, color: '#14532d', margin: 0 }}>Campagnes ({campagnes.length})</p>
                    </div>
                    {campagnes.length === 0 ? (
                        <p style={{ padding: 28, textAlign: 'center', color: '#9db8a4', fontSize: 14 }}>Aucune campagne pour l'instant.</p>
                    ) : campagnes.map((c) => (
                        <div key={c.id} style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '14px 20px', borderBottom: '1px solid #f4faf6' }}>
                            <div style={{ flex: 1, minWidth: 0 }}>
                                <p style={{ fontWeight: 700, color: '#0d1f12', margin: 0 }}>{c.sujet}</p>
                                <p style={{ fontSize: 12, color: '#9db8a4', margin: '2px 0 0', display: 'flex', alignItems: 'center', gap: 6 }}>
                                    {c.statut === 'termine'
                                        ? <><CheckCircle2 size={13} color="#059669" /> {c.envoyes} envoyé(s)</>
                                        : `${c.progression}% — ${c.envoyes}/${c.total}`}
                                    {c.echecs > 0 && <><AlertTriangle size={13} color="#f59e0b" /> {c.echecs}</>}
                                    · {c.date}
                                </p>
                            </div>
                            <button onClick={() => confirm('Supprimer cette campagne ?') && router.delete(`/admin/emails/${c.id}`)}
                                style={{ padding: 8, borderRadius: 8, border: '1px solid #fecaca', background: '#fff', color: '#dc2626', cursor: 'pointer' }}>
                                <Trash2 size={15} />
                            </button>
                        </div>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}

import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Trophy, Download, Heart, MessageCircle, Calendar, Medal } from 'lucide-react';

interface Ligne {
    rang: number;
    nom: string;
    email: string | null;
    publications: number;
    likes: number;
    commentaires: number;
    total: number;
    user_id: number;
    qualifie: boolean;
}

interface Props {
    classement: Ligne[];
    debut: string;
    fin: string;
    debut_label: string;
    fin_label: string;
}

const medaille = (r: number) => (r === 1 ? '🥇' : r === 2 ? '🥈' : r === 3 ? '🥉' : `${r}`);

export default function ConcoursIndex({ classement, debut, fin, debut_label, fin_label }: Props) {
    // Fenêtre ajustable : par défaut Phase 2 (6 août → maintenant).
    const [d, setD] = useState(debut.slice(0, 16));
    const [f, setF] = useState(fin.slice(0, 16));

    const appliquer = () => router.get('/admin/concours', { debut: d, fin: f }, { preserveState: false });
    const exportUrl = `/admin/concours/export?debut=${encodeURIComponent(d)}&fin=${encodeURIComponent(f)}`;

    const carte: React.CSSProperties = { background: '#fff', borderRadius: 16, border: '1.5px solid #e8f0eb', boxShadow: '0 2px 12px rgba(20,83,45,.06)' };

    return (
        <AdminLayout title="Résultats du concours">
            <Head title="Résultats du concours" />
            <div style={{ display: 'grid', gap: 18 }}>

                {/* En-tête + export */}
                <div style={{ ...carte, padding: 20, display: 'flex', alignItems: 'center', gap: 14, flexWrap: 'wrap' }}>
                    <div style={{ width: 46, height: 46, borderRadius: 12, background: '#fef9c3', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <Trophy size={24} color="#ca8a04" />
                    </div>
                    <div style={{ flex: 1, minWidth: 200 }}>
                        <p style={{ fontWeight: 800, color: '#14532d', margin: 0, fontSize: 17 }}>Jeu Concours PARADISIA</p>
                        <p style={{ fontSize: 12, color: '#9db8a4', margin: '2px 0 0', display: 'flex', alignItems: 'center', gap: 4 }}>
                            <Calendar size={13} /> {debut_label} → {fin_label}
                        </p>
                    </div>
                    <a href={exportUrl}
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '11px 18px', borderRadius: 10, background: '#2563eb', color: '#fff', fontWeight: 700, fontSize: 14, textDecoration: 'none' }}>
                        <Download size={16} /> Télécharger (Word)
                    </a>
                </div>

                {/* Fenêtre ajustable */}
                <div style={{ ...carte, padding: 16, display: 'flex', gap: 12, alignItems: 'end', flexWrap: 'wrap' }}>
                    <div>
                        <label style={{ fontSize: 11, fontWeight: 700, color: '#4b6355', display: 'block', marginBottom: 4 }}>Début</label>
                        <input type="datetime-local" value={d} onChange={(e) => setD(e.target.value)}
                            style={{ padding: '9px 11px', border: '1px solid #d7e5dc', borderRadius: 9, fontSize: 13 }} />
                    </div>
                    <div>
                        <label style={{ fontSize: 11, fontWeight: 700, color: '#4b6355', display: 'block', marginBottom: 4 }}>Fin (samedi 12h)</label>
                        <input type="datetime-local" value={f} onChange={(e) => setF(e.target.value)}
                            style={{ padding: '9px 11px', border: '1px solid #d7e5dc', borderRadius: 9, fontSize: 13 }} />
                    </div>
                    <button onClick={appliquer}
                        style={{ padding: '10px 18px', borderRadius: 9, border: 0, background: '#14532d', color: '#fff', fontWeight: 700, fontSize: 13, cursor: 'pointer' }}>
                        Recalculer
                    </button>
                </div>

                {/* Classement */}
                <div style={carte}>
                    <div style={{ padding: '14px 20px', borderBottom: '1px solid #f0f7f2' }}>
                        <p style={{ fontWeight: 800, color: '#14532d', margin: 0 }}>Classement ({classement.length} participant{classement.length > 1 ? 's' : ''})</p>
                        <p style={{ fontSize: 12, color: '#9db8a4', margin: '2px 0 0' }}>1 point par personne qui like + 1 point par personne qui commente (une personne = 1 pt max/publi, auto-interactions exclues)</p>
                    </div>

                    {classement.length === 0 ? (
                        <p style={{ padding: 30, textAlign: 'center', color: '#9db8a4' }}>Aucune publication sur la période.</p>
                    ) : (
                        <div style={{ overflowX: 'auto' }}>
                            <table style={{ width: '100%', borderCollapse: 'collapse', minWidth: 560 }}>
                                <thead>
                                    <tr style={{ background: '#f4faf6' }}>
                                        {['Rang', 'Participant', 'Pub.', 'Likes', 'Comm.', 'Total'].map((h, i) => (
                                            <th key={h} style={{ textAlign: i > 1 ? 'right' : 'left', padding: '10px 14px', fontSize: 11, textTransform: 'uppercase', color: '#5b7566' }}>{h}</th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {classement.map((l) => (
                                        <tr key={l.user_id ?? l.rang} style={{ background: l.qualifie ? '#eafaf1' : '#fff', borderBottom: '1px solid #f4faf6' }}>
                                            <td style={{ padding: '11px 14px', fontWeight: 700, fontSize: 15 }}>{medaille(l.rang)}</td>
                                            <td style={{ padding: '11px 14px' }}>
                                                <div style={{ fontWeight: 700, color: '#0d1f12' }}>{l.nom}{l.qualifie && <Medal size={14} color="#059669" style={{ marginLeft: 6, verticalAlign: 'middle' }} />}</div>
                                                {l.email && <div style={{ fontSize: 11, color: '#9db8a4' }}>{l.email}</div>}
                                            </td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', color: '#5b7566' }}>{l.publications}</td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right' }}><span style={{ display: 'inline-flex', alignItems: 'center', gap: 3, color: '#dc2626' }}><Heart size={13} />{l.likes}</span></td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right' }}><span style={{ display: 'inline-flex', alignItems: 'center', gap: 3, color: '#059669' }}><MessageCircle size={13} />{l.commentaires}</span></td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontWeight: 800, color: '#14532d', fontSize: 15 }}>{l.total}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                    {classement.length > 4 && (
                        <p style={{ padding: '10px 20px', fontSize: 12, color: '#059669', margin: 0, background: '#f4faf6' }}>
                            🏅 Les 4 premiers (surlignés) sont qualifiés pour le tour suivant.
                        </p>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}

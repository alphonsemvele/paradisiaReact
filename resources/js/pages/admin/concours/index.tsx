import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Trophy, Download, Heart, MessageCircle, Calendar, Medal, Eye, X, ExternalLink } from 'lucide-react';

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

    // Détail d'un participant (modal).
    const [detail, setDetail] = useState<any>(null);
    const [chargement, setChargement] = useState(false);

    const voirDetails = async (userId: number) => {
        setChargement(true);
        setDetail({});
        const url = `/admin/concours/participant/${userId}?debut=${encodeURIComponent(d)}&fin=${encodeURIComponent(f)}`;
        const r = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        setDetail(await r.json());
        setChargement(false);
    };

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
                                        {['Rang', 'Participant', 'Pub.', 'Likes', 'Comm.', 'Total', ''].map((h, i) => (
                                            <th key={i} style={{ textAlign: i > 1 && i < 6 ? 'right' : 'left', padding: '10px 14px', fontSize: 11, textTransform: 'uppercase', color: '#5b7566' }}>{h}</th>
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
                                            <td style={{ padding: '11px 14px', textAlign: 'right' }}>
                                                <button onClick={() => voirDetails(l.user_id)}
                                                    style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '6px 12px', borderRadius: 8, border: '1px solid #d7e5dc', background: '#fff', color: '#14532d', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>
                                                    <Eye size={13} /> Détails
                                                </button>
                                            </td>
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

            {/* Modal détail d'un participant */}
            {detail && (
                <div onClick={() => setDetail(null)}
                    style={{ position: 'fixed', inset: 0, zIndex: 60, background: 'rgba(0,0,0,.55)', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16 }}>
                    <div onClick={(e) => e.stopPropagation()}
                        style={{ background: '#fff', borderRadius: 18, width: '100%', maxWidth: 640, maxHeight: '88vh', overflow: 'auto', boxShadow: '0 12px 48px rgba(0,0,0,.25)' }}>
                        <div style={{ position: 'sticky', top: 0, background: '#fff', padding: '16px 20px', borderBottom: '1px solid #f0f7f2', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                            <div>
                                <p style={{ fontWeight: 800, color: '#14532d', margin: 0 }}>{chargement ? 'Chargement…' : detail.nom}</p>
                                {detail.email && <p style={{ fontSize: 12, color: '#9db8a4', margin: '2px 0 0' }}>{detail.email}</p>}
                            </div>
                            <button onClick={() => setDetail(null)} style={{ border: 0, background: '#f4faf6', borderRadius: 20, padding: 8, cursor: 'pointer' }}><X size={18} color="#5b7566" /></button>
                        </div>

                        <div style={{ padding: 18 }}>
                            {chargement ? (
                                <p style={{ textAlign: 'center', color: '#9db8a4', padding: 20 }}>…</p>
                            ) : (detail.publications ?? []).length === 0 ? (
                                <p style={{ textAlign: 'center', color: '#9db8a4', padding: 20 }}>Aucune publication sur la période.</p>
                            ) : (
                                detail.publications.map((p: any) => (
                                    <div key={p.id} style={{ border: '1px solid #e8f0eb', borderRadius: 12, padding: 14, marginBottom: 12 }}>
                                        <div style={{ display: 'flex', gap: 12 }}>
                                            {p.image && <img src={p.image} alt="" style={{ width: 60, height: 60, borderRadius: 8, objectFit: 'cover', flexShrink: 0 }} />}
                                            <div style={{ flex: 1, minWidth: 0 }}>
                                                <p style={{ margin: 0, fontSize: 14, color: '#1a2b20' }}>{p.texte}</p>
                                                <p style={{ margin: '4px 0 0', fontSize: 11, color: '#9db8a4' }}>{p.date}</p>
                                            </div>
                                            <a href={p.lien} target="_blank" rel="noopener noreferrer" style={{ color: '#2563eb', flexShrink: 0 }}><ExternalLink size={16} /></a>
                                        </div>
                                        <div style={{ display: 'flex', gap: 16, marginTop: 10, fontSize: 13, fontWeight: 700 }}>
                                            <span style={{ color: '#dc2626', display: 'inline-flex', alignItems: 'center', gap: 4 }}><Heart size={14} /> {p.likers.length} like(s)</span>
                                            <span style={{ color: '#059669', display: 'inline-flex', alignItems: 'center', gap: 4 }}><MessageCircle size={14} /> {p.commenters.length} personne(s)</span>
                                            <span style={{ marginLeft: 'auto', color: '#14532d' }}>= {p.points} pts</span>
                                        </div>

                                        {p.likers.length > 0 && (
                                            <p style={{ margin: '8px 0 0', fontSize: 12, color: '#5b7566' }}>
                                                <strong>Ont aimé :</strong> {p.likers.join(', ')}
                                            </p>
                                        )}
                                        {p.commenters.length > 0 && (
                                            <p style={{ margin: '6px 0 0', fontSize: 12, color: '#5b7566' }}>
                                                <strong>Ont commenté :</strong>{' '}
                                                {p.commenters.map((c: any) => `${c.nom}${c.nb > 1 ? ` (${c.nb} comm. = 1 pt)` : ''}`).join(', ')}
                                            </p>
                                        )}
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}

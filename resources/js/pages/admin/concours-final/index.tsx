import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Trophy, Download, Heart, MessageCircle, Calendar, Medal, Eye, X, ExternalLink, Check, ChevronDown, Sparkles, ListChecks } from 'lucide-react';

interface Ligne {
    rang: number;
    nom: string;
    email: string | null;
    publications: number;
    reponses_justes: number;
    reponses_auto: number;
    note_manuelle: boolean;
    points_reponses: number;
    likes: number;
    commentaires: number;
    total: number;
    user_id: number;
    qualifie: boolean;
}
interface Corr { q: string; r: string }
interface Props {
    classement: Ligne[];
    corrige: Corr[];
    debut: string;
    fin: string;
    debut_label: string;
    fin_label: string;
}

const medaille = (r: number) => (r === 1 ? '🥇' : r === 2 ? '🥈' : r === 3 ? '🥉' : `${r}`);

export default function ConcoursFinal({ classement, corrige, debut, fin, debut_label, fin_label }: Props) {
    const [d, setD] = useState(debut.slice(0, 16));
    const [f, setF] = useState(fin.slice(0, 16));
    const [notes, setNotes] = useState<Record<number, number>>(
        Object.fromEntries(classement.map((l) => [l.user_id, l.reponses_justes])),
    );
    const [detail, setDetail] = useState<any>(null);
    const [chargement, setChargement] = useState(false);
    const [corrOuvert, setCorrOuvert] = useState(false);
    const [corr, setCorr] = useState<Corr[]>(corrige ?? []);

    const enregistrerCorrige = () => {
        router.post('/admin/concours-final/corrige', { corrige: corr, debut: d, fin: f } as any, { preserveScroll: true });
    };

    const appliquer = () => router.get('/admin/concours-final', { debut: d, fin: f }, { preserveState: false });
    const exportUrl = `/admin/concours-final/export?debut=${encodeURIComponent(d)}&fin=${encodeURIComponent(f)}`;

    const enregistrer = (userId: number) => {
        const v = Math.max(0, Math.min(10, Number(notes[userId] ?? 0)));
        router.post('/admin/concours-final/score', { user_id: userId, reponses_justes: v, debut: d, fin: f }, {
            preserveScroll: true,
        });
    };

    const voirDetails = async (userId: number) => {
        setChargement(true);
        setDetail({});
        const url = `/admin/concours-final/participant/${userId}?debut=${encodeURIComponent(d)}&fin=${encodeURIComponent(f)}`;
        const r = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        setDetail(await r.json());
        setChargement(false);
    };

    const carte: React.CSSProperties = { background: '#fff', borderRadius: 16, border: '1.5px solid #e8f0eb', boxShadow: '0 2px 12px rgba(20,83,45,.06)' };

    return (
        <AdminLayout title="Résultats — Dernière phase">
            <Head title="Résultats — Dernière phase" />
            <div style={{ display: 'grid', gap: 18 }}>

                <div style={{ ...carte, padding: 20, display: 'flex', alignItems: 'center', gap: 14, flexWrap: 'wrap' }}>
                    <div style={{ width: 46, height: 46, borderRadius: 12, background: '#fef9c3', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <Trophy size={24} color="#ca8a04" />
                    </div>
                    <div style={{ flex: 1, minWidth: 200 }}>
                        <p style={{ fontWeight: 800, color: '#14532d', margin: 0, fontSize: 17 }}>Jeu Concours — Dernière phase</p>
                        <p style={{ fontSize: 12, color: '#9db8a4', margin: '2px 0 0', display: 'flex', alignItems: 'center', gap: 4 }}>
                            <Calendar size={13} /> {debut_label} → {fin_label}
                        </p>
                    </div>
                    <a href={exportUrl}
                        style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '11px 18px', borderRadius: 10, background: '#2563eb', color: '#fff', fontWeight: 700, fontSize: 14, textDecoration: 'none' }}>
                        <Download size={16} /> Télécharger (Word)
                    </a>
                </div>

                <div style={{ ...carte, padding: 16, display: 'flex', gap: 12, alignItems: 'end', flexWrap: 'wrap' }}>
                    <div>
                        <label style={{ fontSize: 11, fontWeight: 700, color: '#4b6355', display: 'block', marginBottom: 4 }}>Début</label>
                        <input type="datetime-local" value={d} onChange={(e) => setD(e.target.value)}
                            style={{ padding: '9px 11px', border: '1px solid #d7e5dc', borderRadius: 9, fontSize: 13 }} />
                    </div>
                    <div>
                        <label style={{ fontSize: 11, fontWeight: 700, color: '#4b6355', display: 'block', marginBottom: 4 }}>Fin</label>
                        <input type="datetime-local" value={f} onChange={(e) => setF(e.target.value)}
                            style={{ padding: '9px 11px', border: '1px solid #d7e5dc', borderRadius: 9, fontSize: 13 }} />
                    </div>
                    <button onClick={appliquer}
                        style={{ padding: '10px 18px', borderRadius: 9, border: 0, background: '#14532d', color: '#fff', fontWeight: 700, fontSize: 13, cursor: 'pointer' }}>
                        Recalculer
                    </button>
                </div>

                <div style={{ background: '#fff7ed', border: '1px solid #fed7aa', borderRadius: 12, padding: '12px 16px', fontSize: 13, color: '#9a3412' }}>
                    <strong>Barème :</strong> 5 pts par réponse juste + 1 pt/like + 1 pt/commentaire unique. Les points quiz sont <strong>notés automatiquement</strong> : le système lit chaque publication et compte les bonnes réponses du corrigé (tu peux corriger à la main). Total et classement recalculés en direct.
                </div>

                {/* Corrigé du quiz (référence pour la notation auto) */}
                <div style={carte}>
                    <button onClick={() => setCorrOuvert((v) => !v)}
                        style={{ width: '100%', padding: '14px 20px', display: 'flex', alignItems: 'center', gap: 10, background: 'none', border: 0, cursor: 'pointer' }}>
                        <ListChecks size={18} color="#0d7a4f" />
                        <span style={{ fontWeight: 800, color: '#14532d' }}>Corrigé du quiz (10 réponses)</span>
                        <span style={{ marginLeft: 'auto', color: '#9db8a4', fontSize: 12, display: 'inline-flex', alignItems: 'center', gap: 4 }}>
                            Sert à la note auto <ChevronDown size={16} style={{ transform: corrOuvert ? 'rotate(180deg)' : 'none', transition: '.2s' }} />
                        </span>
                    </button>
                    {corrOuvert && (
                        <div style={{ padding: '0 20px 18px' }}>
                            <p style={{ fontSize: 12, color: '#9db8a4', margin: '0 0 12px' }}>
                                Définis la bonne réponse de chaque question. Le système détecte ces mots dans les publications pour attribuer les 5 pts.
                            </p>
                            {corr.map((c, i) => (
                                <div key={i} style={{ display: 'grid', gridTemplateColumns: '1fr 200px', gap: 10, marginBottom: 8, alignItems: 'center' }}>
                                    <div style={{ fontSize: 13, color: '#334155' }}><strong style={{ color: '#0d7a4f' }}>{i + 1}.</strong> {c.q}</div>
                                    <input value={c.r} onChange={(e) => setCorr((arr) => arr.map((x, j) => (j === i ? { ...x, r: e.target.value } : x)))}
                                        placeholder="Bonne réponse"
                                        style={{ padding: '8px 10px', border: '1px solid #d7e5dc', borderRadius: 8, fontSize: 13, fontWeight: 600, color: '#14532d' }} />
                                </div>
                            ))}
                            <button onClick={enregistrerCorrige}
                                style={{ marginTop: 6, padding: '9px 16px', borderRadius: 9, border: 0, background: '#0d7a4f', color: '#fff', fontWeight: 700, fontSize: 13, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 6 }}>
                                <Check size={15} /> Enregistrer le corrigé et recalculer
                            </button>
                        </div>
                    )}
                </div>

                <div style={carte}>
                    <div style={{ padding: '14px 20px', borderBottom: '1px solid #f0f7f2' }}>
                        <p style={{ fontWeight: 800, color: '#14532d', margin: 0 }}>Classement ({classement.length} participant{classement.length > 1 ? 's' : ''})</p>
                    </div>

                    {classement.length === 0 ? (
                        <p style={{ padding: 30, textAlign: 'center', color: '#9db8a4' }}>Aucune publication sur la période.</p>
                    ) : (
                        <div style={{ overflowX: 'auto' }}>
                            <table style={{ width: '100%', borderCollapse: 'collapse', minWidth: 720 }}>
                                <thead>
                                    <tr style={{ background: '#f4faf6' }}>
                                        {['Rang', 'Participant', 'Réponses /10', 'Pts rép.', 'Likes', 'Comm.', 'TOTAL', ''].map((h, i) => (
                                            <th key={i} style={{ textAlign: i > 1 && i < 7 ? 'right' : 'left', padding: '10px 14px', fontSize: 11, textTransform: 'uppercase', color: '#5b7566' }}>{h}</th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {classement.map((l) => (
                                        <tr key={l.user_id} style={{ background: l.qualifie ? '#eafaf1' : '#fff', borderBottom: '1px solid #f4faf6' }}>
                                            <td style={{ padding: '11px 14px', fontWeight: 700, fontSize: 15 }}>{medaille(l.rang)}</td>
                                            <td style={{ padding: '11px 14px' }}>
                                                <div style={{ fontWeight: 700, color: '#0d1f12' }}>{l.nom}{l.qualifie && <Medal size={14} color="#059669" style={{ marginLeft: 6, verticalAlign: 'middle' }} />}</div>
                                                {l.email && <div style={{ fontSize: 11, color: '#9db8a4' }}>{l.email}</div>}
                                            </td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right' }}>
                                                <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6, justifyContent: 'flex-end' }}>
                                                    <input type="number" min={0} max={10} value={notes[l.user_id] ?? 0}
                                                        onChange={(e) => setNotes((n) => ({ ...n, [l.user_id]: Number(e.target.value) }))}
                                                        onKeyDown={(e) => e.key === 'Enter' && enregistrer(l.user_id)}
                                                        style={{ width: 56, padding: '6px 8px', border: '1px solid #d7e5dc', borderRadius: 8, fontSize: 13, textAlign: 'center' }} />
                                                    <button onClick={() => enregistrer(l.user_id)} title="Enregistrer la note manuelle"
                                                        style={{ border: 0, background: '#dcfce7', color: '#166534', borderRadius: 8, padding: 6, cursor: 'pointer', display: 'inline-flex' }}>
                                                        <Check size={14} />
                                                    </button>
                                                </span>
                                                <div style={{ marginTop: 3, fontSize: 10.5 }}>
                                                    {l.note_manuelle ? (
                                                        <span style={{ color: '#a16207', fontWeight: 700 }}>corrigé à la main</span>
                                                    ) : (
                                                        <span style={{ color: '#0d7a4f', display: 'inline-flex', alignItems: 'center', gap: 2, justifyContent: 'flex-end', width: '100%' }}>
                                                            <Sparkles size={11} /> auto : {l.reponses_auto}/10
                                                        </span>
                                                    )}
                                                </div>
                                            </td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', color: '#ca8a04', fontWeight: 700 }}>{l.points_reponses}</td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right' }}><span style={{ display: 'inline-flex', alignItems: 'center', gap: 3, color: '#dc2626' }}><Heart size={13} />{l.likes}</span></td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right' }}><span style={{ display: 'inline-flex', alignItems: 'center', gap: 3, color: '#059669' }}><MessageCircle size={13} />{l.commentaires}</span></td>
                                            <td style={{ padding: '11px 14px', textAlign: 'right', fontWeight: 800, color: '#14532d', fontSize: 16 }}>{l.total}</td>
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
                    {classement.length > 6 && (
                        <p style={{ padding: '10px 20px', fontSize: 12, color: '#059669', margin: 0, background: '#f4faf6' }}>
                            🏆 Les 6 premiers (surlignés) sont les finalistes.
                        </p>
                    )}
                </div>
            </div>

            {/* Modal détail (texte complet pour corriger les réponses) */}
            {detail && (
                <div onClick={() => setDetail(null)}
                    style={{ position: 'fixed', inset: 0, zIndex: 60, background: 'rgba(0,0,0,.55)', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16 }}>
                    <div onClick={(e) => e.stopPropagation()}
                        style={{ background: '#fff', borderRadius: 18, width: '100%', maxWidth: 660, maxHeight: '88vh', overflow: 'auto', boxShadow: '0 12px 48px rgba(0,0,0,.25)' }}>
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
                                                <p style={{ margin: 0, fontSize: 14, color: '#1a2b20', whiteSpace: 'pre-wrap' }}>{p.texte}</p>
                                                <p style={{ margin: '4px 0 0', fontSize: 11, color: '#9db8a4' }}>{p.date}</p>
                                            </div>
                                            <a href={p.lien} target="_blank" rel="noopener noreferrer" style={{ color: '#2563eb', flexShrink: 0 }}><ExternalLink size={16} /></a>
                                        </div>
                                        <div style={{ display: 'flex', gap: 16, marginTop: 10, fontSize: 13, fontWeight: 700 }}>
                                            <span style={{ color: '#dc2626', display: 'inline-flex', alignItems: 'center', gap: 4 }}><Heart size={14} /> {p.likers.length} like(s)</span>
                                            <span style={{ color: '#059669', display: 'inline-flex', alignItems: 'center', gap: 4 }}><MessageCircle size={14} /> {p.commenters.length} personne(s)</span>
                                        </div>
                                        {p.likers.length > 0 && (
                                            <p style={{ margin: '8px 0 0', fontSize: 12, color: '#5b7566' }}><strong>Ont aimé :</strong> {p.likers.join(', ')}</p>
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

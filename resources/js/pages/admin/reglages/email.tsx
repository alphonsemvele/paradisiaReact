import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { Mail, Server, ShieldCheck, Send, Save, Info } from 'lucide-react';

interface Reglages {
    actif: boolean;
    mailer: string;
    host: string | null;
    port: number | null;
    username: string | null;
    a_mot_de_passe: boolean;
    encryption: string | null;
    from_address: string | null;
    from_name: string | null;
}
interface Props {
    reglages: Reglages;
    actuel: { transport: string; from: string | null; host: string | null };
}

export default function ReglagesEmail({ reglages, actuel }: Props) {
    const page = usePage().props as any;
    const flash = page.flash?.success as string | undefined;
    const erreurs = page.errors as Record<string, string>;

    const [f, setF] = useState({
        actif: reglages.actif,
        mailer: reglages.mailer || 'smtp',
        host: reglages.host ?? 'mail.paradisia-africa.com',
        port: reglages.port ?? 465,
        username: reglages.username ?? 'no-reply@paradisia-africa.com',
        password: '',
        encryption: reglages.encryption ?? 'ssl',
        from_address: reglages.from_address ?? 'no-reply@paradisia-africa.com',
        from_name: reglages.from_name ?? 'Paradisia',
    });
    const [testEmail, setTestEmail] = useState('');
    const [busy, setBusy] = useState(false);

    const enregistrer = () => {
        setBusy(true);
        router.post('/admin/reglages/email', { ...f, _method: 'POST' }, { preserveScroll: true, onFinish: () => setBusy(false) });
    };
    const envoyerTest = () => {
        if (!testEmail.trim()) return;
        router.post('/admin/reglages/email/test', { email: testEmail }, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Réglages e-mail">
            <Head title="Réglages e-mail — Admin" />

            <div className="max-w-2xl mx-auto space-y-5">
                <div className="flex items-center gap-3">
                    <div className="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center"><Mail className="w-6 h-6 text-emerald-600" /></div>
                    <div>
                        <h1 className="text-xl font-bold text-zinc-900">Réglages e-mail (SMTP)</h1>
                        <p className="text-sm text-zinc-500">Envoie les mails via ta boîte pro pour arriver en boîte de réception (fini le spam).</p>
                    </div>
                </div>

                {flash && <div className="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">{flash}</div>}
                {erreurs?.test && <div className="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 text-sm">Échec du test : {erreurs.test}</div>}

                {/* Config active */}
                <div className="rounded-xl bg-zinc-50 border border-zinc-200 p-3 text-xs text-zinc-600 flex items-start gap-2">
                    <Info className="w-4 h-4 mt-0.5 text-zinc-400" />
                    <div>Config actuellement active — transport : <b>{actuel.transport}</b> · expéditeur : <b>{actuel.from || '—'}</b>{actuel.host ? <> · hôte : <b>{actuel.host}</b></> : null}</div>
                </div>

                {/* Formulaire */}
                <div className="bg-white rounded-2xl border border-zinc-200 p-5 space-y-4">
                    <label className="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" checked={f.actif} onChange={(e) => setF({ ...f, actif: e.target.checked })} className="w-4 h-4" />
                        <span className="text-sm font-semibold text-zinc-800">Activer ces réglages SMTP</span>
                        <span className="text-xs text-zinc-400">(sinon, config du serveur utilisée)</span>
                    </label>

                    <div className="grid sm:grid-cols-2 gap-3">
                        <L label="Serveur SMTP (host)" icon={Server}><input className="ipt" value={f.host} onChange={(e) => setF({ ...f, host: e.target.value })} placeholder="mail.paradisia-africa.com" /></L>
                        <L label="Port"><input className="ipt" type="number" value={f.port} onChange={(e) => setF({ ...f, port: Number(e.target.value) })} placeholder="465" /></L>
                    </div>
                    <div className="grid sm:grid-cols-2 gap-3">
                        <L label="Identifiant (adresse complète)"><input className="ipt" value={f.username} onChange={(e) => setF({ ...f, username: e.target.value })} placeholder="no-reply@paradisia-africa.com" /></L>
                        <L label="Chiffrement"><select className="ipt" value={f.encryption} onChange={(e) => setF({ ...f, encryption: e.target.value })}><option value="ssl">SSL (port 465)</option><option value="tls">TLS (port 587)</option></select></L>
                    </div>
                    <L label={reglages.a_mot_de_passe ? 'Mot de passe (laisser vide pour garder l\'actuel)' : 'Mot de passe de la boîte'} icon={ShieldCheck}>
                        <input className="ipt" type="password" value={f.password} onChange={(e) => setF({ ...f, password: e.target.value })} placeholder={reglages.a_mot_de_passe ? '•••••••• (inchangé)' : 'Mot de passe'} />
                    </L>
                    <div className="grid sm:grid-cols-2 gap-3">
                        <L label="Expéditeur (from)"><input className="ipt" value={f.from_address} onChange={(e) => setF({ ...f, from_address: e.target.value })} placeholder="no-reply@paradisia-africa.com" /></L>
                        <L label="Nom expéditeur"><input className="ipt" value={f.from_name} onChange={(e) => setF({ ...f, from_name: e.target.value })} placeholder="Paradisia" /></L>
                    </div>

                    <button onClick={enregistrer} disabled={busy}
                        className="w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm flex items-center justify-center gap-2 disabled:opacity-60">
                        <Save className="w-4 h-4" /> Enregistrer les réglages
                    </button>
                </div>

                {/* Test */}
                <div className="bg-white rounded-2xl border border-zinc-200 p-5">
                    <p className="text-sm font-semibold text-zinc-800 mb-2">Envoyer un e-mail de test</p>
                    <div className="flex gap-2">
                        <input className="ipt flex-1" value={testEmail} onChange={(e) => setTestEmail(e.target.value)} placeholder="ton@email.com" />
                        <button onClick={envoyerTest} className="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-semibold flex items-center gap-1.5">
                            <Send className="w-4 h-4" /> Tester
                        </button>
                    </div>
                    <p className="text-xs text-zinc-400 mt-2">Le test utilise les réglages <b>enregistrés</b>. Enregistre d'abord, puis teste.</p>
                </div>
            </div>

            <style>{`.ipt{width:100%;padding:.6rem .75rem;background:#fafafa;border:1px solid #e4e4e7;border-radius:.6rem;font-size:.9rem}.ipt:focus{outline:none;box-shadow:0 0 0 2px #10b981}`}</style>
        </AdminLayout>
    );
}

function L({ label, icon: Icon, children }: { label: string; icon?: any; children: React.ReactNode }) {
    return (
        <label className="block">
            <span className="flex items-center gap-1.5 text-xs font-medium text-zinc-500 mb-1">{Icon && <Icon className="w-3.5 h-3.5" />}{label}</span>
            {children}
        </label>
    );
}

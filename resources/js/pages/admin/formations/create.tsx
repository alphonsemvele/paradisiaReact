import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import { ArrowLeft, Save, GraduationCap, ImageIcon, X, FileText, MapPin, Monitor, Check } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

export default function FormationCreate() {
    const [preview, setPreview] = useState<string | null>(null);

    const { data, setData, post, processing, errors } = useForm({
        titre: '',
        description: '',
        prix: '',
        duree: '',
        session: '',
        mode: 'presentiel',
        image: null as File | null,
        document: null as File | null,
    });

    const handleImage = (file: File | null) => {
        setData('image', file);
        if (!file) return setPreview(null);
        const reader = new FileReader();
        reader.onload = (e) => setPreview(e.target?.result as string);
        reader.readAsDataURL(file);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/formations', { forceFormData: true });
    };

    return (
        <AdminLayout title="Nouvelle formation">
            <Head title="Admin - Nouvelle formation" />

            <div className="max-w-3xl mx-auto space-y-6">
                <Link href="/admin/formations" className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900">
                    <ArrowLeft className="w-4 h-4" />
                    Retour aux formations
                </Link>

                <motion.form
                    onSubmit={submit}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6 space-y-5"
                >
                    <div className="flex items-center gap-3 pb-4 border-b border-zinc-100">
                        <div className="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <GraduationCap className="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <h2 className="font-semibold text-zinc-900">Créer une formation</h2>
                            <p className="text-xs text-zinc-500">Renseignez les informations ci-dessous</p>
                        </div>
                    </div>

                    <FormFields data={data} setData={setData} errors={errors} preview={preview} onImage={handleImage} />

                    <div className="flex gap-3 pt-4 border-t border-zinc-100">
                        <Link href="/admin/formations" className="flex-1 text-center py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg">
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-300 text-white font-medium rounded-lg"
                        >
                            <Save className="w-4 h-4" />
                            {processing ? 'Création...' : 'Créer la formation'}
                        </button>
                    </div>
                </motion.form>
            </div>
        </AdminLayout>
    );
}

/* ============ Champs partagés (création / édition) ============ */
export function FormFields({
    data,
    setData,
    errors,
    preview,
    onImage,
    existingImage,
    existingDocName,
}: {
    data: any;
    setData: (key: any, value: any) => void;
    errors: Record<string, string>;
    preview: string | null;
    onImage: (file: File | null) => void;
    existingImage?: string | null;
    existingDocName?: string | null;
}) {
    return (
        <>
            {/* Titre */}
            <div>
                <label className="block text-sm font-medium text-zinc-700 mb-1.5">Titre de la formation *</label>
                <input
                    type="text"
                    value={data.titre}
                    onChange={(e) => setData('titre', e.target.value)}
                    placeholder="Ex: Production de jus de fruits naturels"
                    required
                    className={`w-full px-3 py-2.5 bg-zinc-50 border ${errors.titre ? 'border-red-300' : 'border-zinc-200'} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                />
                {errors.titre && <p className="text-xs text-red-600 mt-1">{errors.titre}</p>}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {/* Prix */}
                <div>
                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">Prix (FCFA) *</label>
                    <input
                        type="number"
                        step="1"
                        min="0"
                        value={data.prix}
                        onChange={(e) => setData('prix', e.target.value)}
                        placeholder="50000"
                        required
                        className={`w-full px-3 py-2.5 bg-zinc-50 border ${errors.prix ? 'border-red-300' : 'border-zinc-200'} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                    />
                    {errors.prix && <p className="text-xs text-red-600 mt-1">{errors.prix}</p>}
                </div>

                {/* Durée */}
                <div>
                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">Durée</label>
                    <input
                        type="text"
                        value={data.duree}
                        onChange={(e) => setData('duree', e.target.value)}
                        placeholder="Ex: 3 mois"
                        className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                {/* Session */}
                <div>
                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">Session</label>
                    <input
                        type="text"
                        value={data.session}
                        onChange={(e) => setData('session', e.target.value)}
                        placeholder="Ex: Janvier 2026"
                        className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
            </div>

            {/* Mode : présentiel / en ligne */}
            <div>
                <label className="block text-sm font-medium text-zinc-700 mb-1.5">Type de formation *</label>
                <div className="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        onClick={() => setData('mode', 'presentiel')}
                        className={`relative flex items-center justify-center gap-2 py-3 px-3 rounded-xl border-2 text-sm font-medium transition-all ${
                            data.mode === 'presentiel'
                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300'
                        }`}
                    >
                        {data.mode === 'presentiel' && <Check className="absolute top-2 right-2 w-4 h-4 text-emerald-600" />}
                        <MapPin className="w-5 h-5" />
                        En présentiel
                    </button>
                    <button
                        type="button"
                        onClick={() => setData('mode', 'en_ligne')}
                        className={`relative flex items-center justify-center gap-2 py-3 px-3 rounded-xl border-2 text-sm font-medium transition-all ${
                            data.mode === 'en_ligne'
                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300'
                        }`}
                    >
                        {data.mode === 'en_ligne' && <Check className="absolute top-2 right-2 w-4 h-4 text-blue-600" />}
                        <Monitor className="w-5 h-5" />
                        En ligne
                    </button>
                </div>
                {errors.mode && <p className="text-xs text-red-600 mt-1">{errors.mode}</p>}
            </div>

            {/* Description */}
            <div>
                <label className="block text-sm font-medium text-zinc-700 mb-1.5">Description</label>
                <textarea
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    rows={5}
                    placeholder="Programme, objectifs, prérequis..."
                    className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                />
            </div>

            {/* Image + Document */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {/* Image */}
                <div>
                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">Image</label>
                    {preview || existingImage ? (
                        <div className="relative aspect-video bg-zinc-100 rounded-lg overflow-hidden group">
                            <img src={preview || existingImage!} alt="Aperçu" className="w-full h-full object-cover" />
                            <button
                                type="button"
                                onClick={() => onImage(null)}
                                className="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600"
                            >
                                <X className="w-4 h-4" />
                            </button>
                        </div>
                    ) : (
                        <label className={`flex flex-col items-center justify-center aspect-video border-2 border-dashed ${errors.image ? 'border-red-300 bg-red-50' : 'border-zinc-300 bg-zinc-50 hover:bg-zinc-100'} rounded-lg cursor-pointer transition-colors`}>
                            <ImageIcon className="w-8 h-8 text-zinc-400 mb-1" />
                            <span className="text-xs text-zinc-500">Cliquer pour ajouter</span>
                            <input type="file" accept="image/*" onChange={(e) => onImage(e.target.files?.[0] || null)} className="hidden" />
                        </label>
                    )}
                    {errors.image && <p className="text-xs text-red-600 mt-1">{errors.image}</p>}
                </div>

                {/* Document */}
                <div>
                    <label className="block text-sm font-medium text-zinc-700 mb-1.5">Document (PDF, Word, PPT)</label>
                    <label className={`flex flex-col items-center justify-center aspect-video border-2 border-dashed ${errors.document ? 'border-red-300 bg-red-50' : 'border-zinc-300 bg-zinc-50 hover:bg-zinc-100'} rounded-lg cursor-pointer transition-colors text-center px-2`}>
                        <FileText className="w-8 h-8 text-zinc-400 mb-1" />
                        <span className="text-xs text-zinc-500 truncate max-w-full">
                            {data.document ? data.document.name : existingDocName ? existingDocName : 'Cliquer pour ajouter'}
                        </span>
                        <input
                            type="file"
                            accept=".pdf,.doc,.docx,.ppt,.pptx"
                            onChange={(e) => setData('document', e.target.files?.[0] || null)}
                            className="hidden"
                        />
                    </label>
                    <p className="text-xs text-zinc-500 mt-1">Max 10 MB</p>
                    {errors.document && <p className="text-xs text-red-600 mt-1">{errors.document}</p>}
                </div>
            </div>
        </>
    );
}

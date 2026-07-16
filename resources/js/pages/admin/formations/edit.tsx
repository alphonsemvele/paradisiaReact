import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import { ArrowLeft, Save, GraduationCap, FileDown, Trash2 } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { FormFields } from './create';
import { resizeImageFile } from '@/utils/resizeImage';

interface Formation {
    id: number;
    titre: string;
    description: string | null;
    prix: number | string;
    duree: string | null;
    session: string | null;
    mode: string;
    statut: string;
    image: string | null;
    document: string | null;
    document_nom: string | null;
}

export default function FormationEdit({ formation }: { formation: Formation }) {
    const [preview, setPreview] = useState<string | null>(null);
    const [removeDoc, setRemoveDoc] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        titre: formation.titre || '',
        description: formation.description || '',
        prix: String(formation.prix ?? ''),
        duree: formation.duree || '',
        session: formation.session || '',
        mode: formation.mode || 'presentiel',
        image: null as File | null,
        document: null as File | null,
        remove_document: false as boolean,
    });

    const handleImage = async (file: File | null) => {
        const optimized = file ? await resizeImageFile(file) : null;
        setData('image', optimized);
        if (!optimized) return setPreview(null);
        const reader = new FileReader();
        reader.onload = (e) => setPreview(e.target?.result as string);
        reader.readAsDataURL(optimized);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/formations/${formation.id}`, { forceFormData: true });
    };

    const showExistingDoc = formation.document && !removeDoc && !data.document;

    return (
        <AdminLayout title="Modifier la formation">
            <Head title="Admin - Modifier formation" />

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
                            <h2 className="font-semibold text-zinc-900">Modifier la formation</h2>
                            <p className="text-xs text-zinc-500">Laissez l'image vide pour conserver l'actuelle</p>
                        </div>
                    </div>

                    <FormFields
                        data={data}
                        setData={setData}
                        errors={errors}
                        preview={preview}
                        onImage={handleImage}
                        existingImage={formation.image}
                        existingDocName={showExistingDoc ? formation.document_nom : null}
                    />

                    {/* Document existant : lien + suppression */}
                    {showExistingDoc && (
                        <div className="flex items-center justify-between bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-2.5">
                            <a
                                href={formation.document!}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 text-sm text-emerald-700 hover:underline"
                            >
                                <FileDown className="w-4 h-4" />
                                {formation.document_nom}
                            </a>
                            <button
                                type="button"
                                onClick={() => {
                                    setRemoveDoc(true);
                                    setData('remove_document', true);
                                }}
                                className="inline-flex items-center gap-1.5 text-xs text-red-600 hover:bg-red-50 px-2 py-1 rounded"
                            >
                                <Trash2 className="w-3.5 h-3.5" /> Retirer
                            </button>
                        </div>
                    )}
                    {removeDoc && (
                        <p className="text-xs text-amber-600">
                            Le document sera retiré à l'enregistrement.{' '}
                            <button
                                type="button"
                                onClick={() => {
                                    setRemoveDoc(false);
                                    setData('remove_document', false);
                                }}
                                className="underline"
                            >
                                Annuler
                            </button>
                        </p>
                    )}

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
                            {processing ? 'Enregistrement...' : 'Enregistrer'}
                        </button>
                    </div>
                </motion.form>
            </div>
        </AdminLayout>
    );
}

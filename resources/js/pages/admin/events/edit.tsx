import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeft, Save, Calendar, Users, FileDown, Trash2 } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { EventFields } from './create';
import { resizeImageFile } from '@/utils/resizeImage';

export default function EventEdit({ event }: { event: any }) {
    const [preview, setPreview] = useState<string | null>(null);
    const [removeDoc, setRemoveDoc] = useState(false);

    const { data, setData, post, processing, errors } = useForm<any>({
        _method: 'PATCH',
        titre: event.titre || '',
        description: event.description || '',
        type: event.type || 'meeting',
        mode: event.mode || 'en_ligne',
        lieu: event.lieu || '',
        date_debut: event.date_debut || '',
        date_fin: event.date_fin || '',
        collecte_pays: !!event.collecte_pays,
        collecte_profil: !!event.collecte_profil,
        collecte_telephone: !!event.collecte_telephone,
        collecte_nom: !!event.collecte_nom,
        message_confirmation: event.message_confirmation || '',
        lien_reunion: event.lien_reunion || '',
        statut: event.statut || 'publie',
        inscriptions_ouvertes: !!event.inscriptions_ouvertes,
        places_max: event.places_max ?? '',
        image: null as File | null,
        images: [] as File[],
        remove_images: [] as number[],
        document: null as File | null,
        remove_document: false,
    });

    const [galleryPreviews, setGalleryPreviews] = useState<string[]>([]);

    const handleGalleryAdd = async (files: FileList | null) => {
        if (!files?.length) return;
        const opt = await Promise.all(Array.from(files).map((f) => resizeImageFile(f)));
        const next = [...data.images, ...opt].slice(0, 10);
        setData('images', next);
        setGalleryPreviews(next.map((f) => URL.createObjectURL(f)));
    };
    const handleGalleryRemove = (i: number) => {
        const next = data.images.filter((_: File, k: number) => k !== i);
        setData('images', next);
        setGalleryPreviews(next.map((f: File) => URL.createObjectURL(f)));
    };
    const handleRemoveExisting = (id: number) => setData('remove_images', [...data.remove_images, id]);
    const visibleImages = (event.images || []).filter((img: any) => !data.remove_images.includes(img.id));

    const handleImage = async (file: File | null) => {
        const opt = file ? await resizeImageFile(file) : null;
        setData('image', opt);
        if (!opt) return setPreview(null);
        const reader = new FileReader();
        reader.onload = (e) => setPreview(e.target?.result as string);
        reader.readAsDataURL(opt);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/events/${event.id}`, { forceFormData: true });
    };

    const docVisible = event.document && !removeDoc && !data.document;

    return (
        <AdminLayout title="Modifier l'événement">
            <Head title="Admin - Modifier événement" />
            <div className="max-w-3xl mx-auto space-y-6">
                <div className="flex items-center justify-between">
                    <Link href="/admin/events" className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900">
                        <ArrowLeft className="w-4 h-4" /> Retour
                    </Link>
                    <Link href={`/admin/events/${event.id}/inscrits`}
                        className="inline-flex items-center gap-1.5 text-sm text-emerald-700 font-medium hover:underline">
                        <Users className="w-4 h-4" /> Voir les inscrits
                    </Link>
                </div>

                <form onSubmit={submit} className="bg-white rounded-2xl border border-zinc-200 p-6 space-y-5">
                    <div className="flex items-center gap-3 pb-4 border-b border-zinc-100">
                        <div className="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <Calendar className="w-5 h-5 text-emerald-600" />
                        </div>
                        <h2 className="font-semibold text-zinc-900">Modifier l'événement</h2>
                    </div>

                    <EventFields data={data} setData={setData} errors={errors} preview={preview}
                        onImage={handleImage} existingImage={event.image}
                        galleryPreviews={galleryPreviews} onGalleryAdd={handleGalleryAdd} onGalleryRemove={handleGalleryRemove}
                        existingImages={visibleImages} onRemoveExisting={handleRemoveExisting}
                        existingDocName={docVisible ? event.document_nom : null} />

                    {docVisible && (
                        <div className="flex items-center justify-between bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-2.5">
                            <a href={event.document} target="_blank" rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 text-sm text-emerald-700 hover:underline">
                                <FileDown className="w-4 h-4" /> {event.document_nom}
                            </a>
                            <button type="button" onClick={() => { setRemoveDoc(true); setData('remove_document', true); }}
                                className="p-1.5 text-zinc-400 hover:text-red-600">
                                <Trash2 className="w-4 h-4" />
                            </button>
                        </div>
                    )}

                    <div className="flex gap-3 pt-4 border-t border-zinc-100">
                        <Link href="/admin/events" className="flex-1 text-center py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg">
                            Annuler
                        </Link>
                        <button type="submit" disabled={processing}
                            className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-300 text-white font-medium rounded-lg">
                            <Save className="w-4 h-4" /> {processing ? 'Enregistrement…' : 'Enregistrer'}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

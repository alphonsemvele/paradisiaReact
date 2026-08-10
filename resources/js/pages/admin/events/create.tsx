import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeft, Save, Calendar, ImageIcon, X, FileText } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { resizeImageFile } from '@/utils/resizeImage';

export default function EventCreate() {
    const [preview, setPreview] = useState<string | null>(null);

    const { data, setData, post, processing, errors } = useForm<any>({
        titre: '',
        description: '',
        type: 'meeting',
        mode: 'en_ligne',
        lieu: '',
        date_debut: '',
        date_fin: '',
        collecte_pays: true,
        collecte_profil: true,
        collecte_telephone: false,
        collecte_nom: false,
        email_optionnel: false,
        message_confirmation: '',
        lien_reunion: '',
        statut: 'publie',
        inscriptions_ouvertes: true,
        places_max: '',
        image: null as File | null,
        images: [] as File[],
        document: null as File | null,
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
        post('/admin/events', { forceFormData: true });
    };

    return (
        <AdminLayout title="Nouvel événement">
            <Head title="Admin - Nouvel événement" />
            <div className="max-w-3xl mx-auto space-y-6">
                <Link href="/admin/events" className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900">
                    <ArrowLeft className="w-4 h-4" /> Retour aux événements
                </Link>

                <form onSubmit={submit} className="bg-white rounded-2xl border border-zinc-200 p-6 space-y-5">
                    <div className="flex items-center gap-3 pb-4 border-b border-zinc-100">
                        <div className="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <Calendar className="w-5 h-5 text-emerald-600" />
                        </div>
                        <h2 className="font-semibold text-zinc-900">Créer un événement</h2>
                    </div>

                    <EventFields data={data} setData={setData} errors={errors} preview={preview} onImage={handleImage}
                        galleryPreviews={galleryPreviews} onGalleryAdd={handleGalleryAdd} onGalleryRemove={handleGalleryRemove} />

                    <div className="flex gap-3 pt-4 border-t border-zinc-100">
                        <Link href="/admin/events" className="flex-1 text-center py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg">
                            Annuler
                        </Link>
                        <button type="submit" disabled={processing}
                            className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-300 text-white font-medium rounded-lg">
                            <Save className="w-4 h-4" /> {processing ? 'Création…' : 'Créer l\'événement'}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

/* ═════════ Champs partagés création / édition ═════════ */
export function EventFields({ data, setData, errors, preview, onImage, galleryPreviews, onGalleryAdd, onGalleryRemove, existingImage, existingImages, onRemoveExisting, existingDocName }: {
    data: any;
    setData: (k: any, v: any) => void;
    errors: Record<string, string>;
    preview: string | null;
    onImage: (f: File | null) => void;
    galleryPreviews: string[];
    onGalleryAdd: (files: FileList | null) => void;
    onGalleryRemove: (i: number) => void;
    existingImage?: string | null;
    existingImages?: { id: number; url: string }[];
    onRemoveExisting?: (id: number) => void;
    existingDocName?: string | null;
}) {
    const champ = 'w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500';
    const label = 'block text-sm font-medium text-zinc-700 mb-1.5';

    const Toggle = ({ k, titre, desc }: { k: string; titre: string; desc: string }) => (
        <label className="flex items-start gap-3 p-3 rounded-xl border border-zinc-200 cursor-pointer hover:bg-zinc-50">
            <input type="checkbox" checked={!!data[k]} onChange={(e) => setData(k, e.target.checked)} className="mt-0.5" />
            <span>
                <span className="block text-sm font-medium text-zinc-800">{titre}</span>
                <span className="block text-xs text-zinc-500">{desc}</span>
            </span>
        </label>
    );

    return (
        <>
            <div>
                <label className={label}>Titre *</label>
                <input value={data.titre} onChange={(e) => setData('titre', e.target.value)} required
                    placeholder="Ex : Meeting Paradisia — Vision 2027" className={champ} />
                {errors.titre && <p className="text-xs text-red-600 mt-1">{errors.titre}</p>}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label className={label}>Type</label>
                    <input value={data.type} onChange={(e) => setData('type', e.target.value)}
                        placeholder="meeting, webinaire, conférence…" className={champ} />
                </div>
                <div>
                    <label className={label}>Format *</label>
                    <select value={data.mode} onChange={(e) => setData('mode', e.target.value)} className={champ}>
                        <option value="en_ligne">En ligne</option>
                        <option value="presentiel">En présentiel</option>
                        <option value="hybride">Hybride</option>
                    </select>
                </div>
            </div>

            {data.mode !== 'en_ligne' && (
                <div>
                    <label className={label}>Lieu</label>
                    <input value={data.lieu} onChange={(e) => setData('lieu', e.target.value)}
                        placeholder="Adresse du lieu" className={champ} />
                </div>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label className={label}>Date et heure de début *</label>
                    <input type="datetime-local" value={data.date_debut} onChange={(e) => setData('date_debut', e.target.value)} required className={champ} />
                    {errors.date_debut && <p className="text-xs text-red-600 mt-1">{errors.date_debut}</p>}
                </div>
                <div>
                    <label className={label}>Date et heure de fin</label>
                    <input type="datetime-local" value={data.date_fin} onChange={(e) => setData('date_fin', e.target.value)} className={champ} />
                </div>
            </div>

            <div>
                <label className={label}>Description</label>
                <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} rows={4}
                    placeholder="Présentation de l'événement, programme…" className={`${champ} resize-none`} />
            </div>

            {/* Configuration du formulaire d'inscription */}
            <div>
                <p className="text-sm font-semibold text-zinc-800 mb-2">Que demander à l'inscription ?</p>
                <p className="text-xs text-zinc-500 mb-3">Par défaut l'e-mail est demandé. Choisissez les champs supplémentaires.</p>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <Toggle k="collecte_profil" titre="Profil" desc="Investisseur ou participant" />
                    <Toggle k="collecte_pays" titre="Pays" desc="Sélection dans la liste des pays" />
                    <Toggle k="collecte_nom" titre="Nom" desc="Nom complet de la personne" />
                    <Toggle k="collecte_telephone" titre="Numéro WhatsApp" desc="Numéro de contact WhatsApp" />
                    <Toggle k="email_optionnel" titre="E-mail facultatif" desc="Ne pas exiger l'e-mail (WhatsApp suffit)" />
                </div>
            </div>

            <div>
                <label className={label}>Message de confirmation (e-mail)</label>
                <textarea value={data.message_confirmation} onChange={(e) => setData('message_confirmation', e.target.value)} rows={2}
                    placeholder="Laissez vide pour : « Le lien de la réunion vous sera envoyé le moment venu. »" className={`${champ} resize-none`} />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label className={label}>Lien de réunion (envoyé plus tard)</label>
                    <input value={data.lien_reunion} onChange={(e) => setData('lien_reunion', e.target.value)}
                        placeholder="https://meet.google.com/…" className={champ} />
                </div>
                <div>
                    <label className={label}>Places maximum</label>
                    <input type="number" min="1" value={data.places_max} onChange={(e) => setData('places_max', e.target.value)}
                        placeholder="Illimité si vide" className={champ} />
                </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label className={label}>Statut</label>
                    <select value={data.statut} onChange={(e) => setData('statut', e.target.value)} className={champ}>
                        <option value="publie">Publié (visible à l'accueil)</option>
                        <option value="brouillon">Brouillon</option>
                        <option value="termine">Terminé</option>
                    </select>
                </div>
                <label className="flex items-center gap-3 mt-7">
                    <input type="checkbox" checked={!!data.inscriptions_ouvertes} onChange={(e) => setData('inscriptions_ouvertes', e.target.checked)} />
                    <span className="text-sm text-zinc-700">Inscriptions ouvertes</span>
                </label>
            </div>

            {/* Image + document */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label className={label}>Image (carte d'accueil)</label>
                    {preview || existingImage ? (
                        <div className="relative aspect-video bg-zinc-100 rounded-lg overflow-hidden">
                            <img src={preview || existingImage!} alt="" className="w-full h-full object-cover" />
                            <button type="button" onClick={() => onImage(null)}
                                className="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full">
                                <X className="w-4 h-4" />
                            </button>
                        </div>
                    ) : (
                        <label className="flex flex-col items-center justify-center aspect-video border-2 border-dashed border-zinc-300 bg-zinc-50 hover:bg-zinc-100 rounded-lg cursor-pointer">
                            <ImageIcon className="w-8 h-8 text-zinc-400 mb-1" />
                            <span className="text-xs text-zinc-500">Cliquer pour ajouter</span>
                            <input type="file" accept="image/*" onChange={(e) => onImage(e.target.files?.[0] || null)} className="hidden" />
                        </label>
                    )}
                    {errors.image && <p className="text-xs text-red-600 mt-1">{errors.image}</p>}
                </div>
                <div>
                    <label className={label}>Fichier joint (PDF, Word, PPT)</label>
                    <label className="flex flex-col items-center justify-center aspect-video border-2 border-dashed border-zinc-300 bg-zinc-50 hover:bg-zinc-100 rounded-lg cursor-pointer text-center px-2">
                        <FileText className="w-8 h-8 text-zinc-400 mb-1" />
                        <span className="text-xs text-zinc-500 truncate max-w-full">
                            {data.document ? data.document.name : existingDocName || 'Cliquer pour ajouter'}
                        </span>
                        <input type="file" accept=".pdf,.doc,.docx,.ppt,.pptx"
                            onChange={(e) => setData('document', e.target.files?.[0] || null)} className="hidden" />
                    </label>
                    {errors.document && <p className="text-xs text-red-600 mt-1">{errors.document}</p>}
                </div>
            </div>

            {/* Galerie d'images (en plus de la couverture) */}
            <div>
                <label className={label}>
                    Galerie d'images <span className="text-zinc-400 font-normal">(jusqu'à 10, en plus de la couverture)</span>
                </label>
                <div className="grid grid-cols-3 sm:grid-cols-5 gap-3">
                    {existingImages?.map((img) => (
                        <div key={`ex-${img.id}`} className="relative aspect-square bg-zinc-100 rounded-lg overflow-hidden group">
                            <img src={img.url} alt="" className="w-full h-full object-cover" />
                            <button type="button" onClick={() => onRemoveExisting?.(img.id)}
                                className="absolute top-1.5 right-1.5 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                <X className="w-3.5 h-3.5" />
                            </button>
                        </div>
                    ))}
                    {galleryPreviews.map((url, i) => (
                        <div key={`new-${i}`} className="relative aspect-square bg-zinc-100 rounded-lg overflow-hidden group">
                            <img src={url} alt="" className="w-full h-full object-cover" />
                            <button type="button" onClick={() => onGalleryRemove(i)}
                                className="absolute top-1.5 right-1.5 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                <X className="w-3.5 h-3.5" />
                            </button>
                        </div>
                    ))}
                    <label className="flex flex-col items-center justify-center aspect-square border-2 border-dashed border-zinc-300 bg-zinc-50 hover:bg-zinc-100 rounded-lg cursor-pointer">
                        <ImageIcon className="w-6 h-6 text-zinc-400 mb-1" />
                        <span className="text-[11px] text-zinc-500">Ajouter</span>
                        <input type="file" accept="image/*" multiple className="hidden"
                            onChange={(e) => { onGalleryAdd(e.target.files); e.target.value = ''; }} />
                    </label>
                </div>
                {errors.images && <p className="text-xs text-red-600 mt-1">{errors.images}</p>}
            </div>
        </>
    );
}

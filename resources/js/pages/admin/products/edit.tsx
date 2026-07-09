import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import { ArrowLeft, X, Save, ImageIcon, Package } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';
import { TypeSelector, CompositionBuilder, type ComponentRow } from '@/components/admin/ProductComposition';

interface Props {
    product: {
        id: number;
        name: string;
        description: string | null;
        price: number;
        type: 'simple' | 'compose';
        id_category: number | null;
        status: string | null;
        img_1: string | null;
        img_2: string | null;
        components: ComponentRow[];
    };
    categories: Array<{ id: number; name: string }>;
    simpleProducts: Array<{ id: number; name: string }>;
}

export default function ProductEdit({ product, categories, simpleProducts }: Props) {
    const [preview1, setPreview1] = useState<string | null>(product.img_1);
    const [preview2, setPreview2] = useState<string | null>(product.img_2);

    const { data, setData, post, processing, errors } = useForm({
        _method: 'PATCH',
        name: product.name,
        description: product.description || '',
        price: product.price.toString(),
        type: (product.type || 'simple') as 'simple' | 'compose',
        id_category: product.id_category?.toString() || '',
        components: (product.components || []) as ComponentRow[],
        img_1: null as File | null,
        img_2: null as File | null,
        remove_img_1: false,
        remove_img_2: false,
    });

    const handleFile = (key: 'img_1' | 'img_2', file: File | null) => {
        setData(key, file);
        if (key === 'img_1') {
            setData('remove_img_1', false);
            if (!file) {
                setPreview1(null);
                return;
            }
        } else {
            setData('remove_img_2', false);
            if (!file) {
                setPreview2(null);
                return;
            }
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            const result = e.target?.result as string;
            key === 'img_1' ? setPreview1(result) : setPreview2(result);
        };
        reader.readAsDataURL(file);
    };

    const removeImage = (key: 'img_1' | 'img_2') => {
        if (key === 'img_1') {
            setPreview1(null);
            setData('img_1', null);
            setData('remove_img_1', true);
        } else {
            setPreview2(null);
            setData('img_2', null);
            setData('remove_img_2', true);
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/products/${product.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout title={`Modifier : ${product.name}`}>
            <Head title={`Admin - ${product.name}`} />

            <div className="max-w-3xl mx-auto space-y-6">
                <Link href="/admin/products" className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900">
                    <ArrowLeft className="w-4 h-4" />
                    Retour aux produits
                </Link>

                <motion.form onSubmit={submit} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6 space-y-5">
                    <div className="flex items-center gap-3 pb-4 border-b border-zinc-100">
                        <div className="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <Package className="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <h2 className="font-semibold text-zinc-900">Modifier le produit</h2>
                            <p className="text-xs text-zinc-500">{product.name}</p>
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5">Nom du produit *</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            className={`w-full px-3 py-2.5 bg-zinc-50 border ${errors.name ? 'border-red-300' : 'border-zinc-200'} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                        />
                        {errors.name && <p className="text-xs text-red-600 mt-1">{errors.name}</p>}
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-zinc-700 mb-1.5">Prix (FCFA) *</label>
                            <input
                                type="number"
                                step="1"
                                min="0"
                                value={data.price}
                                onChange={(e) => setData('price', e.target.value)}
                                required
                                className={`w-full px-3 py-2.5 bg-zinc-50 border ${errors.price ? 'border-red-300' : 'border-zinc-200'} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-zinc-700 mb-1.5">Catégorie</label>
                            <select
                                value={data.id_category}
                                onChange={(e) => setData('id_category', e.target.value)}
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="">Sans catégorie</option>
                                {categories.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Type de produit */}
                    <TypeSelector value={data.type} onChange={(v) => setData('type', v)} />

                    {/* Composition (si composé) */}
                    {data.type === 'compose' && (
                        <CompositionBuilder
                            value={data.components}
                            onChange={(rows) => setData('components', rows)}
                            simpleProducts={simpleProducts}
                            errors={errors as Record<string, string>}
                        />
                    )}

                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5">Description</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows={4}
                            className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-3">Images</label>
                        <div className="grid grid-cols-2 gap-3">
                            <ImageUpload
                                label="Image principale"
                                preview={preview1}
                                onChange={(f) => handleFile('img_1', f)}
                                onRemove={() => removeImage('img_1')}
                            />
                            <ImageUpload
                                label="Image secondaire"
                                preview={preview2}
                                onChange={(f) => handleFile('img_2', f)}
                                onRemove={() => removeImage('img_2')}
                            />
                        </div>
                    </div>

                    <div className="flex gap-3 pt-4 border-t border-zinc-100">
                        <Link href="/admin/products" className="flex-1 text-center py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg">
                            Annuler
                        </Link>
                        <button type="submit" disabled={processing} className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-300 text-white font-medium rounded-lg">
                            <Save className="w-4 h-4" />
                            {processing ? 'Sauvegarde...' : 'Enregistrer'}
                        </button>
                    </div>
                </motion.form>
            </div>
        </AdminLayout>
    );
}

function ImageUpload({ label, preview, onChange, onRemove }: { label: string; preview: string | null; onChange: (f: File | null) => void; onRemove: () => void; }) {
    return (
        <div>
            <p className="text-xs text-zinc-500 mb-1.5">{label}</p>
            {preview ? (
                <div className="relative aspect-square bg-zinc-100 rounded-lg overflow-hidden">
                    <img src={preview} alt="Aperçu" className="w-full h-full object-cover" />
                    <button type="button" onClick={onRemove} className="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600">
                        <X className="w-4 h-4" />
                    </button>
                </div>
            ) : (
                <label className="flex flex-col items-center justify-center aspect-square border-2 border-dashed border-zinc-300 bg-zinc-50 hover:bg-zinc-100 rounded-lg cursor-pointer transition-colors">
                    <ImageIcon className="w-8 h-8 text-zinc-400 mb-1" />
                    <span className="text-xs text-zinc-500">Cliquer pour ajouter</span>
                    <input type="file" accept="image/*" onChange={(e) => onChange(e.target.files?.[0] || null)} className="hidden" />
                </label>
            )}
        </div>
    );
}
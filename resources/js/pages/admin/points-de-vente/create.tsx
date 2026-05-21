import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import { ArrowLeft, X, Save, ImageIcon, Store, MapPin, Phone, Clock } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

export default function PointDeVenteCreate() {
    const [preview, setPreview] = useState<string | null>(null);

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        address: '',
        phone: '',
        hours: '',
        latitude: '',
        longitude: '',
        image: null as File | null,
    });

    const handleFile = (file: File | null) => {
        setData('image', file);
        if (!file) {
            setPreview(null);
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => setPreview(e.target?.result as string);
        reader.readAsDataURL(file);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/points-de-vente', { forceFormData: true });
    };

    return (
        <AdminLayout title="Nouveau point de vente">
            <Head title="Admin - Nouveau point de vente" />

            <div className="max-w-3xl mx-auto space-y-6">
                <Link
                    href="/admin/points-de-vente"
                    className="flex items-center gap-2 text-sm text-zinc-600 hover:text-zinc-900"
                >
                    <ArrowLeft className="w-4 h-4" />
                    Retour aux points de vente
                </Link>

                <motion.form
                    onSubmit={submit}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6 space-y-5"
                >
                    <div className="flex items-center gap-3 pb-4 border-b border-zinc-100">
                        <div className="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <Store className="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <h2 className="font-semibold text-zinc-900">Créer un point de vente</h2>
                            <p className="text-xs text-zinc-500">Seul le nom est obligatoire</p>
                        </div>
                    </div>

                    {/* Nom */}
                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                            Nom du point de vente *
                        </label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Ex: PARADISIA Akwa"
                            autoFocus
                            required
                            className={`w-full px-3 py-2.5 bg-zinc-50 border ${
                                errors.name ? 'border-red-300' : 'border-zinc-200'
                            } rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500`}
                        />
                        {errors.name && <p className="text-xs text-red-600 mt-1">{errors.name}</p>}
                    </div>

                    {/* Adresse */}
                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5 flex items-center gap-1.5">
                            <MapPin className="w-4 h-4 text-zinc-400" />
                            Adresse (optionnel)
                        </label>
                        <input
                            type="text"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                            placeholder="Ex: Rue Joss, Akwa, Douala"
                            className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {/* Téléphone */}
                        <div>
                            <label className="block text-sm font-medium text-zinc-700 mb-1.5 flex items-center gap-1.5">
                                <Phone className="w-4 h-4 text-zinc-400" />
                                Téléphone (optionnel)
                            </label>
                            <input
                                type="tel"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                placeholder="+237 6XX XXX XXX"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>

                        {/* Horaires */}
                        <div>
                            <label className="block text-sm font-medium text-zinc-700 mb-1.5 flex items-center gap-1.5">
                                <Clock className="w-4 h-4 text-zinc-400" />
                                Horaires (optionnel)
                            </label>
                            <input
                                type="text"
                                value={data.hours}
                                onChange={(e) => setData('hours', e.target.value)}
                                placeholder="Ex: 8h - 20h"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    {/* Géolocalisation */}
                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5 flex items-center gap-1.5">
                            <MapPin className="w-4 h-4 text-zinc-400" />
                            Coordonnées GPS (optionnel)
                        </label>
                        <div className="grid grid-cols-2 gap-3">
                            <input
                                type="number"
                                step="any"
                                value={data.latitude}
                                onChange={(e) => setData('latitude', e.target.value)}
                                placeholder="Latitude (ex: 4.0511)"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                            <input
                                type="number"
                                step="any"
                                value={data.longitude}
                                onChange={(e) => setData('longitude', e.target.value)}
                                placeholder="Longitude (ex: 9.7679)"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <p className="text-xs text-zinc-500 mt-1.5">
                            💡 Vous pouvez récupérer ces coordonnées sur Google Maps (clic droit
                            sur l'emplacement)
                        </p>
                    </div>

                    {/* Image */}
                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                            Image du point (optionnel)
                        </label>
                        <ImageUpload
                            preview={preview}
                            onChange={handleFile}
                            onRemove={() => handleFile(null)}
                            error={errors.image}
                        />
                        <p className="text-xs text-zinc-500 mt-1.5">
                            Formats : JPG, PNG, WebP · Max 5 MB · Une image par défaut sera utilisée
                            sinon
                        </p>
                    </div>

                    {/* Actions */}
                    <div className="flex gap-3 pt-4 border-t border-zinc-100">
                        <Link
                            href="/admin/points-de-vente"
                            className="flex-1 text-center py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-lg"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-300 text-white font-medium rounded-lg"
                        >
                            <Save className="w-4 h-4" />
                            {processing ? 'Création...' : 'Créer le point de vente'}
                        </button>
                    </div>
                </motion.form>
            </div>
        </AdminLayout>
    );
}

function ImageUpload({
    preview,
    onChange,
    onRemove,
    error,
}: {
    preview: string | null;
    onChange: (file: File | null) => void;
    onRemove: () => void;
    error?: string;
}) {
    return (
        <div>
            {preview ? (
                <div className="relative aspect-video bg-zinc-100 rounded-lg overflow-hidden max-w-md">
                    <img src={preview} alt="Aperçu" className="w-full h-full object-cover" />
                    <button
                        type="button"
                        onClick={onRemove}
                        className="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>
            ) : (
                <label
                    className={`flex flex-col items-center justify-center aspect-video max-w-md border-2 border-dashed ${
                        error
                            ? 'border-red-300 bg-red-50'
                            : 'border-zinc-300 bg-zinc-50 hover:bg-zinc-100'
                    } rounded-lg cursor-pointer transition-colors`}
                >
                    <ImageIcon className="w-10 h-10 text-zinc-400 mb-2" />
                    <span className="text-sm text-zinc-600 font-medium">
                        Cliquer pour ajouter une image
                    </span>
                    <span className="text-xs text-zinc-400 mt-1">JPG, PNG, WebP - Max 5 MB</span>
                    <input
                        type="file"
                        accept="image/*"
                        onChange={(e) => onChange(e.target.files?.[0] || null)}
                        className="hidden"
                    />
                </label>
            )}
            {error && <p className="text-xs text-red-600 mt-1">{error}</p>}
        </div>
    );
}
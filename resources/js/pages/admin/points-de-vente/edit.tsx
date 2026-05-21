import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { useState } from 'react';
import { ArrowLeft, X, Save, ImageIcon, Store, MapPin, Phone, Clock } from 'lucide-react';
import AdminLayout from '@/components/layouts/AdminLayout';

export default function PointDeVenteEdit({ point }: any) {
    const [preview, setPreview] = useState<string | null>(point?.image || null);

    const { data, setData, post, processing, errors } = useForm({
        _method: 'PATCH',
        name: point?.name || '',
        address: point?.address || '',
        phone: point?.phone || '',
        hours: point?.hours || '',
        latitude: point?.latitude?.toString() || '',
        longitude: point?.longitude?.toString() || '',
        image: null as File | null,
        remove_image: false,
    });

    const handleFile = (file: File | null) => {
        setData('image', file);
        setData('remove_image', false);
        if (!file) {
            setPreview(null);
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => setPreview(e.target?.result as string);
        reader.readAsDataURL(file);
    };

    const removeImage = () => {
        setPreview(null);
        setData('image', null);
        setData('remove_image', true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/points-de-vente/${point.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout title={`Modifier : ${point?.name || ''}`}>
            <Head title={`Admin - ${point?.name || 'Édition'}`} />

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
                            <h2 className="font-semibold text-zinc-900">Modifier le point</h2>
                            <p className="text-xs text-zinc-500">{point?.name}</p>
                        </div>
                    </div>

                    {/* Nom */}
                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                            Nom *
                        </label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
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
                            Adresse
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
                        <div>
                            <label className="block text-sm font-medium text-zinc-700 mb-1.5 flex items-center gap-1.5">
                                <Phone className="w-4 h-4 text-zinc-400" />
                                Téléphone
                            </label>
                            <input
                                type="tel"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                placeholder="+237 6XX XXX XXX"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-zinc-700 mb-1.5 flex items-center gap-1.5">
                                <Clock className="w-4 h-4 text-zinc-400" />
                                Horaires
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
                            Coordonnées GPS
                        </label>
                        <div className="grid grid-cols-2 gap-3">
                            <input
                                type="number"
                                step="any"
                                value={data.latitude}
                                onChange={(e) => setData('latitude', e.target.value)}
                                placeholder="Latitude"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                            <input
                                type="number"
                                step="any"
                                value={data.longitude}
                                onChange={(e) => setData('longitude', e.target.value)}
                                placeholder="Longitude"
                                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <p className="text-xs text-zinc-500 mt-1.5">
                            💡 Récupérez ces coordonnées sur Google Maps (clic droit sur
                            l'emplacement)
                        </p>
                    </div>

                    {/* Image */}
                    <div>
                        <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                            Image
                        </label>
                        {preview ? (
                            <div className="relative aspect-video bg-zinc-100 rounded-lg overflow-hidden max-w-md">
                                <img
                                    src={preview}
                                    alt="Aperçu"
                                    className="w-full h-full object-cover"
                                />
                                <button
                                    type="button"
                                    onClick={removeImage}
                                    className="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600"
                                >
                                    <X className="w-4 h-4" />
                                </button>
                            </div>
                        ) : (
                            <label className="flex flex-col items-center justify-center aspect-video max-w-md border-2 border-dashed border-zinc-300 bg-zinc-50 hover:bg-zinc-100 rounded-lg cursor-pointer transition-colors">
                                <ImageIcon className="w-10 h-10 text-zinc-400 mb-2" />
                                <span className="text-sm text-zinc-600 font-medium">
                                    Cliquer pour ajouter une image
                                </span>
                                <span className="text-xs text-zinc-400 mt-1">
                                    JPG, PNG, WebP - Max 5 MB
                                </span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) =>
                                        handleFile(e.target.files?.[0] || null)
                                    }
                                    className="hidden"
                                />
                            </label>
                        )}
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
                            {processing ? 'Sauvegarde...' : 'Enregistrer'}
                        </button>
                    </div>
                </motion.form>
            </div>
        </AdminLayout>
    );
}
import { useState, useEffect, useMemo } from 'react';
import {
    MapPin,
    Navigation,
    Phone,
    ChevronRight,
    Loader2,
    AlertCircle,
    X,
} from 'lucide-react';
import type { PointDeVente as PointType } from '@/types';

interface Props {
    points: PointType[];
}

export default function PointsDeVente({ points: initialPoints }: Props) {
    const [userLocation, setUserLocation] = useState<{ lat: number; lng: number } | null>(null);
    const [locating, setLocating] = useState(false);
    const [locationError, setLocationError] = useState<string | null>(null);
    const [selectedPoint, setSelectedPoint] = useState<PointType | null>(null);
    const [pointsWithStatus, setPointsWithStatus] = useState<PointType[]>(initialPoints);

    // Parser les heures de manière sécurisée
    const parseHours = (hoursStr: string): { open: number; close: number } => {
        try {
            const parts = hoursStr.split(' - ');
            if (parts.length !== 2) return { open: 8, close: 20 };

            const open = parseInt(parts[0].replace(/[^0-9]/g, ''), 10);
            const close = parseInt(parts[1].replace(/[^0-9]/g, ''), 10);

            return {
                open: isNaN(open) ? 8 : open,
                close: isNaN(close) ? 20 : close,
            };
        } catch {
            return { open: 8, close: 20 };
        }
    };

    // Mettre à jour le statut ouvert/fermé
    useEffect(() => {
        const updateStatus = () => {
            const currentHour = new Date().getHours();
            setPointsWithStatus(
                initialPoints.map((p) => {
                    const { open, close } = parseHours(p.hours);
                    return { ...p, isOpen: currentHour >= open && currentHour < close };
                })
            );
        };

        updateStatus();
        const interval = setInterval(updateStatus, 60000);
        return () => clearInterval(interval);
    }, [initialPoints]);

    // Calcul de distance (formule Haversine)
    const calculateDistance = (
        lat1: number,
        lon1: number,
        lat2: number,
        lon2: number
    ): number => {
        const R = 6371;
        const dLat = ((lat2 - lat1) * Math.PI) / 180;
        const dLon = ((lon2 - lon1) * Math.PI) / 180;
        const a =
            Math.sin(dLat / 2) ** 2 +
            Math.cos((lat1 * Math.PI) / 180) *
                Math.cos((lat2 * Math.PI) / 180) *
                Math.sin(dLon / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    };

    // Tri par distance si localisation disponible
    const sortedPoints = useMemo(() => {
        if (!userLocation) return pointsWithStatus;

        return [...pointsWithStatus]
            .map((p) => ({
                ...p,
                distance: calculateDistance(userLocation.lat, userLocation.lng, p.lat, p.lng),
            }))
            .sort((a, b) => (a.distance ?? 0) - (b.distance ?? 0));
    }, [pointsWithStatus, userLocation]);

    // Demander la localisation
    const handleGetLocation = () => {
        if (!navigator.geolocation) {
            setLocationError('Géolocalisation non supportée par votre navigateur');
            return;
        }

        setLocating(true);
        setLocationError(null);

        navigator.geolocation.getCurrentPosition(
            (position) => {
                setUserLocation({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                });
                setLocating(false);
            },
            (error) => {
                setLocating(false);
                const errorMessages: Record<number, string> = {
                    1: 'Accès à la position refusé',
                    2: 'Position non disponible',
                    3: 'Délai dépassé',
                };
                setLocationError(errorMessages[error.code] ?? 'Erreur de localisation');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000,
            }
        );
    };

    // Ouvrir l'itinéraire dans Google Maps
    const handleOpenMaps = (point: PointType) => {
        const url = userLocation
            ? `https://www.google.com/maps/dir/${userLocation.lat},${userLocation.lng}/${point.lat},${point.lng}`
            : `https://www.google.com/maps/search/?api=1&query=${point.lat},${point.lng}`;
        window.open(url, '_blank', 'noopener,noreferrer');
    };

    // Appeler le point de vente
    const handleCall = (point: PointType) => {
        const cleanPhone = point.phone.replace(/\s/g, '');
        window.location.href = `tel:${cleanPhone}`;
    };

    // Sélectionner un point
    const handleSelectPoint = (point: PointType) => {
        setSelectedPoint(selectedPoint?.id === point.id ? null : point);
    };

    return (
        <div className="bg-white rounded-2xl shadow-lg p-4 border-2 border-orange-100">
            {/* Header */}
            <div className="flex items-center justify-between mb-3">
                <h4 className="font-bold text-gray-800 flex items-center gap-2">
                    <span className="text-2xl">📍</span>
                    Nos Points de Vente
                </h4>
                <button
                    type="button"
                    onClick={handleGetLocation}
                    disabled={locating}
                    className={`text-xs bg-emerald-100 text-emerald-600 px-2.5 py-1.5 rounded-lg hover:bg-emerald-200 transition-all flex items-center gap-1 disabled:opacity-50 ${
                        locating ? 'animate-pulse' : ''
                    }`}
                >
                    {locating ? (
                        <Loader2 className="w-3 h-3 animate-spin" />
                    ) : (
                        <MapPin className="w-3 h-3" />
                    )}
                    <span className="font-medium">
                        {locating ? 'Localisation...' : 'Me localiser'}
                    </span>
                </button>
            </div>

            {/* Message succès */}
            {userLocation && !locationError && (
                <div className="mb-3 p-2 bg-emerald-50 rounded-lg text-xs text-emerald-700 flex items-center gap-2">
                    <MapPin className="w-4 h-4 flex-shrink-0" />
                    <span>Trié par distance depuis votre position</span>
                </div>
            )}

            {/* Message erreur */}
            {locationError && (
                <div className="mb-3 p-2 bg-red-50 rounded-lg text-xs text-red-600 flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2">
                        <AlertCircle className="w-4 h-4 flex-shrink-0" />
                        <span>{locationError}</span>
                    </div>
                    <button
                        onClick={() => setLocationError(null)}
                        className="hover:bg-red-100 rounded p-0.5"
                    >
                        <X className="w-3 h-3" />
                    </button>
                </div>
            )}

            {/* Liste des points */}
            <div className="space-y-2 max-h-80 overflow-y-auto custom-scrollbar pr-1">
                {sortedPoints.length === 0 ? (
                    <p className="text-center text-gray-500 text-sm py-4">
                        Aucun point de vente disponible
                    </p>
                ) : (
                    sortedPoints.map((point, index) => (
                        <PointCard
                            key={point.id}
                            point={point}
                            index={index}
                            isSelected={selectedPoint?.id === point.id}
                            hasUserLocation={!!userLocation}
                            onSelect={() => handleSelectPoint(point)}
                            onOpenMaps={() => handleOpenMaps(point)}
                            onCall={() => handleCall(point)}
                        />
                    ))
                )}
            </div>

            {/* Mini carte du point sélectionné */}
            {selectedPoint && (
                <div className="mt-3 animate-fade-in">
                    <div className="w-full h-40 rounded-xl bg-gray-100 overflow-hidden border border-gray-200">
                        <iframe
                            src={`https://www.google.com/maps?q=${selectedPoint.lat},${selectedPoint.lng}&z=15&output=embed`}
                            className="w-full h-full border-0"
                            allowFullScreen
                            loading="lazy"
                            referrerPolicy="no-referrer-when-downgrade"
                            title={`Carte de ${selectedPoint.name}`}
                        />
                    </div>
                    <button
                        onClick={() => handleOpenMaps(selectedPoint)}
                        className="w-full mt-2 text-xs bg-gradient-to-r from-emerald-500 to-teal-500 text-white py-2 px-4 rounded-lg font-semibold hover:shadow-lg transition-all flex items-center justify-center gap-2"
                    >
                        <Navigation className="w-4 h-4" />
                        Itinéraire vers {selectedPoint.name}
                    </button>
                </div>
            )}

            {/* Bouton voir tous */}
            <button
                type="button"
                className="mt-3 w-full bg-orange-100 hover:bg-orange-200 text-orange-700 font-semibold py-2.5 px-4 rounded-xl transition-all flex items-center justify-center gap-2 text-sm"
            >
                <span>Voir tous les points de vente</span>
                <ChevronRight className="w-4 h-4" />
            </button>

            <style>{`
                .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                .custom-scrollbar::-webkit-scrollbar-track { 
                    background: #f1f1f1; 
                    border-radius: 10px; 
                }
                .custom-scrollbar::-webkit-scrollbar-thumb { 
                    background: #10b981; 
                    border-radius: 10px; 
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover { 
                    background: #059669; 
                }
                @keyframes fade-in {
                    from { opacity: 0; transform: translateY(-10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .animate-fade-in { animation: fade-in 0.3s ease-out; }
            `}</style>
        </div>
    );
}

// Sous-composant : Carte d'un point de vente
interface PointCardProps {
    point: PointType;
    index: number;
    isSelected: boolean;
    hasUserLocation: boolean;
    onSelect: () => void;
    onOpenMaps: () => void;
    onCall: () => void;
}

function PointCard({
    point,
    index,
    isSelected,
    hasUserLocation,
    onSelect,
    onOpenMaps,
    onCall,
}: PointCardProps) {
    return (
        <div
            onClick={onSelect}
            className={`p-3 rounded-xl border transition-all cursor-pointer group ${
                isSelected
                    ? 'bg-emerald-50 border-emerald-300 shadow-md'
                    : 'border-gray-100 hover:border-emerald-300 hover:shadow-md'
            }`}
        >
            <div className="flex items-start gap-3">
                {/* Numéro / Distance */}
                <div
                    className={`flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-white font-bold ${
                        point.isOpen ? 'bg-emerald-500' : 'bg-gray-400'
                    }`}
                >
                    {hasUserLocation && point.distance !== undefined ? (
                        <span className="text-[10px] leading-tight text-center">
                            {point.distance.toFixed(1)}
                            <br />
                            km
                        </span>
                    ) : (
                        <span className="text-sm">{index + 1}</span>
                    )}
                </div>

                {/* Infos */}
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                        <h5 className="font-semibold text-gray-800 text-sm truncate">
                            {point.name}
                        </h5>
                        {point.isOpen && (
                            <span
                                className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse flex-shrink-0"
                                title="Ouvert"
                            />
                        )}
                    </div>
                    <p className="text-xs text-gray-500 truncate mt-0.5">{point.address}</p>
                    <div className="flex items-center gap-2 mt-1.5">
                        <span
                            className={`text-[10px] px-2 py-0.5 rounded-full font-medium ${
                                point.isOpen
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-red-100 text-red-600'
                            }`}
                        >
                            {point.isOpen ? '● Ouvert' : '● Fermé'}
                        </span>
                        <span className="text-[10px] text-gray-400">{point.hours}</span>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex flex-col gap-1 opacity-0 group-hover:opacity-100 sm:opacity-100 transition-opacity">
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            onOpenMaps();
                        }}
                        className="p-1.5 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-all"
                        title="Itinéraire"
                    >
                        <Navigation className="w-3.5 h-3.5" />
                    </button>
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            onCall();
                        }}
                        className="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg hover:bg-emerald-200 transition-all"
                        title="Appeler"
                    >
                        <Phone className="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>
        </div>
    );
}
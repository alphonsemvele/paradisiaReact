import { Link } from '@inertiajs/react';
import { GraduationCap, ArrowRight } from 'lucide-react';

/**
 * Image de fabrication de jus (vitrine).
 * 👉 Remplaçable par une de vos propres photos : déposez-la dans
 *    public/images/formations/ puis remplacez l'URL ci-dessous.
 */
const JUICE_IMAGE =
    'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=500&q=75&auto=format&fit=crop';

export default function ServicesSection() {
    return (
        <div className="bg-white rounded-2xl shadow-lg p-6 border-2 border-emerald-100">
            <h4 className="font-bold text-gray-800 flex items-center gap-2">
                <GraduationCap className="w-5 h-5 text-emerald-600" />
                Nos formations
            </h4>
            <p className="text-xs text-gray-600 mt-1 mb-4">
                Paradisia offre plusieurs formations.
            </p>

            <div className="rounded-xl overflow-hidden border-2 border-emerald-200">
                {/* Image de fabrication de jus */}
                <div className="aspect-video bg-emerald-50 overflow-hidden">
                    <img
                        src={JUICE_IMAGE}
                        alt="Fabrication de jus de fruits naturels"
                        className="w-full h-full object-cover"
                    />
                </div>

                {/* Bouton */}
                <div className="p-4 bg-gradient-to-br from-emerald-50 to-orange-50">
                    <Link
                        href="/formations"
                        className="inline-flex w-full items-center justify-center gap-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                        S'inscrire
                        <ArrowRight className="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>
    );
}

export default function PromoCard() {
    return (
        <div className="bg-gradient-to-br from-emerald-400 via-teal-400 to-blue-500 rounded-2xl shadow-lg overflow-hidden">
            <div className="p-6 text-center text-white">
                <span className="text-5xl mb-3 block">🎁</span>
                <h4 className="font-bold text-xl mb-2">Offre Spéciale !</h4>
                <p className="text-sm mb-4 opacity-90">
                    Inscrivez-vous à nos formations et recevez un pack cadeau exclusif
                </p>
                <button className="bg-white text-emerald-600 font-bold py-3 px-6 rounded-full hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                    En savoir plus
                </button>
            </div>
        </div>
    );
}
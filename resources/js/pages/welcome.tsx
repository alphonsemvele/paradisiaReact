import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue" />
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-paradisia-50 to-mango-50">
                <div className="text-center">
                    <h1 className="text-6xl font-heading font-bold text-paradisia-600">
                        🌴 PARADISIA 🍹
                    </h1>
                    <p className="mt-4 text-xl text-gray-600">
                        React + Inertia + Laravel — Ça marche ! ✨
                    </p>
                </div>
            </div>
        </>
    );
}
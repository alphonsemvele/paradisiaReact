import { Head, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import AppLayout from '@/components/layouts/AppLayout';
import HeroSection from '@/components/dashboard/HeroSection';
import ProfileCard from '@/components/dashboard/ProfileCard';
import QuickActions from '@/components/dashboard/QuickActions';
import PointsDeVente from '@/components/dashboard/PointsDeVente';
import CreatePostBox from '@/components/dashboard/CreatePostBox';
import ShopSection from '@/components/dashboard/ShopSection';
import PublicationCard from '@/components/dashboard/PublicationCard';
import ServicesSection from '@/components/dashboard/ServicesSection';
import PromoCard from '@/components/dashboard/PromoCard';
import ShareModal from '@/components/dashboard/ShareModal';
import CreatePostModal from '@/components/dashboard/CreatePostModal';
import PublicationModal from '@/components/dashboard/PublicationModal';
import type { Publication, Product, PointDeVente as PointDeVenteType, PageProps } from '@/types';

interface DashboardProps extends PageProps {
    publications: Publication[];
    highlightedPublication: Publication | null;
    featuredProducts: Product[];
    otherProducts: Product[];
    pointsDeVente: PointDeVenteType[];
}

export default function DashboardIndex() {
    const {
        publications,
        highlightedPublication,
        featuredProducts,
        otherProducts,
        pointsDeVente,
        auth,
    } = usePage<DashboardProps>().props;

    const [shareModalPub, setShareModalPub] = useState<Publication | null>(null);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [highlightModalPub, setHighlightModalPub] = useState<Publication | null>(
        highlightedPublication
    );

    // Si l'URL change et contient un highlight, afficher le modal
    useEffect(() => {
        if (highlightedPublication) {
            setHighlightModalPub(highlightedPublication);
        }
    }, [highlightedPublication]);

    const handleCloseHighlight = () => {
        setHighlightModalPub(null);
        // Nettoyer l'URL en retirant le paramètre ?highlight=X
        const url = new URL(window.location.href);
        url.searchParams.delete('highlight');
        window.history.replaceState({}, '', url.toString());
    };

    return (
        <AppLayout>
            <Head title="Accueil" />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <HeroSection />

                <div className="grid grid-cols-12 gap-6 mt-8">
                    <motion.aside
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ duration: 0.4 }}
                        className="col-span-12 lg:col-span-3 space-y-4"
                    >
                        <ProfileCard user={auth.user} />
                        <QuickActions
                            onCreatePost={() => setShowCreateModal(true)}
                            isAuthenticated={!!auth.user}
                        />
                        <PointsDeVente points={pointsDeVente} />
                    </motion.aside>

                    <motion.main
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4, delay: 0.1 }}
                        className="col-span-12 lg:col-span-6 space-y-6"
                    >
                        <CreatePostBox
                            user={auth.user}
                            onOpen={() => setShowCreateModal(true)}
                        />

                        <ShopSection featured={featuredProducts} others={otherProducts} />

                        {publications.length > 0 ? (
                            publications.map((pub, index) => (
                                <motion.div
                                    key={pub.id}
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ duration: 0.3, delay: index * 0.05 }}
                                >
                                    <PublicationCard
                                        publication={pub}
                                        currentUser={auth.user}
                                        onShare={() => setShareModalPub(pub)}
                                    />
                                </motion.div>
                            ))
                        ) : (
                            <EmptyPublications onCreate={() => setShowCreateModal(true)} />
                        )}
                    </motion.main>

                    <motion.aside
                        initial={{ opacity: 0, x: 20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ duration: 0.4, delay: 0.2 }}
                        className="col-span-12 lg:col-span-3 space-y-4"
                    >
                        <ServicesSection />
                        <PromoCard />
                    </motion.aside>
                </div>
            </div>

            {/* 🆕 Modal de la publication mise en avant */}
            {highlightModalPub && (
                <PublicationModal
                    publication={highlightModalPub}
                    currentUser={auth.user}
                    onClose={handleCloseHighlight}
                    onShare={() => setShareModalPub(highlightModalPub)}
                />
            )}

            {shareModalPub && (
                <ShareModal
                    publication={shareModalPub}
                    onClose={() => setShareModalPub(null)}
                />
            )}

            {showCreateModal && (
                <CreatePostModal
                    user={auth.user}
                    onClose={() => setShowCreateModal(false)}
                />
            )}
        </AppLayout>
    );
}

function EmptyPublications({ onCreate }: { onCreate: () => void }) {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-12 text-center">
            <div className="w-16 h-16 rounded-2xl bg-zinc-100 flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 className="text-lg font-semibold text-zinc-900 mb-1">Aucune publication</h3>
            <p className="text-sm text-zinc-500 mb-6">Soyez le premier à partager votre expérience.</p>
            <button
                onClick={onCreate}
                className="inline-flex items-center gap-2 px-4 py-2.5 bg-zinc-900 text-white text-sm font-medium rounded-lg hover:bg-zinc-800 transition-colors"
            >
                Créer une publication
            </button>
        </div>
    );
}
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
import CartButton from '@/components/dashboard/CartButton';
import CartDrawer from '@/components/dashboard/CartDrawer';
import type { Publication, Product, PointDeVente as PointDeVenteType, Cart, PageProps } from '@/types';

interface ProchainEvent {
    id: number;
    titre: string;
    type: string;
    mode_label: string;
    date_label: string;
    date_courte: string;
    image: string | null;
    extrait: string | null;
    inscriptions_ouvertes: boolean;
}

interface DashboardProps extends PageProps {
    publications: Publication[];
    highlightedPublication: Publication | null;
    featuredProducts: Product[];
    otherProducts: Product[];
    pointsDeVente: PointDeVenteType[];
    prochainEvent: ProchainEvent | null;
    cart: Cart;
}

export default function DashboardIndex() {
    const {
        publications,
        highlightedPublication,
        featuredProducts,
        otherProducts,
        pointsDeVente,
        prochainEvent,
        cart,
        auth,
    } = usePage<DashboardProps>().props;

    const cartCount = Object.values(cart ?? {}).reduce((sum, item) => sum + item.quantity, 0);
    const [showCart, setShowCart] = useState(false);
    const [shareModalPub, setShareModalPub] = useState<Publication | null>(null);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [highlightModalPub, setHighlightModalPub] = useState<Publication | null>(
        highlightedPublication
    );
    const [postModalPub, setPostModalPub] = useState<Publication | null>(null);

    // Liste locale du fil : on peut charger toutes les publications (« Voir plus »).
    const [posts, setPosts] = useState<Publication[]>(publications);
    const [encore, setEncore] = useState(publications.length >= 10);
    const [chargement, setChargement] = useState(false);

    // Nouvelles publications du serveur (sans écraser celles déjà chargées).
    useEffect(() => {
        setPosts((prev) => {
            const ids = new Set(prev.map((p) => p.id));
            const nouveaux = publications.filter((p) => !ids.has(p.id));
            return nouveaux.length ? [...nouveaux, ...prev] : prev;
        });
    }, [publications]);

    const chargerPlus = async () => {
        setChargement(true);
        try {
            const r = await fetch(`/feed/plus?offset=${posts.length}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (r.ok) {
                const d = await r.json();
                setPosts((prev) => {
                    const ids = new Set(prev.map((p) => p.id));
                    return [...prev, ...(d.publications ?? []).filter((p: Publication) => !ids.has(p.id))];
                });
                setEncore(!!d.encore);
            }
        } catch { /* silencieux */ }
        finally { setChargement(false); }
    };

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

                        {prochainEvent && <EventCard event={prochainEvent} />}

                        <ShopSection
                            featured={featuredProducts}
                            others={otherProducts}
                            onAdded={() => setShowCart(true)}
                        />

                        {posts.length > 0 ? (
                            <>
                                {posts.map((pub) => (
                                    <motion.div
                                        key={pub.id}
                                        initial={{ opacity: 0, y: 28, scale: 0.97 }}
                                        whileInView={{ opacity: 1, y: 0, scale: 1 }}
                                        viewport={{ once: true, margin: '-40px' }}
                                        transition={{ duration: 0.45, ease: [0.22, 1, 0.36, 1] }}
                                    >
                                        <PublicationCard
                                            publication={pub}
                                            currentUser={auth.user}
                                            onShare={() => setShareModalPub(pub)}
                                            onComment={() => setPostModalPub(pub)}
                                        />
                                    </motion.div>
                                ))}

                                {encore && (
                                    <div className="flex justify-center pt-2">
                                        <button
                                            onClick={chargerPlus}
                                            disabled={chargement}
                                            className="px-6 py-2.5 rounded-full bg-white border border-zinc-200 shadow-sm hover:shadow-md text-sm font-semibold text-emerald-700 hover:bg-emerald-50 transition-all disabled:opacity-60"
                                        >
                                            {chargement ? 'Chargement…' : 'Voir plus de publications'}
                                        </button>
                                    </div>
                                )}
                            </>
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

            {/* Panier visible depuis l'accueil */}
            <CartButton count={cartCount} onClick={() => setShowCart(true)} />
            {showCart && <CartDrawer cart={cart ?? {}} onClose={() => setShowCart(false)} />}

            {/* 🆕 Modal de la publication mise en avant */}
            {highlightModalPub && (
                <PublicationModal
                    publication={highlightModalPub}
                    currentUser={auth.user}
                    onClose={handleCloseHighlight}
                    onShare={() => setShareModalPub(highlightModalPub)}
                />
            )}

            {/* Modal de publication ouvert au clic « Commenter » (façon Facebook) */}
            {postModalPub && (
                <PublicationModal
                    publication={postModalPub}
                    currentUser={auth.user}
                    onClose={() => setPostModalPub(null)}
                    onShare={() => setShareModalPub(postModalPub)}
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

function EventCard({ event }: { event: ProchainEvent }) {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-sm">
            <div className="bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 flex items-center justify-between">
                <span className="text-white text-xs font-bold uppercase tracking-wide">📅 Prochain événement</span>
                <span className="text-white/90 text-xs font-semibold bg-white/20 px-2 py-0.5 rounded-full">{event.date_courte}</span>
            </div>
            <div className="sm:flex">
                <div className="sm:w-2/5 h-44 sm:h-auto bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                    {event.image
                        ? <img src={event.image} alt={event.titre} className="w-full h-full object-cover" />
                        : <span className="text-5xl">📣</span>}
                </div>
                <div className="p-5 sm:w-3/5 flex flex-col">
                    <span className="text-xs font-bold uppercase tracking-wide text-emerald-600">{event.type} · {event.mode_label}</span>
                    <h3 className="mt-1 font-bold text-lg text-zinc-900">{event.titre}</h3>
                    <p className="mt-1 text-sm text-zinc-500">{event.date_label}</p>
                    {event.extrait && <p className="mt-2 text-sm text-zinc-600 line-clamp-2">{event.extrait}</p>}
                    <a href={`/events/${event.id}`}
                        className="mt-auto pt-4 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition-colors">
                        {event.inscriptions_ouvertes ? "S'inscrire" : 'Voir le détail'}
                    </a>
                </div>
            </div>
        </div>
    );
}

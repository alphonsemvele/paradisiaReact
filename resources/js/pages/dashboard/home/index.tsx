import { Head, usePage } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import { motion } from 'framer-motion';
import AppLayout from '@/components/layouts/AppLayout';
import CreatePostBox from '@/components/dashboard/CreatePostBox';
import PublicationCard from '@/components/dashboard/PublicationCard';
import ShareModal from '@/components/dashboard/ShareModal';
import CreatePostModal from '@/components/dashboard/CreatePostModal';
import PublicationModal from '@/components/dashboard/PublicationModal';
import CartButton from '@/components/dashboard/CartButton';
import CartDrawer from '@/components/dashboard/CartDrawer';
import type { Publication, Cart, PageProps } from '@/types';

interface DashboardProps extends PageProps {
    publications: Publication[];
    highlightedPublication: Publication | null;
    cart: Cart;
}

/**
 * Accueil épuré, façon fil d'actualité : on publie et on parcourt les
 * publications, sur mobile comme sur desktop. Les blocs marketing (boutique,
 * formations, événements, points de vente…) vivent dans leurs pages / le menu.
 */
export default function DashboardIndex() {
    const { publications, highlightedPublication, cart, auth } = usePage<DashboardProps>().props;

    const cartCount = Object.values(cart ?? {}).reduce((sum, item) => sum + item.quantity, 0);
    const [showCart, setShowCart] = useState(false);
    const [shareModalPub, setShareModalPub] = useState<Publication | null>(null);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [highlightModalPub, setHighlightModalPub] = useState<Publication | null>(highlightedPublication);
    const [postModalPub, setPostModalPub] = useState<Publication | null>(null);

    // Fil local : on peut charger toutes les publications (« Voir plus »).
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

    const chargementRef = useRef(false);
    const chargerPlus = async () => {
        if (chargementRef.current) return;
        chargementRef.current = true;
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
        finally { setChargement(false); chargementRef.current = false; }
    };

    // Chargement automatique au défilement (infinite scroll).
    const sentinelle = useRef<HTMLDivElement>(null);
    useEffect(() => {
        if (!encore) return;
        const el = sentinelle.current;
        if (!el) return;
        const obs = new IntersectionObserver(
            (entries) => { if (entries[0]?.isIntersecting) chargerPlus(); },
            { rootMargin: '800px' },
        );
        obs.observe(el);
        return () => obs.disconnect();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [encore, posts.length]);

    useEffect(() => {
        if (highlightedPublication) setHighlightModalPub(highlightedPublication);
    }, [highlightedPublication]);

    const handleCloseHighlight = () => {
        setHighlightModalPub(null);
        const url = new URL(window.location.href);
        url.searchParams.delete('highlight');
        window.history.replaceState({}, '', url.toString());
    };

    return (
        <AppLayout masquerFooterMobile={encore}>
            <Head title="Accueil" />

            <div className="max-w-xl mx-auto px-3 sm:px-4 py-4 sm:py-6 space-y-4">
                <CreatePostBox user={auth.user} onOpen={() => setShowCreateModal(true)} />

                {posts.length > 0 ? (
                    <>
                        {posts.map((pub) => (
                            <motion.div
                                key={pub.id}
                                initial={{ opacity: 0, y: 24 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true, margin: '-40px' }}
                                transition={{ duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
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
                            <div ref={sentinelle} className="flex justify-center pt-2 pb-4">
                                {chargement ? (
                                    <div className="flex items-center gap-2 text-sm text-zinc-500 py-2">
                                        <span className="w-4 h-4 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin" />
                                        Chargement…
                                    </div>
                                ) : (
                                    <button onClick={chargerPlus} className="text-sm font-medium text-zinc-400 hover:text-emerald-600 transition-colors">
                                        Charger la suite
                                    </button>
                                )}
                            </div>
                        )}
                    </>
                ) : (
                    <EmptyPublications onCreate={() => setShowCreateModal(true)} />
                )}
            </div>

            {/* Panier (des articles peuvent y rester même hors boutique) */}
            {cartCount > 0 && <CartButton count={cartCount} onClick={() => setShowCart(true)} />}
            {showCart && <CartDrawer cart={cart ?? {}} onClose={() => setShowCart(false)} />}

            {highlightModalPub && (
                <PublicationModal
                    publication={highlightModalPub}
                    currentUser={auth.user}
                    onClose={handleCloseHighlight}
                    onShare={() => setShareModalPub(highlightModalPub)}
                />
            )}

            {postModalPub && (
                <PublicationModal
                    publication={postModalPub}
                    currentUser={auth.user}
                    onClose={() => setPostModalPub(null)}
                    onShare={() => setShareModalPub(postModalPub)}
                />
            )}

            {shareModalPub && (
                <ShareModal publication={shareModalPub} onClose={() => setShareModalPub(null)} />
            )}

            {showCreateModal && (
                <CreatePostModal user={auth.user} onClose={() => setShowCreateModal(false)} />
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
            <p className="text-sm text-zinc-500 mb-6">Sois le premier à partager ton expérience.</p>
            <button
                onClick={onCreate}
                className="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors"
            >
                Créer une publication
            </button>
        </div>
    );
}

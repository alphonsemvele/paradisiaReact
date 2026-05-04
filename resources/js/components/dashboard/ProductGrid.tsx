import { Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Search } from 'lucide-react';
import ProductCard from './ProductCard';
import ProductListItem from './ProductListItem';
import type { PaginatedProducts, ShopProduct } from '@/types';

interface Props {
    products: PaginatedProducts;
    viewMode: 'grid' | 'list';
    onSelectProduct: (product: ShopProduct) => void;
}

export default function ProductGrid({ products, viewMode, onSelectProduct }: Props) {
    if (products.data.length === 0) {
        return <EmptyState />;
    }

    return (
        <>
            {viewMode === 'grid' ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    {products.data.map((product, index) => (
                        <motion.div
                            key={product.id}
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.3, delay: index * 0.03 }}
                        >
                            <ProductCard
                                product={product}
                                onView={() => onSelectProduct(product)}
                            />
                        </motion.div>
                    ))}
                </div>
            ) : (
                <div className="space-y-4">
                    {products.data.map((product, index) => (
                        <motion.div
                            key={product.id}
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.3, delay: index * 0.03 }}
                        >
                            <ProductListItem
                                product={product}
                                onView={() => onSelectProduct(product)}
                            />
                        </motion.div>
                    ))}
                </div>
            )}

            <Pagination products={products} />
        </>
    );
}

function Pagination({ products }: { products: PaginatedProducts }) {
    if (products.last_page <= 1) return null;

    return (
        <div className="flex items-center justify-center gap-1 mt-10">
            {products.links.map((link, i) => {
                if (!link.url) {
                    return (
                        <span
                            key={i}
                            className="px-3 py-2 text-sm text-zinc-400"
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    );
                }

                return (
                    <Link
                        key={i}
                        href={link.url}
                        preserveScroll
                        className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors ${
                            link.active
                                ? 'bg-zinc-900 text-white'
                                : 'text-zinc-700 hover:bg-zinc-100'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                );
            })}
        </div>
    );
}

function EmptyState() {
    return (
        <div className="bg-white rounded-2xl border border-zinc-200 p-12 text-center">
            <div className="w-16 h-16 rounded-2xl bg-zinc-100 flex items-center justify-center mx-auto mb-4">
                <Search className="w-7 h-7 text-zinc-400" />
            </div>
            <h3 className="text-lg font-semibold text-zinc-900 mb-1">
                Aucun produit trouvé
            </h3>
            <p className="text-sm text-zinc-500 mb-6">
                Essayez de modifier vos critères de recherche.
            </p>
            <button
                onClick={() => router.get('/shop', {})}
                className="inline-flex items-center gap-2 px-4 py-2.5 bg-zinc-900 text-white text-sm font-medium rounded-lg hover:bg-zinc-800 transition-colors"
            >
                Voir tous les produits
            </button>
        </div>
    );
}
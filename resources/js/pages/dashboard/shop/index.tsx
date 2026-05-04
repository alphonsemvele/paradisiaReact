import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { motion } from 'framer-motion';
import AppLayout from '@/components/layouts/AppLayout';
import ShopHero from '@/components/dashboard/ShopHero';
import FilterSidebar from '@/components/dashboard/FilterSidebar';
import MobileFilters from '@/components/dashboard/MobileFilters';
import ProductToolbar from '@/components/dashboard/ProductToolbar';
import ProductGrid from '@/components/dashboard/ProductGrid';
import ProductModal from '@/components/dashboard/ProductModal';
import CartDrawer from '@/components/dashboard/CartDrawer';
import CartButton from '@/components/dashboard/CartButton';
import type { PaginatedProducts, Category, Cart, ShopFilters, ShopProduct, PageProps } from '@/types';

interface ShopProps extends PageProps {
    products: PaginatedProducts;
    categories: Category[];
    cart: Cart;
    filters: ShopFilters;
}

export default function ShopIndex() {
    const { products, categories, cart, filters } = usePage<ShopProps>().props;

    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const [showMobileFilters, setShowMobileFilters] = useState(false);
    const [selectedProduct, setSelectedProduct] = useState<ShopProduct | null>(null);
    const [showCart, setShowCart] = useState(false);

    const cartCount = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);

    return (
        <AppLayout>
            <Head title="Boutique" />

            <ShopHero filters={filters} totalProducts={products.total} />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="flex flex-col lg:flex-row gap-8">
                    <aside className="hidden lg:block w-72 flex-shrink-0">
                        <div className="sticky top-20">
                            <FilterSidebar
                                categories={categories}
                                filters={filters}
                                totalProducts={products.total}
                            />
                        </div>
                    </aside>

                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4 }}
                        className="flex-1 min-w-0"
                    >
                        <ProductToolbar
                            total={products.total}
                            viewMode={viewMode}
                            onViewModeChange={setViewMode}
                            filters={filters}
                            onOpenMobileFilters={() => setShowMobileFilters(true)}
                        />

                        {showMobileFilters && (
                            <MobileFilters
                                categories={categories}
                                filters={filters}
                                onClose={() => setShowMobileFilters(false)}
                            />
                        )}

                        <ProductGrid
                            products={products}
                            viewMode={viewMode}
                            onSelectProduct={setSelectedProduct}
                        />
                    </motion.div>
                </div>
            </div>

            <CartButton count={cartCount} onClick={() => setShowCart(true)} />

            {selectedProduct && (
                <ProductModal
                    product={selectedProduct}
                    onClose={() => setSelectedProduct(null)}
                />
            )}

            {showCart && (
                <CartDrawer cart={cart} onClose={() => setShowCart(false)} />
            )}
        </AppLayout>
    );
}
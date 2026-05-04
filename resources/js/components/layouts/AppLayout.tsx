import { ReactNode, useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Search,
    Bell,
    Menu,
    X,
    Home,
    ShoppingBag,
    TrendingUp,
    Users,
    LogOut,
    User as UserIcon,
} from 'lucide-react';
import type { PageProps } from '@/types';

interface Props {
    children: ReactNode;
}

export default function AppLayout({ children }: Props) {
    const { auth, url } = usePage<PageProps>().props as any;
    const currentPath = usePage().url;

    const [scrolled, setScrolled] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 10);
        window.addEventListener('scroll', onScroll);
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    // Fermer le menu user au clic extérieur
    useEffect(() => {
        const handleClickOutside = () => setUserMenuOpen(false);
        if (userMenuOpen) {
            document.addEventListener('click', handleClickOutside);
            return () => document.removeEventListener('click', handleClickOutside);
        }
    }, [userMenuOpen]);

    const userAvatar = auth?.user
        ? auth.user.photo ||
          `https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user.name)}&background=10b981&color=fff&size=64`
        : null;

    const handleLogout = () => {
        router.post('/logout');
    };

    const isActive = (path: string) => {
        if (path === '/') return currentPath === '/';
        return currentPath.startsWith(path);
    };

    return (
        <div className="min-h-screen bg-stone-50">
            {/* ============== NAVBAR ============== */}
            <nav
                className={`sticky top-0 z-40 transition-all duration-300 ${
                    scrolled
                        ? 'bg-white/80 backdrop-blur-xl border-b border-zinc-200/60 shadow-sm'
                        : 'bg-white border-b border-zinc-100'
                }`}
            >
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between h-16">
                        {/* Logo */}
                        <Link href="/" className="flex items-center gap-2.5 group">
                            <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shadow-sm shadow-emerald-600/20 group-hover:shadow-md group-hover:shadow-emerald-600/30 transition-all">
                                <span className="text-white font-bold text-sm">P</span>
                            </div>
                            <span className="font-bold text-zinc-900 text-lg tracking-tight">
                                Paradisia
                            </span>
                        </Link>

                        {/* Nav Links Desktop */}
                        <div className="hidden md:flex items-center gap-1">
                            <NavLink
                                href="/"
                                icon={<Home className="w-4 h-4" />}
                                label="Accueil"
                                active={isActive('/')}
                            />
                            <NavLink
                                href="/shop"
                                icon={<ShoppingBag className="w-4 h-4" />}
                                label="Boutique"
                                active={isActive('/shop')}
                            />
                            <NavLink
                                href="/invest"
                                icon={<TrendingUp className="w-4 h-4" />}
                                label="Investir"
                                active={isActive('/invest')}
                            />
                            <NavLink
                                href="/community"
                                icon={<Users className="w-4 h-4" />}
                                label="Communauté"
                                active={isActive('/community')}
                            />
                        </div>

                        {/* Right side */}
                        <div className="flex items-center gap-2">
                            {/* Search */}
                            <button
                                className="p-2 hover:bg-zinc-100 rounded-lg transition-colors hidden sm:flex"
                                aria-label="Rechercher"
                            >
                                <Search className="w-5 h-5 text-zinc-600" />
                            </button>

                            {auth?.user ? (
                                <>
                                    {/* Notifications */}
                                    <button
                                        className="relative p-2 hover:bg-zinc-100 rounded-lg transition-colors"
                                        aria-label="Notifications"
                                    >
                                        <Bell className="w-5 h-5 text-zinc-600" />
                                        <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-orange-500 rounded-full ring-2 ring-white" />
                                    </button>

                                    {/* Avatar + Menu */}
                                    <div className="relative ml-2">
                                        <button
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                setUserMenuOpen(!userMenuOpen);
                                            }}
                                            className="flex items-center gap-2 p-1 rounded-full hover:ring-2 hover:ring-emerald-200 transition-all"
                                        >
                                            <img
                                                src={userAvatar!}
                                                alt={auth.user.name}
                                                className="w-9 h-9 rounded-full object-cover ring-2 ring-white shadow-sm"
                                            />
                                        </button>

                                        {/* Dropdown menu */}
                                        <AnimatePresence>
                                            {userMenuOpen && (
                                                <motion.div
                                                    initial={{ opacity: 0, y: -10 }}
                                                    animate={{ opacity: 1, y: 0 }}
                                                    exit={{ opacity: 0, y: -10 }}
                                                    transition={{ duration: 0.15 }}
                                                    className="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-lg border border-zinc-200 overflow-hidden"
                                                >
                                                    <div className="p-3 border-b border-zinc-100">
                                                        <p className="text-sm font-semibold text-zinc-900 truncate">
                                                            {auth.user.name}
                                                        </p>
                                                        <p className="text-xs text-zinc-500 truncate">
                                                            {auth.user.email}
                                                        </p>
                                                    </div>
                                                    <div className="py-1">
                                                        <Link
                                                            href="/profile"
                                                            className="flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 transition-colors"
                                                        >
                                                            <UserIcon className="w-4 h-4" />
                                                            Mon profil
                                                        </Link>
                                                        <button
                                                            onClick={handleLogout}
                                                            className="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                                        >
                                                            <LogOut className="w-4 h-4" />
                                                            Déconnexion
                                                        </button>
                                                    </div>
                                                </motion.div>
                                            )}
                                        </AnimatePresence>
                                    </div>
                                </>
                            ) : (
                                /* ============== Boutons Login/Register (LIENS, PAS MODAL) ============== */
                                <div className="hidden sm:flex items-center gap-2">
                                    <Link
                                        href="/login"
                                        className="px-4 py-2 text-sm font-medium text-zinc-700 hover:text-zinc-900 transition-colors"
                                    >
                                        Connexion
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="px-4 py-2 text-sm font-medium bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors shadow-sm"
                                    >
                                        S'inscrire
                                    </Link>
                                </div>
                            )}

                            {/* Mobile menu button */}
                            <button
                                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                                className="md:hidden p-2 hover:bg-zinc-100 rounded-lg transition-colors"
                                aria-label="Menu"
                            >
                                {mobileMenuOpen ? (
                                    <X className="w-5 h-5" />
                                ) : (
                                    <Menu className="w-5 h-5" />
                                )}
                            </button>
                        </div>
                    </div>
                </div>

                {/* ============== Mobile menu ============== */}
                <AnimatePresence>
                    {mobileMenuOpen && (
                        <motion.div
                            initial={{ opacity: 0, height: 0 }}
                            animate={{ opacity: 1, height: 'auto' }}
                            exit={{ opacity: 0, height: 0 }}
                            transition={{ duration: 0.2 }}
                            className="md:hidden border-t border-zinc-200 bg-white overflow-hidden"
                        >
                            <div className="px-4 py-3 space-y-1">
                                <MobileNavLink
                                    href="/"
                                    icon={<Home className="w-4 h-4" />}
                                    label="Accueil"
                                />
                                <MobileNavLink
                                    href="/shop"
                                    icon={<ShoppingBag className="w-4 h-4" />}
                                    label="Boutique"
                                />
                                <MobileNavLink
                                    href="/invest"
                                    icon={<TrendingUp className="w-4 h-4" />}
                                    label="Investir"
                                />
                                <MobileNavLink
                                    href="/community"
                                    icon={<Users className="w-4 h-4" />}
                                    label="Communauté"
                                />

                                {/* Login/Register sur mobile (LIENS, PAS MODAL) */}
                                {!auth?.user && (
                                    <div className="pt-3 mt-3 border-t border-zinc-200 space-y-2">
                                        <Link
                                            href="/login"
                                            className="block w-full text-center px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-100 rounded-lg transition-colors"
                                        >
                                            Connexion
                                        </Link>
                                        <Link
                                            href="/register"
                                            className="block w-full text-center px-4 py-2.5 text-sm font-medium bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors"
                                        >
                                            S'inscrire
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </nav>

            {/* ============== MAIN CONTENT ============== */}
            <main>{children}</main>
        </div>
    );
}

/* ============== Composant NavLink (Desktop) ============== */
function NavLink({
    href,
    icon,
    label,
    active = false,
}: {
    href: string;
    icon: ReactNode;
    label: string;
    active?: boolean;
}) {
    return (
        <Link
            href={href}
            className={`flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                active
                    ? 'text-emerald-700 bg-emerald-50'
                    : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50'
            }`}
        >
            {icon}
            {label}
        </Link>
    );
}

/* ============== Composant MobileNavLink ============== */
function MobileNavLink({
    href,
    icon,
    label,
}: {
    href: string;
    icon: ReactNode;
    label: string;
}) {
    return (
        <Link
            href={href}
            className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-zinc-700 hover:bg-zinc-100 transition-colors"
        >
            {icon}
            {label}
        </Link>
    );
}
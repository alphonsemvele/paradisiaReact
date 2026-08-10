import { ReactNode, useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Home,
    Store,
    BarChart3,
    GraduationCap,
    Calendar,
    User,
    LogOut,
    Menu,
    X,
    ChevronDown,
    Settings,
    Shield,
    PartyPopper,
} from 'lucide-react';
import type { PageProps } from '@/types';
import SupportButton from '@/components/SupportButton';
import NotificationBell from '@/components/NotificationBell';

interface Props {
    children: ReactNode;
}

const navLinks = [
    { label: 'Accueil', href: '/', icon: Home },
    { label: 'Points de vente', href: '/points-de-vente', icon: Store },
    { label: 'Statistique', href: '/statistiques', icon: BarChart3 },
    { label: 'Formations', href: '/formations', icon: GraduationCap },
    { label: 'Événements', href: '/events', icon: Calendar },
    { label: 'Festy', href: '/festy', icon: PartyPopper },
];

export default function AppLayout({ children }: Props) {
    const { auth } = usePage<PageProps>().props as any;
    const currentPath = usePage().url;

    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);

    useEffect(() => {
        const handleClickOutside = () => setUserMenuOpen(false);
        if (userMenuOpen) {
            document.addEventListener('click', handleClickOutside);
            return () => document.removeEventListener('click', handleClickOutside);
        }
    }, [userMenuOpen]);

    const handleLogout = () => {
        router.post('/logout');
    };

    const userAvatar = auth?.user
        ? auth.user.photo ||
          `https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user.name)}&background=10b981&color=fff&size=64`
        : null;

    const isActive = (href: string) => {
        if (href === '/') return currentPath === '/';
        return currentPath.startsWith(href);
    };

    const isAdmin = auth?.user?.role === 'admin' || auth?.user?.role === 'super-admin';

    return (
        <div className="min-h-screen bg-zinc-50 flex flex-col">
            {/* ============== HEADER ============== */}
            <header className="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-zinc-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="h-16 flex items-center justify-between gap-4">
                        {/* Logo */}
                        <Link href="/" className="flex items-center gap-2.5 flex-shrink-0">
                            <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-600/20">
                                <span className="text-white font-bold text-sm">P</span>
                            </div>
                            <div className="hidden sm:block">
                                <p className="font-bold text-zinc-900 text-sm leading-tight">
                                    Paradisia
                                </p>
                                <p className="text-[10px] text-emerald-600 uppercase tracking-wider">
                                    Africa
                                </p>
                            </div>
                        </Link>

                        {/* Navigation Desktop */}
                        <nav className="hidden lg:flex items-center gap-1">
                            {navLinks.map((link) => {
                                const Icon = link.icon;
                                const active = isActive(link.href);
                                return (
                                    <Link
                                        key={link.href}
                                        href={link.href}
                                        className={`flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                                            active
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900'
                                        }`}
                                    >
                                        <Icon className="w-4 h-4" />
                                        {link.label}
                                    </Link>
                                );
                            })}
                        </nav>

                        {/* Actions à droite */}
                        <div className="flex items-center gap-2">
                            {auth?.user ? (
                                <>
                                    {/* Cloche de notifications */}
                                    <NotificationBell />

                                    {/* 🆕 Bouton Administration (visible si admin) */}
                                    {isAdmin && (
                                        <Link
                                            href="/admin"
                                            className="hidden sm:flex items-center gap-1.5 px-3 py-2 bg-gradient-to-br from-emerald-500 to-emerald-700 hover:from-emerald-600 hover:to-emerald-800 text-white text-sm font-medium rounded-lg shadow-sm shadow-emerald-600/20 transition-all"
                                        >
                                            <Shield className="w-4 h-4" />
                                            <span className="hidden md:inline">Administration</span>
                                            <span className="md:hidden">Admin</span>
                                        </Link>
                                    )}

                                    {/* User Menu */}
                                    <div className="relative">
                                        <button
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                setUserMenuOpen(!userMenuOpen);
                                            }}
                                            className="flex items-center gap-2 p-1 pr-2 rounded-full hover:bg-zinc-100 transition-colors"
                                        >
                                            <img
                                                src={userAvatar!}
                                                alt={auth.user.name}
                                                className="w-8 h-8 rounded-full object-cover ring-2 ring-white shadow-sm"
                                            />
                                            <span className="hidden sm:inline text-sm font-medium text-zinc-700 max-w-[100px] truncate">
                                                {auth.user.name}
                                            </span>
                                            <ChevronDown className="hidden sm:inline w-4 h-4 text-zinc-400" />
                                        </button>

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
                                                        {isAdmin && (
                                                            <span className="inline-block mt-1.5 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded uppercase tracking-wider">
                                                                {auth.user.role}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div className="py-1">
                                                        <Link
                                                            href="/profile"
                                                            className="flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50"
                                                        >
                                                            <User className="w-4 h-4" />
                                                            Mon profil
                                                        </Link>
                                                        <Link
                                                            href="/settings"
                                                            className="flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50"
                                                        >
                                                            <Settings className="w-4 h-4" />
                                                            Paramètres
                                                        </Link>
                                                        {isAdmin && (
                                                            <Link
                                                                href="/admin"
                                                                className="flex items-center gap-2 px-3 py-2 text-sm text-emerald-700 hover:bg-emerald-50 font-medium sm:hidden"
                                                            >
                                                                <Shield className="w-4 h-4" />
                                                                Administration
                                                            </Link>
                                                        )}
                                                        <div className="border-t border-zinc-100" />
                                                        <button
                                                            onClick={handleLogout}
                                                            className="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50"
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
                                <div className="flex items-center gap-2">
                                    <Link
                                        href="/login"
                                        className="px-3 py-2 text-sm font-medium text-zinc-700 hover:text-zinc-900 transition-colors"
                                    >
                                        Se connecter
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        S'inscrire
                                    </Link>
                                </div>
                            )}

                            {/* Bouton menu mobile */}
                            <button
                                onClick={() => setMobileMenuOpen(true)}
                                className="lg:hidden p-2 hover:bg-zinc-100 rounded-lg"
                                aria-label="Menu"
                            >
                                <Menu className="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            {/* ============== MENU MOBILE ============== */}
            <AnimatePresence>
                {mobileMenuOpen && (
                    <>
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            onClick={() => setMobileMenuOpen(false)}
                            className="lg:hidden fixed inset-0 bg-black/50 z-50"
                        />

                        <motion.aside
                            initial={{ x: '100%' }}
                            animate={{ x: 0 }}
                            exit={{ x: '100%' }}
                            transition={{ type: 'tween', duration: 0.25 }}
                            className="lg:hidden fixed inset-y-0 right-0 w-72 bg-white z-50 flex flex-col"
                        >
                            <div className="h-16 px-5 flex items-center justify-between border-b border-zinc-200">
                                <Link
                                    href="/"
                                    className="flex items-center gap-2.5"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center">
                                        <span className="text-white font-bold text-sm">P</span>
                                    </div>
                                    <span className="font-bold text-zinc-900">Paradisia</span>
                                </Link>
                                <button
                                    onClick={() => setMobileMenuOpen(false)}
                                    className="p-1.5 hover:bg-zinc-100 rounded-lg"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>

                            <nav className="flex-1 overflow-y-auto p-3 space-y-1">
                                {navLinks.map((link) => {
                                    const Icon = link.icon;
                                    const active = isActive(link.href);
                                    return (
                                        <Link
                                            key={link.href}
                                            href={link.href}
                                            onClick={() => setMobileMenuOpen(false)}
                                            className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                                                active
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : 'text-zinc-700 hover:bg-zinc-100'
                                            }`}
                                        >
                                            <Icon className="w-5 h-5" />
                                            {link.label}
                                        </Link>
                                    );
                                })}

                                {/* 🆕 Bouton Admin (mobile, plus visible) */}
                                {isAdmin && (
                                    <>
                                        <div className="my-3 border-t border-zinc-200" />
                                        <Link
                                            href="/admin"
                                            onClick={() => setMobileMenuOpen(false)}
                                            className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-white bg-gradient-to-br from-emerald-500 to-emerald-700 hover:from-emerald-600 hover:to-emerald-800 shadow-sm shadow-emerald-600/20"
                                        >
                                            <Shield className="w-5 h-5" />
                                            Administration
                                        </Link>
                                    </>
                                )}
                            </nav>

                            {auth?.user && (
                                <div className="p-3 border-t border-zinc-200">
                                    <div className="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg mb-2">
                                        <img
                                            src={userAvatar!}
                                            alt={auth.user.name}
                                            className="w-9 h-9 rounded-full object-cover"
                                        />
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-semibold text-zinc-900 truncate">
                                                {auth.user.name}
                                            </p>
                                            <p className="text-xs text-zinc-500 truncate">
                                                {auth.user.email}
                                            </p>
                                            {isAdmin && (
                                                <span className="inline-block mt-1 px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] font-semibold rounded uppercase tracking-wider">
                                                    {auth.user.role}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <button
                                        onClick={handleLogout}
                                        className="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg"
                                    >
                                        <LogOut className="w-4 h-4" />
                                        Déconnexion
                                    </button>
                                </div>
                            )}

                            {!auth?.user && (
                                <div className="p-3 border-t border-zinc-200 space-y-2">
                                    <Link
                                        href="/login"
                                        onClick={() => setMobileMenuOpen(false)}
                                        className="block w-full text-center px-4 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-sm font-medium rounded-lg"
                                    >
                                        Se connecter
                                    </Link>
                                    <Link
                                        href="/register"
                                        onClick={() => setMobileMenuOpen(false)}
                                        className="block w-full text-center px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg"
                                    >
                                        S'inscrire
                                    </Link>
                                </div>
                            )}
                        </motion.aside>
                    </>
                )}
            </AnimatePresence>

            {/* ============== CONTENT ============== */}
            <main className="flex-1">{children}</main>

            {/* ============== FOOTER ============== */}
            <footer className="bg-zinc-900 text-zinc-400 mt-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div>
                            <div className="flex items-center gap-2.5 mb-4">
                                <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center">
                                    <span className="text-white font-bold text-sm">P</span>
                                </div>
                                <div>
                                    <p className="font-bold text-white">Paradisia</p>
                                    <p className="text-[10px] text-emerald-400 uppercase tracking-wider">
                                        Africa
                                    </p>
                                </div>
                            </div>
                            <p className="text-sm text-zinc-400">
                                Le goût authentique de l'Afrique, fraîchement préparé pour vous 🌴
                            </p>
                        </div>

                        <div>
                            <h4 className="font-semibold text-white mb-3 text-sm">Navigation</h4>
                            <ul className="space-y-2 text-sm">
                                {navLinks.map((link) => (
                                    <li key={link.href}>
                                        <Link
                                            href={link.href}
                                            className="hover:text-emerald-400 transition-colors"
                                        >
                                            {link.label}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div>
                            <h4 className="font-semibold text-white mb-3 text-sm">Contact</h4>
                            <ul className="space-y-2 text-sm">
                                <li>
                                    <a
                                        href="https://wa.me/237687984282"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="hover:text-emerald-400 transition-colors"
                                    >
                                        +237 687 98 42 82
                                    </a>
                                </li>
                                <li>contact@paradisia-africa.com</li>
                                <li>Douala / Yaoundé, Cameroun</li>
                            </ul>
                        </div>

                        <div>
                            <h4 className="font-semibold text-white mb-3 text-sm">Légal</h4>
                            <ul className="space-y-2 text-sm">
                                <li>
                                    <a href="#" className="hover:text-emerald-400 transition-colors">
                                        Mentions légales
                                    </a>
                                </li>
                                <li>
                                    <a href="#" className="hover:text-emerald-400 transition-colors">
                                        CGU
                                    </a>
                                </li>
                                <li>
                                    <a href="#" className="hover:text-emerald-400 transition-colors">
                                        Confidentialité
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="border-t border-zinc-800 mt-8 pt-6 text-center text-xs">
                        <p>© {new Date().getFullYear()} Paradisia Africa. Tous droits réservés.</p>
                    </div>
                </div>
            </footer>

            {/* Assistance : présente sur toutes les pages du site */}
            <SupportButton />
        </div>
    );
}
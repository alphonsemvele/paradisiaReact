import { ReactNode, useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    LayoutDashboard,
    Users,
    Eye,
    Package,
    FolderTree,
    FileText,
    TrendingUp,
    DollarSign,
    ShoppingCart,
    ShoppingBag,
    Receipt,
    Settings,
    LogOut,
    Menu,
    X,
    Bell,
    Search,
    ChevronDown,
    ExternalLink,
    Store,
} from 'lucide-react';
import type { PageProps } from '@/types';

interface Props {
    children: ReactNode;
    title?: string;
}

interface MenuItem {
    label: string;
    href: string;
    icon: any;
    badge?: number | string;
}

interface MenuSection {
    title: string;
    items: MenuItem[];
}

const menuSections: MenuSection[] = [
    {
        title: 'Général',
        items: [
            { label: 'Tableau de bord', href: '/admin', icon: LayoutDashboard },
            { label: 'Visiteurs', href: '/admin/visitors', icon: Eye },
        ],
    },
    {
        title: 'Communauté',
        items: [
            { label: 'Utilisateurs', href: '/admin/users', icon: Users },
            { label: 'Publications', href: '/admin/publications', icon: FileText },
        ],
    },
    {
        title: 'Boutique',
        items: [
            { label: 'Catégories', href: '/admin/categories', icon: FolderTree },
            { label: 'Produits', href: '/admin/products', icon: Package },
        ],
    },
    {
        title: 'Ventes',
        items: [
            { label: 'Statistiques', href: '/admin/sales', icon: TrendingUp },
            { label: 'Liste des ventes', href: '/admin/sales/list', icon: Receipt },
            { label: 'Nouvelle vente', href: '/admin/sales/create', icon: ShoppingBag },
        ],
    },
    {
        title: 'Localisation',
        items: [
            { label: 'Points de vente', href: '/admin/points-de-vente', icon: Store },
        ],
    },
    {
        title: 'Business',
        items: [
            { label: 'Investissements', href: '/admin/investments', icon: DollarSign },
        ],
    },
    {
        title: 'Système',
        items: [
            { label: 'Paramètres', href: '/admin/settings', icon: Settings },
        ],
    },
];

export default function AdminLayout({ children, title = 'Administration' }: Props) {
    const { auth } = usePage<PageProps>().props as any;
    const currentPath = usePage().url;

    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);

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
        if (path === '/admin') return currentPath === '/admin';
        // Cas spécial pour /admin/sales : ne pas activer si on est sur /admin/sales/list ou /admin/sales/create
        if (path === '/admin/sales') {
            return currentPath === '/admin/sales';
        }
        return currentPath.startsWith(path);
    };

    return (
        <div className="min-h-screen bg-zinc-50 flex">
            {/* ============== SIDEBAR DESKTOP ============== */}
            <aside className="hidden lg:flex flex-col w-64 bg-zinc-900 text-zinc-100 fixed inset-y-0 left-0 z-30">
                {/* Logo */}
                <div className="h-16 px-6 flex items-center border-b border-zinc-800">
                    <Link href="/admin" className="flex items-center gap-2.5">
                        <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-600/20">
                            <span className="text-white font-bold text-sm">P</span>
                        </div>
                        <div>
                            <p className="font-bold text-white text-sm leading-tight">Paradisia</p>
                            <p className="text-[10px] text-zinc-400 uppercase tracking-wider">
                                Admin
                            </p>
                        </div>
                    </Link>
                </div>

                {/* Navigation */}
                <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-6">
                    {menuSections.map((section) => (
                        <div key={section.title}>
                            <h3 className="px-3 mb-2 text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">
                                {section.title}
                            </h3>
                            <ul className="space-y-0.5">
                                {section.items.map((item) => {
                                    const Icon = item.icon;
                                    const active = isActive(item.href);
                                    return (
                                        <li key={item.href}>
                                            <Link
                                                href={item.href}
                                                className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors ${
                                                    active
                                                        ? 'bg-emerald-500/15 text-emerald-400 font-medium'
                                                        : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200'
                                                }`}
                                            >
                                                <Icon className="w-4 h-4 flex-shrink-0" />
                                                <span className="flex-1">{item.label}</span>
                                                {item.badge && (
                                                    <span className="text-xs px-1.5 py-0.5 bg-emerald-500 text-white rounded">
                                                        {item.badge}
                                                    </span>
                                                )}
                                            </Link>
                                        </li>
                                    );
                                })}
                            </ul>
                        </div>
                    ))}
                </nav>

                {/* Footer sidebar */}
                <div className="p-3 border-t border-zinc-800">
                    <Link
                        href="/"
                        className="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200 transition-colors"
                    >
                        <ExternalLink className="w-4 h-4" />
                        <span>Voir le site</span>
                    </Link>
                </div>
            </aside>

            {/* ============== SIDEBAR MOBILE ============== */}
            <AnimatePresence>
                {sidebarOpen && (
                    <>
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            onClick={() => setSidebarOpen(false)}
                            className="lg:hidden fixed inset-0 bg-black/50 z-40"
                        />

                        <motion.aside
                            initial={{ x: -280 }}
                            animate={{ x: 0 }}
                            exit={{ x: -280 }}
                            transition={{ type: 'tween', duration: 0.25 }}
                            className="lg:hidden fixed inset-y-0 left-0 w-64 bg-zinc-900 text-zinc-100 z-50 flex flex-col"
                        >
                            <div className="h-16 px-6 flex items-center justify-between border-b border-zinc-800">
                                <Link href="/admin" className="flex items-center gap-2.5">
                                    <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center">
                                        <span className="text-white font-bold text-sm">P</span>
                                    </div>
                                    <span className="font-bold text-white">Admin</span>
                                </Link>
                                <button
                                    onClick={() => setSidebarOpen(false)}
                                    className="p-1.5 hover:bg-zinc-800 rounded-lg"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>

                            <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-6">
                                {menuSections.map((section) => (
                                    <div key={section.title}>
                                        <h3 className="px-3 mb-2 text-[10px] font-semibold text-zinc-500 uppercase tracking-wider">
                                            {section.title}
                                        </h3>
                                        <ul className="space-y-0.5">
                                            {section.items.map((item) => {
                                                const Icon = item.icon;
                                                const active = isActive(item.href);
                                                return (
                                                    <li key={item.href}>
                                                        <Link
                                                            href={item.href}
                                                            onClick={() => setSidebarOpen(false)}
                                                            className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors ${
                                                                active
                                                                    ? 'bg-emerald-500/15 text-emerald-400 font-medium'
                                                                    : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200'
                                                            }`}
                                                        >
                                                            <Icon className="w-4 h-4" />
                                                            <span>{item.label}</span>
                                                        </Link>
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    </div>
                                ))}
                            </nav>
                        </motion.aside>
                    </>
                )}
            </AnimatePresence>

            {/* ============== MAIN CONTENT ============== */}
            <div className="flex-1 lg:ml-64 min-w-0 flex flex-col">
                {/* ============== TOPBAR ============== */}
                <header className="sticky top-0 z-20 bg-white/80 backdrop-blur-xl border-b border-zinc-200">
                    <div className="h-16 px-4 lg:px-8 flex items-center justify-between gap-4">
                        <button
                            onClick={() => setSidebarOpen(true)}
                            className="lg:hidden p-2 hover:bg-zinc-100 rounded-lg"
                        >
                            <Menu className="w-5 h-5" />
                        </button>

                        <h1 className="text-lg font-semibold text-zinc-900 truncate">{title}</h1>

                        <div className="hidden md:flex flex-1 max-w-md mx-4">
                            <div className="relative w-full">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                                <input
                                    type="search"
                                    placeholder="Rechercher..."
                                    className="w-full pl-10 pr-4 py-2 bg-zinc-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
                                />
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <button
                                className="relative p-2 hover:bg-zinc-100 rounded-lg"
                                aria-label="Notifications"
                            >
                                <Bell className="w-5 h-5 text-zinc-600" />
                                <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-orange-500 rounded-full ring-2 ring-white" />
                            </button>

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
                                        alt={auth?.user?.name}
                                        className="w-8 h-8 rounded-full object-cover ring-2 ring-white shadow-sm"
                                    />
                                    <span className="hidden sm:inline text-sm font-medium text-zinc-700">
                                        {auth?.user?.name}
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
                                                    {auth?.user?.name}
                                                </p>
                                                <p className="text-xs text-zinc-500 truncate">
                                                    {auth?.user?.email}
                                                </p>
                                                <span className="inline-block mt-1.5 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded uppercase tracking-wider">
                                                    {auth?.user?.role || 'Admin'}
                                                </span>
                                            </div>
                                            <div className="py-1">
                                                <Link
                                                    href="/"
                                                    className="flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50"
                                                >
                                                    <ExternalLink className="w-4 h-4" />
                                                    Voir le site
                                                </Link>
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
                        </div>
                    </div>
                </header>

                {/* ============== CONTENT ============== */}
                <main className="flex-1 p-4 lg:p-8">{children}</main>
            </div>
        </div>
    );
}
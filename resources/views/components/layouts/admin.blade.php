<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PARADISIA Admin - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }

        .animate-slide-in { animation: slideInLeft 0.6s ease-out; }
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

        .sidebar-gradient {
            background: linear-gradient(180deg, #059669 0%, #10b981 50%, #34d399 100%);
        }

        .nav-link {
            transition: all 0.3s ease;
            border-radius: 12px;
            margin: 4px 12px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 0.65rem;
            font-weight: bold;
            border-radius: 9999px;
            width: 1.2rem;
            height: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 2px white;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* Sidebar collapsed state */
        .sidebar-collapsed { width: 80px; }
        .sidebar-collapsed .sidebar-text { display: none; }
        .sidebar-collapsed .sidebar-logo-text { display: none; }
        .sidebar-collapsed .nav-link { justify-content: center; margin: 4px 8px; }
        .sidebar-collapsed .nav-link svg { margin-right: 0; }

        /* Scrollbar personnalisé */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #059669; }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-gradient fixed inset-y-0 left-0 z-40 w-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between p-5 border-b border-white/20">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-2xl">🌴</span>
                        </div>
                        <span class="sidebar-text text-xl font-bold text-white">PARADISIA</span>
                    </a>
                    <button id="toggleSidebar" class="p-2 rounded-lg hover:bg-white/10 text-white lg:hidden">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 py-6 overflow-y-auto">
                    <div class="px-3 mb-4">
                        <p class="sidebar-text text-xs font-semibold text-white/60 uppercase tracking-wider px-3 mb-2">Principal</p>
                    </div>

                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="sidebar-text">Dashboard</span>
                    </a>

                    <!-- Utilisateurs -->
                    <a href="{{ route('admin.users') }}" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="sidebar-text">Utilisateurs</span>
                    </a>

                    <!-- Publications -->
                    <a href="{{ route('admin.publications') }}" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.publications') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        <span class="sidebar-text">Publications</span>
                    </a>

                    <div class="px-3 my-4">
                        <p class="sidebar-text text-xs font-semibold text-white/60 uppercase tracking-wider px-3 mb-2">E-commerce</p>
                    </div>
                 <!-- categories -->
                    <a href="{{ route('admin.categories') }}" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="sidebar-text">Categories</span>
                    </a>
                    <!-- Produits -->
                    <a href="{{ route('admin.products') }}" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="sidebar-text">Produits</span>
                    </a>

                    <!-- Commandes -->
                    <a href="" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span class="sidebar-text">Commandes</span>
                        <span class="sidebar-text ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">12</span>
                    </a>

                    <!-- Paiements -->
                    <a href="" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="sidebar-text">Paiements</span>
                    </a>

                    <div class="px-3 my-4">
                        <p class="sidebar-text text-xs font-semibold text-white/60 uppercase tracking-wider px-3 mb-2">Services</p>
                    </div>

                    <!-- Formations -->
                    <a href="" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.formations.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="sidebar-text">Formations</span>
                    </a>

                    <!-- Événements -->
                    <a href="" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="sidebar-text">Événements</span>
                    </a>

                    <div class="px-3 my-4">
                        <p class="sidebar-text text-xs font-semibold text-white/60 uppercase tracking-wider px-3 mb-2">Système</p>
                    </div>

                    <!-- Paramètres -->
                    <a href="" class="nav-link flex items-center gap-3 px-4 py-3 text-white {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="sidebar-text">Paramètres</span>
                    </a>
                </nav>

                <!-- User Section -->
                <div class="p-4 border-t border-white/20">
                    @auth
                    <div class="flex items-center gap-3">
                        <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=ffffff&color=059669' }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="w-10 h-10 rounded-full border-2 border-white/50">
                        <div class="sidebar-text flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-white/70 truncate">{{ Auth::user()->role ?? 'Admin' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="sidebar-text">
                            @csrf
                            <button type="submit" class="p-2 rounded-lg hover:bg-white/10 text-white" title="Déconnexion">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
            <!-- Top Navigation -->
            <nav class="bg-white shadow-sm sticky top-0 z-30">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Mobile Toggle & Breadcrumb -->
                    <div class="flex items-center gap-4">
                        <button id="mobileToggle" class="p-2 rounded-lg hover:bg-gray-100 lg:hidden">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                            <p class="text-sm text-gray-500">@yield('page-description', 'Bienvenue sur votre espace admin')</p>
                        </div>
                    </div>

                    <!-- Search & Actions -->
                    <div class="flex items-center gap-4">
                        <!-- Search -->
                        <div class="hidden md:block relative">
                            <input type="search" 
                                   placeholder="Rechercher..." 
                                   class="w-64 pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        <!-- Notifications -->
                        <div class="relative">
                            <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-xl transition-all" data-dropdown-toggle="admin-notifications">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span class="notification-badge">5</span>
                            </button>

                            <!-- Dropdown -->
                            <div class="z-50 hidden bg-white rounded-xl shadow-lg w-80 border border-gray-100" id="admin-notifications">
                                <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                    <span class="font-semibold text-gray-800">Notifications</span>
                                    <span class="text-xs text-green-600 cursor-pointer hover:underline">Tout marquer lu</span>
                                </div>
                                <ul class="max-h-80 overflow-y-auto">
                                    <li class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-800">Nouvelle commande</p>
                                                <p class="text-xs text-gray-500">Commande #1234 - 45,000 FCFA</p>
                                                <p class="text-xs text-gray-400 mt-1">Il y a 5 min</p>
                                            </div>
                                            <span class="w-2 h-2 bg-green-500 rounded-full pulse-dot"></span>
                                        </div>
                                    </li>
                                    <li class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-800">Nouvel utilisateur</p>
                                                <p class="text-xs text-gray-500">Marie Dupont s'est inscrite</p>
                                                <p class="text-xs text-gray-400 mt-1">Il y a 1 heure</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div class="px-4 py-3 border-t border-gray-100">
                                    <a href="#" class="text-sm text-green-600 hover:text-green-700 font-medium">Voir toutes les notifications →</a>
                                </div>
                            </div>
                        </div>

                        <!-- Retour au site -->
                        <a href="{{ route('accueil') }}" class="hidden md:flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Voir le site
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-xl animate-slide-in">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-xl animate-slide-in">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const mobileToggle = document.getElementById('mobileToggle');
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }

        if (mobileToggle) {
            mobileToggle.addEventListener('click', openSidebar);
        }

        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', closeSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }

        // Handle responsive
        function handleResize() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    </script>
    @stack('scripts')
</body>

</html>
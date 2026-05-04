<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PARADISIA - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
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

        /* Navigation icônes centrées */
        .main-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            min-width: 70px;
            color: #6b7280;
        }

        .main-nav-item:hover {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .main-nav-item:hover .nav-icon-main {
            transform: scale(1.1);
            color: #10b981;
        }

        .main-nav-item.active {
            background-color: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }

        .main-nav-item.active .nav-icon-main {
            color: #10b981;
        }

        .main-nav-item.active .nav-text-main {
            color: #10b981;
            font-weight: 600;
        }

        .nav-icon-main {
            width: 24px;
            height: 24px;
            transition: all 0.3s ease;
        }

        .nav-text-main {
            font-size: 0.65rem;
            margin-top: 0.15rem;
            font-weight: 500;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <div class="flex-1">
            <!-- Top Navigation -->
            <nav class="bg-white shadow-md sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 lg:px-8 py-3">
                    
                    <!-- Logo -->
                    <a href="{{ route('accueil') }}" class="flex items-center gap-2 flex-shrink-0">
                        <img src="{{ asset('logo.png') }}" alt="PARADISIA" class="w-10 h-10 rounded-full">
                        <span class="text-lg font-bold text-green-600 hidden sm:block">PARADISIA</span>
                    </a>

                    <!-- Navigation Icônes Centrées -->
                    <div class="hidden md:flex items-center gap-1">
                        <!-- Accueil -->
                        <a href="{{ route('accueil') }}" class="main-nav-item {{ request()->routeIs('accueil') ? 'active' : '' }}">
                            <svg class="nav-icon-main" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="nav-text-main">Accueil</span>
                        </a>

                        <!-- Investir -->
                        <a href="{{ route('invest') ?? '#' }}" class="main-nav-item {{ request()->routeIs('invest') ? 'active' : '' }}">
                            <svg class="nav-icon-main" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="nav-text-main">Investir</span>
                        </a>

                        <!-- Mon Compte -->
                        @auth
                        <a href="{{ route('profile') ?? '#' }}" class="main-nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
                            <svg class="nav-icon-main" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="nav-text-main">Mon Compte</span>
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="main-nav-item">
                            <svg class="nav-icon-main" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="nav-text-main">Connexion</span>
                        </a>
                        @endauth

                        <!-- Paramètres -->
                        @auth
                        <a href="" class="main-nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
                            <svg class="nav-icon-main" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="nav-text-main">Paramètres</span>
                        </a>
                        @endauth
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        @auth
                            <!-- Quick Actions -->
                            <button class="p-2.5 text-gray-600 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all" title="Nouvelle transaction">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>

                            <!-- Notifications -->
                            <div class="relative">
                                <button type="button" class="relative p-2.5 text-gray-600 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all" data-dropdown-toggle="notification-dropdown">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <span class="notification-badge">3</span>
                                </button>
                                
                                <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-xl shadow-lg w-80" id="notification-dropdown">
                                    <div class="px-4 py-3 flex items-center justify-between">
                                        <span class="block text-sm font-semibold text-gray-900">Notifications</span>
                                        <span class="text-xs text-purple-600 font-medium cursor-pointer hover:underline">Tout marquer comme lu</span>
                                    </div>
                                    <ul class="py-2 max-h-96 overflow-y-auto">
                                        <li>
                                            <a href="#" class="block px-4 py-3 hover:bg-purple-50 transition-all">
                                                <div class="flex items-start space-x-3">
                                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-gray-900">Paiement réussi</p>
                                                        <p class="text-xs text-gray-600">45,000 FCFA reçu</p>
                                                        <p class="text-xs text-gray-400 mt-1">Il y a 5 minutes</p>
                                                    </div>
                                                    <span class="w-2 h-2 bg-purple-500 rounded-full pulse-dot"></span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="px-4 py-3">
                                        <a href="" class="text-sm text-purple-600 hover:text-purple-700 font-medium">Voir toutes les notifications →</a>
                                    </div>
                                </div>
                            </div>

                            <!-- User Profile -->
                            <button type="button" class="flex items-center space-x-3 p-2 hover:bg-purple-50 rounded-xl transition-all" data-dropdown-toggle="user-dropdown">
                                @if(Auth::user()->avatar)
                                    <img class="w-10 h-10 rounded-full ring-2 ring-purple-500" src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}">
                                @else
                                    <img class="w-10 h-10 rounded-full ring-2 ring-purple-500" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=7c3aed&color=fff" alt="{{ Auth::user()->name }}">
                                @endif
                                <div class="text-left hidden lg:block">
                                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Auth::user()->role ?? 'Utilisateur' }}</p>
                                </div>
                            </button>
                            
                            <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-xl shadow-lg" id="user-dropdown">
                                <div class="px-4 py-3">
                                    <span class="block text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</span>
                                    <span class="block text-sm text-gray-500 truncate">{{ Auth::user()->email }}</span>
                                </div>
                                <ul class="py-2">
                                    <li><a href="" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mon Profil</a></li>
                                    <li><a href="" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Paramètres</a></li>
                                </ul>
                                <div class="py-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">Se déconnecter</button>
                                    </form>
                                </div>
                            </div>

                        @else
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-purple-600 transition-colors">Connexion</a>
                                <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition-all">S'inscrire</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </nav>

            <!-- Dynamic Content -->
            <main class="p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl">{{ session('error') }}</div>
                @endif

                <div id="main-content">{{ $slot }}</div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    @stack('scripts')
</body>

</html>
<x-layouts.app>
    @volt
    <div>
    <div class="w-full bg-gradient-to-br from-green-50 via-blue-50 to-yellow-50 min-h-screen">
        <!-- Animated Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute top-10 left-10 w-32 h-32 bg-green-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob"></div>
            <div class="absolute top-20 right-20 w-40 h-40 bg-yellow-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-20 left-40 w-36 h-36 bg-pink-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-4000"></div>
            <div class="absolute bottom-40 right-40 w-32 h-32 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-6000"></div>
        </div>

        <div class="relative z-10 container mx-auto px-4 py-6" style="max-width: 1600px;">
            <!-- Hero Section avec Logo et Slogan -->
            <div class="bg-gradient-to-r from-green-400 via-blue-400 to-yellow-400 rounded-3xl shadow-2xl mb-6 overflow-hidden relative">
                <div class="absolute inset-0 bg-black opacity-20"></div>
                <div class="relative z-10 p-8 text-center">
                    <div class="flex items-center justify-center mb-4">
                        <img src="{{ asset('logo.png') }}" 
                             alt="PARADISIA Logo" 
                             class="w-24 h-24 rounded-full border-4 border-white shadow-lg transform hover:scale-110 transition-transform duration-300">
                    </div>
                    <h1 class="text-5xl font-bold text-white mb-2 drop-shadow-lg">🌴 PARADISIA 🍹</h1>
                    <p class="text-xl text-white font-semibold drop-shadow-md">100% Naturel • 100% Délicieux • 100% Paradis</p>
                    <p class="text-sm text-white mt-2 opacity-90">Découvrez nos jus tropicaux et investissez dans le goût du paradis</p>
                </div>
                
                <!-- Decorative Tropical Elements -->
                <div class="absolute top-0 right-0 text-6xl opacity-30 transform rotate-12">🌺</div>
                <div class="absolute bottom-0 left-0 text-6xl opacity-30 transform -rotate-12">🥥</div>
            </div>

            <div class="grid grid-cols-12 gap-6">
                <!-- Left Sidebar - Profil & Navigation -->
                <div class="col-span-12 lg:col-span-3 space-y-4">
                    <!-- Profil Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="text-center">
                            <div class="relative inline-block">
                                <img src="https://ui-avatars.com/api/?name=Investisseur+Pro&background=10b981&color=fff&size=128" 
                                     alt="Profile" 
                                     class="w-24 h-24 rounded-full border-4 border-green-400 shadow-lg mx-auto">
                                <span class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mt-4">Investisseur Pro</h3>
                            <p class="text-sm text-gray-500">@investor_paradise</p>
                            
                            <div class="flex items-center justify-center gap-4 mt-4 text-sm">
                                <div class="text-center">
                                    <p class="font-bold text-green-600">1.2k</p>
                                    <p class="text-gray-500 text-xs">Abonnés</p>
                                </div>
                                <div class="text-center">
                                    <p class="font-bold text-blue-600">890</p>
                                    <p class="text-gray-500 text-xs">Suivis</p>
                                </div>
                                <div class="text-center">
                                    <p class="font-bold text-yellow-600">45</p>
                                    <p class="text-gray-500 text-xs">Posts</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl shadow-lg p-4 border-2 border-blue-100">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <span class="text-2xl">🚀</span>
                            Actions Rapides
                        </h4>
                        <div class="space-y-2">
                            <button class="w-full bg-gradient-to-r from-green-400 to-green-600 text-white rounded-xl py-3 px-4 font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                                <span>💰</span> Investir Maintenant
                            </button>
                            <button class="w-full bg-gradient-to-r from-blue-400 to-blue-600 text-white rounded-xl py-3 px-4 font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                                <span>📸</span> Créer un Post
                            </button>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="bg-white rounded-2xl shadow-lg p-4 border-2 border-yellow-100">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <span class="text-2xl">🏷️</span>
                            Catégories
                        </h4>
                        <div class="space-y-2">
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-green-50 transition-all group">
                                <span class="text-2xl group-hover:scale-125 transition-transform">🍊</span>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">Jus d'Orange</p>
                                    <p class="text-xs text-gray-500">245 posts</p>
                                </div>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-yellow-50 transition-all group">
                                <span class="text-2xl group-hover:scale-125 transition-transform">🍍</span>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">Jus d'Ananas</p>
                                    <p class="text-xs text-gray-500">189 posts</p>
                                </div>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 transition-all group">
                                <span class="text-2xl group-hover:scale-125 transition-transform">🍉</span>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">Jus de Pastèque</p>
                                    <p class="text-xs text-gray-500">167 posts</p>
                                </div>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-pink-50 transition-all group">
                                <span class="text-2xl group-hover:scale-125 transition-transform">🥭</span>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">Jus de Mangue</p>
                                    <p class="text-xs text-gray-500">203 posts</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Center - Feed d'actualités -->
                <div class="col-span-12 lg:col-span-6 space-y-6">
                    <!-- Section E-commerce -->
                    <div class="bg-gradient-to-r from-orange-400 via-red-400 to-pink-400 rounded-2xl shadow-2xl overflow-hidden mb-6">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                                    <span>🛒</span>
                                    Boutique PARADISIA
                                </h3>
                                <button class="bg-white text-orange-600 px-4 py-2 rounded-lg font-semibold hover:shadow-lg transition-all">
                                    Voir tout →
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Produit 1 - Cartons de jus -->
                                <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-2 cursor-pointer">
                                    <div class="relative">
                                        <img src="https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400&h=300&fit=crop" 
                                             alt="Carton de jus" 
                                             class="w-full h-48 object-cover">
                                        <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                            -20%
                                        </span>
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-bold text-gray-800 mb-2">Carton Mixte Tropical</h4>
                                        <p class="text-sm text-gray-600 mb-3">24 bouteilles - Saveurs variées</p>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-gray-400 line-through text-sm">35,000 FCFA</span>
                                                <p class="text-2xl font-bold text-green-600">28,000 FCFA</p>
                                            </div>
                                            <button class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-600 transition-all flex items-center gap-2">
                                                <span>🛍️</span>
                                                Acheter
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Produit 2 - Pack Premium -->
                                <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-2 cursor-pointer">
                                    <div class="relative">
                                        <img src="https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=400&h=300&fit=crop" 
                                             alt="Pack premium" 
                                             class="w-full h-48 object-cover">
                                        <span class="absolute top-3 right-3 bg-yellow-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                            ⭐ Premium
                                        </span>
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-bold text-gray-800 mb-2">Pack Événement VIP</h4>
                                        <p class="text-sm text-gray-600 mb-3">50 bouteilles + Décoration</p>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-2xl font-bold text-purple-600">75,000 FCFA</p>
                                            </div>
                                            <button class="bg-purple-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-600 transition-all flex items-center gap-2">
                                                <span>🎁</span>
                                                Acheter
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Mariage/Événement -->
                                <div class="bg-gradient-to-br from-pink-100 to-purple-100 rounded-xl p-6 flex flex-col justify-between shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-2 cursor-pointer border-2 border-pink-300">
                                    <div>
                                        <span class="text-5xl mb-3 block">💒</span>
                                        <h4 class="font-bold text-gray-800 mb-2 text-xl">Services Événements</h4>
                                        <p class="text-sm text-gray-700 mb-3">Mariages, Anniversaires, Baptêmes, Conférences...</p>
                                        <ul class="text-xs text-gray-600 space-y-1 mb-4">
                                            <li>✓ Livraison gratuite</li>
                                            <li>✓ Service personnalisé</li>
                                            <li>✓ Devis sur mesure</li>
                                        </ul>
                                    </div>
                                    <button onclick="openEventModal()" class="w-full bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold py-3 rounded-lg hover:shadow-xl transition-all">
                                        Demander un devis
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create Post Box -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-100 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center gap-4 mb-4">
                            <img src="https://ui-avatars.com/api/?name=You&background=8b5cf6&color=fff&size=64" 
                                 alt="Your avatar" 
                                 class="w-12 h-12 rounded-full border-2 border-purple-400">
                            <input type="text" 
                                   onclick="openCreatePostModal()"
                                   readonly
                                   placeholder="Partagez votre expérience Paradisia... 🌴" 
                                   class="flex-1 bg-gray-100 rounded-full px-6 py-3 cursor-pointer hover:bg-gray-200 transition-all outline-none">
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex gap-2">
                                <button onclick="openCreatePostModal('photo')" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-green-50 text-green-600 transition-all">
                                    <span class="text-xl">📸</span>
                                    <span class="text-sm font-semibold">Photo</span>
                                </button>
                                <button onclick="openCreatePostModal('video')" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-blue-50 text-blue-600 transition-all">
                                    <span class="text-xl">🎥</span>
                                    <span class="text-sm font-semibold">Vidéo</span>
                                </button>
                                <button onclick="openCreatePostModal('feeling')" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-yellow-50 text-yellow-600 transition-all">
                                    <span class="text-xl">😊</span>
                                    <span class="text-sm font-semibold">Humeur</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Stories Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-4 border-2 border-pink-100">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span class="text-2xl">📖</span>
                            Stories Paradisia
                        </h4>
                        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                            <!-- Add Story -->
                            <div class="flex-shrink-0 text-center cursor-pointer group">
                                <div class="w-20 h-20 bg-gradient-to-br from-purple-400 to-pink-400 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-110">
                                    <span class="text-3xl">➕</span>
                                </div>
                                <p class="text-xs mt-1 font-semibold text-gray-600">Ajouter</p>
                            </div>
                            
                            <!-- Story 1 -->
                            <div class="flex-shrink-0 text-center cursor-pointer group">
                                <div class="w-20 h-20 rounded-xl overflow-hidden border-4 border-green-400 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-110">
                                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&h=200&fit=crop" alt="Story" class="w-full h-full object-cover">
                                </div>
                                <p class="text-xs mt-1 font-semibold text-gray-600">Sarah</p>
                            </div>
                            
                            <!-- Story 2 -->
                            <div class="flex-shrink-0 text-center cursor-pointer group">
                                <div class="w-20 h-20 rounded-xl overflow-hidden border-4 border-yellow-400 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-110">
                                    <img src="https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=200&h=200&fit=crop" alt="Story" class="w-full h-full object-cover">
                                </div>
                                <p class="text-xs mt-1 font-semibold text-gray-600">Marc</p>
                            </div>
                            
                            <!-- Story 3 -->
                            <div class="flex-shrink-0 text-center cursor-pointer group">
                                <div class="w-20 h-20 rounded-xl overflow-hidden border-4 border-blue-400 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-110">
                                    <img src="https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=200&h=200&fit=crop" alt="Story" class="w-full h-full object-cover">
                                </div>
                                <p class="text-xs mt-1 font-semibold text-gray-600">Lisa</p>
                            </div>
                            
                            <!-- Story 4 -->
                            <div class="flex-shrink-0 text-center cursor-pointer group">
                                <div class="w-20 h-20 rounded-xl overflow-hidden border-4 border-pink-400 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-110">
                                    <img src="https://images.unsplash.com/photo-1560493676-04071c5f467b?w=200&h=200&fit=crop" alt="Story" class="w-full h-full object-cover">
                                </div>
                                <p class="text-xs mt-1 font-semibold text-gray-600">Tom</p>
                            </div>
                            
                            <!-- Story 5 -->
                            <div class="flex-shrink-0 text-center cursor-pointer group">
                                <div class="w-20 h-20 rounded-xl overflow-hidden border-4 border-orange-400 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-110">
                                    <img src="https://images.unsplash.com/photo-1514995669114-6081e934b693?w=200&h=200&fit=crop" alt="Story" class="w-full h-full object-cover">
                                </div>
                                <p class="text-xs mt-1 font-semibold text-gray-600">Emma</p>
                            </div>
                        </div>
                    </div>

                    <!-- Post 1 - Vidéo de production -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-green-100 hover:shadow-2xl transition-all duration-300 animate-slide-in">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=100&h=100&fit=crop" 
                                         alt="PARADISIA Official" 
                                         class="w-12 h-12 rounded-full border-2 border-green-400">
                                    <div>
                                        <h4 class="font-bold text-gray-800 flex items-center gap-2">
                                            PARADISIA Official
                                            <span class="text-blue-500">✓</span>
                                        </h4>
                                        <p class="text-sm text-gray-500">Il y a 2 heures • 🌴</p>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <p class="text-gray-800 mb-4">
                                🌺 Découvrez les coulisses de notre production ! De la cueillette à la bouteille, chaque goutte de PARADISIA est un voyage au cœur des tropiques 🍹✨
                                <br><br>
                                <span class="text-green-600 font-semibold">#ParadisiaFamily #JusNaturel #100Naturel #InvestissezDansLeParadis</span>
                            </p>
                        </div>
                        
                        <div class="relative group cursor-pointer">
                            <video class="w-full h-96 object-cover" poster="https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=800&h=600&fit=crop" controls>
                                <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4" type="video/mp4">
                            </video>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4 text-sm text-gray-600">
                                <span class="flex items-center gap-2">
                                    <span class="flex -space-x-2">
                                        <img src="https://ui-avatars.com/api/?name=User1&background=10b981&color=fff&size=32" class="w-6 h-6 rounded-full border-2 border-white" alt="">
                                        <img src="https://ui-avatars.com/api/?name=User2&background=3b82f6&color=fff&size=32" class="w-6 h-6 rounded-full border-2 border-white" alt="">
                                        <img src="https://ui-avatars.com/api/?name=User3&background=f59e0b&color=fff&size=32" class="w-6 h-6 rounded-full border-2 border-white" alt="">
                                    </span>
                                    Sarah, Marc et 342 autres
                                </span>
                                <span>89 commentaires</span>
                            </div>
                            
                            <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-green-50 text-green-600 transition-all group">
                                    <svg class="w-5 h-5 group-hover:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span class="font-semibold">J'adore</span>
                                </button>
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-blue-50 text-blue-600 transition-all group">
                                    <svg class="w-5 h-5 group-hover:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <span class="font-semibold">Commenter</span>
                                </button>
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-purple-50 text-purple-600 transition-all group">
                                    <svg class="w-5 h-5 group-hover:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                    <span class="font-semibold">Partager</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Post 2 - Opportunité d'investissement -->
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl shadow-lg overflow-hidden border-2 border-yellow-200 hover:shadow-2xl transition-all duration-300">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-2xl">
                                        💰
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800 flex items-center gap-2">
                                            Opportunités d'Investissement
                                            <span class="bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded-full font-bold">HOT</span>
                                        </h4>
                                        <p class="text-sm text-gray-500">Il y a 5 heures</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-xl p-6 mb-4 border-2 border-yellow-300">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <span>🚀</span>
                                    Projet Expansion Tropicale 2024
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    Rejoignez notre aventure et investissez dans l'avenir de PARADISIA ! Nouvelle ligne de production et expansion en Afrique de l'Ouest.
                                </p>
                                
                                <div class="grid grid-cols-3 gap-4 mb-4">
                                    <div class="text-center p-3 bg-green-50 rounded-lg">
                                        <p class="text-2xl font-bold text-green-600">15%</p>
                                        <p class="text-xs text-gray-600">ROI Annuel</p>
                                    </div>
                                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                                        <p class="text-2xl font-bold text-blue-600">500K</p>
                                        <p class="text-xs text-gray-600">Objectif</p>
                                    </div>
                                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                                        <p class="text-2xl font-bold text-purple-600">78%</p>
                                        <p class="text-xs text-gray-600">Complété</p>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                                        <span>390,000 FCFA collectés</span>
                                        <span>500,000 FCFA</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                        <div class="bg-gradient-to-r from-green-400 via-blue-400 to-purple-500 h-full rounded-full animate-pulse" style="width: 78%"></div>
                                    </div>
                                </div>
                                
                                <button class="w-full bg-gradient-to-r from-yellow-400 via-orange-400 to-red-400 text-white font-bold py-4 rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                                    <span class="text-xl">💎</span>
                                    Investir Maintenant
                                    <span class="text-xl">💎</span>
                                </button>
                            </div>
                            
                            <p class="text-sm text-gray-600 flex items-center gap-2">
                                <span>👥</span>
                                <strong>156 investisseurs</strong> ont déjà rejoint ce projet
                            </p>
                        </div>
                    </div>

                    <!-- Post 3 - Galerie de photos -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-blue-100 hover:shadow-2xl transition-all duration-300">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Marie+Tropical&background=3b82f6&color=fff&size=64" 
                                         alt="Marie" 
                                         class="w-12 h-12 rounded-full border-2 border-blue-400">
                                    <div>
                                        <h4 class="font-bold text-gray-800">Marie Tropical</h4>
                                        <p class="text-sm text-gray-500">Il y a 1 jour • 📍 Douala, Cameroun</p>
                                    </div>
                                </div>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <p class="text-gray-800 mb-4">
                                Rien de mieux qu'un jus PARADISIA pour bien commencer la journée ! 🌅🍹 
                                Le goût du paradis dans chaque gorgée ! 
                                <br>
                                <span class="text-blue-600 font-semibold">#ParadisiaMoment #VieParadisiaque #JusDuMatin</span>
                            </p>
                        </div>
                        
                        <!-- Photo Grid -->
                        <div class="grid grid-cols-2 gap-1">
                            <img src="https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=600&h=400&fit=crop" 
                                 alt="Jus" 
                                 class="w-full h-64 object-cover hover:opacity-90 transition-opacity cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=600&h=400&fit=crop" 
                                 alt="Fruits" 
                                 class="w-full h-64 object-cover hover:opacity-90 transition-opacity cursor-pointer">
                        </div>
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4 text-sm text-gray-600">
                                <span>❤️ 234 J'adore</span>
                                <span>45 commentaires • 12 partages</span>
                            </div>
                            
                            <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-red-50 text-red-500 transition-all group">
                                    <svg class="w-5 h-5 group-hover:scale-125 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-semibold">J'adore</span>
                                </button>
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-blue-50 text-blue-600 transition-all group">
                                    <svg class="w-5 h-5 group-hover:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <span class="font-semibold">Commenter</span>
                                </button>
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-green-50 text-green-600 transition-all group">
                                    <svg class="w-5 h-5 group-hover:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                    <span class="font-semibold">Partager</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Post 4 - Témoignage client avec vidéo -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-pink-100 hover:shadow-2xl transition-all duration-300">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Thomas+Paradise&background=ec4899&color=fff&size=64" 
                                         alt="Thomas" 
                                         class="w-12 h-12 rounded-full border-2 border-pink-400">
                                    <div>
                                        <h4 class="font-bold text-gray-800">Thomas Paradise</h4>
                                        <p class="text-sm text-gray-500">Il y a 3 jours</p>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-gray-800 mb-4">
                                🌟 Témoignage : Comment PARADISIA a changé mes matinées ! Depuis que j'ai découvert ces jus, je ne peux plus m'en passer. Regardez ma routine matinale 🌺
                                <br>
                                <span class="text-pink-600 font-semibold">#TémoignageParadisia #ChangezVotreVie</span>
                            </p>
                        </div>
                        
                        <div class="relative">
                            <video class="w-full h-80 object-cover" poster="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&h=600&fit=crop" controls>
                                <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" type="video/mp4">
                            </video>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4 text-sm text-gray-600">
                                <span>❤️ 567 J'adore</span>
                                <span>123 commentaires</span>
                            </div>
                            
                            <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-pink-50 text-pink-600 transition-all font-semibold">
                                    ❤️ J'adore
                                </button>
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-blue-50 text-blue-600 transition-all font-semibold">
                                    💬 Commenter
                                </button>
                                <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-green-50 text-green-600 transition-all font-semibold">
                                    📤 Partager
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Load More Button -->
                    <div class="text-center py-6">
                        <button class="bg-gradient-to-r from-purple-400 to-pink-400 text-white font-bold py-4 px-8 rounded-full hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center gap-3 mx-auto">
                            <span>Charger plus de posts</span>
                            <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Right Sidebar - Suggestions & Tendances -->
                <div class="col-span-12 lg:col-span-3 space-y-4">
                    <!-- Investisseurs Suggérés -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-100">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span class="text-2xl">👥</span>
                            Investisseurs à Suivre
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Sophie+Laurent&background=8b5cf6&color=fff&size=64" 
                                         alt="Sophie" 
                                         class="w-12 h-12 rounded-full border-2 border-purple-300">
                                    <div>
                                        <h5 class="font-semibold text-gray-800 text-sm">Sophie Laurent</h5>
                                        <p class="text-xs text-gray-500">Investisseur VIP</p>
                                    </div>
                                </div>
                                <button class="bg-purple-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-600 transition-all">
                                    Suivre
                                </button>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Pierre+Martin&background=10b981&color=fff&size=64" 
                                         alt="Pierre" 
                                         class="w-12 h-12 rounded-full border-2 border-green-300">
                                    <div>
                                        <h5 class="font-semibold text-gray-800 text-sm">Pierre Martin</h5>
                                        <p class="text-xs text-gray-500">Investisseur Pro</p>
                                    </div>
                                </div>
                                <button class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-600 transition-all">
                                    Suivre
                                </button>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=Julie+Dubois&background=f59e0b&color=fff&size=64" 
                                         alt="Julie" 
                                         class="w-12 h-12 rounded-full border-2 border-yellow-300">
                                    <div>
                                        <h5 class="font-semibold text-gray-800 text-sm">Julie Dubois</h5>
                                        <p class="text-xs text-gray-500">Investisseur</p>
                                    </div>
                                </div>
                                <button class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-600 transition-all">
                                    Suivre
                                </button>
                            </div>
                        </div>
                        
                        <button class="w-full mt-4 text-purple-600 font-semibold text-sm hover:underline">
                            Voir plus de suggestions →
                        </button>
                    </div>

                    <!-- Tendances -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-orange-100">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span class="text-2xl">🔥</span>
                            Tendances PARADISIA
                        </h4>
                        <div class="space-y-4">
                            <div class="cursor-pointer hover:bg-orange-50 p-3 rounded-lg transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500">Tendance #1</p>
                                        <h5 class="font-bold text-gray-800">#JusDuParadis</h5>
                                        <p class="text-xs text-gray-500">12.5K posts</p>
                                    </div>
                                    <span class="text-2xl">🍹</span>
                                </div>
                            </div>
                            
                            <div class="cursor-pointer hover:bg-green-50 p-3 rounded-lg transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500">Tendance #2</p>
                                        <h5 class="font-bold text-gray-800">#InvestissezNaturel</h5>
                                        <p class="text-xs text-gray-500">8.9K posts</p>
                                    </div>
                                    <span class="text-2xl">💰</span>
                                </div>
                            </div>
                            
                            <div class="cursor-pointer hover:bg-blue-50 p-3 rounded-lg transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500">Tendance #3</p>
                                        <h5 class="font-bold text-gray-800">#VieParadisiaque</h5>
                                        <p class="text-xs text-gray-500">6.7K posts</p>
                                    </div>
                                    <span class="text-2xl">🌴</span>
                                </div>
                            </div>
                            
                            <div class="cursor-pointer hover:bg-pink-50 p-3 rounded-lg transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500">Tendance #4</p>
                                        <h5 class="font-bold text-gray-800">#100Naturel</h5>
                                        <p class="text-xs text-gray-500">5.2K posts</p>
                                    </div>
                                    <span class="text-2xl">🌺</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Événements à venir -->
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl shadow-lg p-6 border-2 border-blue-200">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span class="text-2xl">📅</span>
                            Événements à Venir
                        </h4>
                        <div class="space-y-3">
                            <div class="bg-white p-4 rounded-xl shadow hover:shadow-lg transition-all cursor-pointer">
                                <div class="flex gap-3">
                                    <div class="bg-gradient-to-br from-red-400 to-orange-400 text-white rounded-lg p-3 text-center flex-shrink-0">
                                        <p class="text-2xl font-bold">15</p>
                                        <p class="text-xs">NOV</p>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-gray-800 text-sm">Grande Dégustation</h5>
                                        <p class="text-xs text-gray-600">Douala, Cameroun</p>
                                        <p class="text-xs text-green-600 font-semibold mt-1">250 participants</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white p-4 rounded-xl shadow hover:shadow-lg transition-all cursor-pointer">
                                <div class="flex gap-3">
                                    <div class="bg-gradient-to-br from-blue-400 to-purple-400 text-white rounded-lg p-3 text-center flex-shrink-0">
                                        <p class="text-2xl font-bold">22</p>
                                        <p class="text-xs">NOV</p>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-gray-800 text-sm">Assemblée Investisseurs</h5>
                                        <p class="text-xs text-gray-600">En ligne</p>
                                        <p class="text-xs text-blue-600 font-semibold mt-1">Webinaire gratuit</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Publicité -->
                    <div class="bg-gradient-to-br from-green-400 via-blue-400 to-purple-500 rounded-2xl shadow-lg overflow-hidden">
                        <div class="p-6 text-center text-white">
                            <span class="text-5xl mb-3 block">🎁</span>
                            <h4 class="font-bold text-xl mb-2">Offre Spéciale !</h4>
                            <p class="text-sm mb-4 opacity-90">Investissez 50K+ et recevez un pack cadeau exclusif PARADISIA</p>
                            <button class="bg-white text-purple-600 font-bold py-3 px-6 rounded-full hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                                En savoir plus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal - Créer un Post -->
    <div id="createPostModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all scale-95 opacity-0" id="createPostModalContent">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <span>✨</span>
                        Créer une publication
                    </h3>
                    <button onclick="closeCreatePostModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- User Info -->
                <div class="flex items-center gap-3 mb-4">
                    <img src="https://ui-avatars.com/api/?name=You&background=8b5cf6&color=fff&size=64" 
                         alt="Your avatar" 
                         class="w-12 h-12 rounded-full border-2 border-purple-400">
                    <div>
                        <h4 class="font-bold text-gray-800">Votre Nom</h4>
                        <select class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-lg border-0 focus:ring-2 focus:ring-purple-400">
                            <option>🌍 Public</option>
                            <option>👥 Amis</option>
                            <option>🔒 Privé</option>
                        </select>
                    </div>
                </div>

                <!-- Text Area -->
                <textarea id="postContent" 
                          rows="6" 
                          placeholder="Que voulez-vous partager sur PARADISIA ? 🌴" 
                          class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:border-transparent resize-none text-gray-800"></textarea>

                <!-- Media Preview -->
                <div id="mediaPreview" class="hidden mt-4 relative">
                    <img id="imagePreview" class="hidden w-full rounded-xl" />
                    <video id="videoPreview" class="hidden w-full rounded-xl" controls></video>
                    <button onclick="removeMedia()" class="absolute top-3 right-3 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Add to Post -->
                <div class="mt-4 p-4 border border-gray-200 rounded-xl">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Ajouter à votre publication</p>
                    <div class="flex gap-2 flex-wrap">
                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-green-50 text-green-600 cursor-pointer transition-all border border-green-200">
                            <span class="text-xl">📸</span>
                            <span class="text-sm font-semibold">Photo</span>
                            <input type="file" accept="image/*" onchange="handleFileSelect(event, 'image')" class="hidden">
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-blue-50 text-blue-600 cursor-pointer transition-all border border-blue-200">
                            <span class="text-xl">🎥</span>
                            <span class="text-sm font-semibold">Vidéo</span>
                            <input type="file" accept="video/*" onchange="handleFileSelect(event, 'video')" class="hidden">
                        </label>
                        <button class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-yellow-50 text-yellow-600 transition-all border border-yellow-200">
                            <span class="text-xl">😊</span>
                            <span class="text-sm font-semibold">Humeur</span>
                        </button>
                        <button class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-purple-50 text-purple-600 transition-all border border-purple-200">
                            <span class="text-xl">📍</span>
                            <span class="text-sm font-semibold">Lieu</span>
                        </button>
                        <button class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-pink-50 text-pink-600 transition-all border border-pink-200">
                            <span class="text-xl">🏷️</span>
                            <span class="text-sm font-semibold">Tag</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6 rounded-b-2xl">
                <button onclick="publishPost()" class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold py-4 rounded-xl hover:shadow-2xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                    <span>📤</span>
                    Publier maintenant
                </button>
            </div>
        </div>
    </div>

    <!-- Modal - Demande de Devis Événement -->
    <div id="eventModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto transform transition-all scale-95 opacity-0" id="eventModalContent">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-pink-500 to-purple-500 p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                            <span>💒</span>
                            Demande de Devis Événement
                        </h3>
                        <p class="text-pink-100 text-sm mt-1">Mariage, Anniversaire, Baptême, Conférence...</p>
                    </div>
                    <button onclick="closeEventModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-6">
                <!-- Type d'événement -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Type d'événement *</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button class="event-type-btn p-4 rounded-xl border-2 border-gray-200 hover:border-pink-500 hover:bg-pink-50 transition-all text-center" data-type="mariage">
                            <span class="text-3xl block mb-2">💒</span>
                            <span class="text-sm font-semibold text-gray-700">Mariage</span>
                        </button>
                        <button class="event-type-btn p-4 rounded-xl border-2 border-gray-200 hover:border-purple-500 hover:bg-purple-50 transition-all text-center" data-type="anniversaire">
                            <span class="text-3xl block mb-2">🎂</span>
                            <span class="text-sm font-semibold text-gray-700">Anniversaire</span>
                        </button>
                        <button class="event-type-btn p-4 rounded-xl border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-all text-center" data-type="bapteme">
                            <span class="text-3xl block mb-2">👶</span>
                            <span class="text-sm font-semibold text-gray-700">Baptême</span>
                        </button>
                        <button class="event-type-btn p-4 rounded-xl border-2 border-gray-200 hover:border-green-500 hover:bg-green-50 transition-all text-center" data-type="conference">
                            <span class="text-3xl block mb-2">🎤</span>
                            <span class="text-sm font-semibold text-gray-700">Conférence</span>
                        </button>
                    </div>
                </div>

                <!-- Informations personnelles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nom complet *</label>
                        <input type="text" placeholder="Jean Dupont" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Téléphone *</label>
                        <input type="tel" placeholder="+237 6XX XXX XXX" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <input type="email" placeholder="email@example.com" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>

                <!-- Détails de l'événement -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date de l'événement *</label>
                        <input type="date" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nombre d'invités *</label>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            <option>Moins de 50</option>
                            <option>50 - 100</option>
                            <option>100 - 200</option>
                            <option>200 - 500</option>
                            <option>Plus de 500</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Lieu de l'événement</label>
                    <input type="text" placeholder="Douala, Cameroun" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>

                <!-- Options de service -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Services souhaités</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-pink-500 rounded focus:ring-2 focus:ring-pink-500">
                            <span class="text-gray-700">🍹 Boissons illimitées</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-pink-500 rounded focus:ring-2 focus:ring-pink-500">
                            <span class="text-gray-700">🎨 Décoration personnalisée</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-pink-500 rounded focus:ring-2 focus:ring-pink-500">
                            <span class="text-gray-700">🚚 Livraison et installation</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-pink-500 rounded focus:ring-2 focus:ring-pink-500">
                            <span class="text-gray-700">👨‍🍳 Personnel de service</span>
                        </label>
                    </div>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Message additionnel</label>
                    <textarea rows="4" placeholder="Décrivez vos besoins spécifiques..." class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent resize-none"></textarea>
                </div>

                <!-- Budget estimé -->
                <div class="bg-gradient-to-br from-green-50 to-blue-50 p-5 rounded-xl border-2 border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-1">Budget estimé</p>
                            <p class="text-2xl font-bold text-green-600">À partir de 50,000 FCFA</p>
                        </div>
                        <span class="text-4xl">💰</span>
                    </div>
                    <p class="text-xs text-gray-600 mt-2">* Le prix final sera calculé en fonction de vos besoins</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6 rounded-b-2xl">
                <div class="flex gap-3">
                    <button onclick="closeEventModal()" class="flex-1 bg-gray-200 text-gray-700 font-bold py-4 rounded-xl hover:bg-gray-300 transition-all">
                        Annuler
                    </button>
                    <button onclick="submitEventRequest()" class="flex-1 bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold py-4 rounded-xl hover:shadow-2xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                        <span>📨</span>
                        Envoyer la demande
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        
        .animate-blob {
            animation: blob 7s infinite;
        }
        
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        
        .animation-delay-4000 {
            animation-delay: 4s;
        }
        
        .animation-delay-6000 {
            animation-delay: 6s;
        }
        
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Modal animations */
        .modal-show {
            display: flex !important;
        }

        .modal-content-show {
            transform: scale(1) !important;
            opacity: 1 !important;
        }

        .event-type-btn.active {
            border-color: #ec4899;
            background-color: #fce7f3;
        }
    </style>

    <script>
        // Modal - Create Post
        function openCreatePostModal(type = null) {
            const modal = document.getElementById('createPostModal');
            const modalContent = document.getElementById('createPostModalContent');
            modal.classList.add('modal-show');
            setTimeout(() => {
                modalContent.classList.add('modal-content-show');
            }, 10);
        }

        function closeCreatePostModal() {
            const modal = document.getElementById('createPostModal');
            const modalContent = document.getElementById('createPostModalContent');
            modalContent.classList.remove('modal-content-show');
            setTimeout(() => {
                modal.classList.remove('modal-show');
                document.getElementById('postContent').value = '';
                removeMedia();
            }, 300);
        }

        function handleFileSelect(event, type) {
            const file = event.target.files[0];
            if (!file) return;

            const preview = document.getElementById('mediaPreview');
            const imagePreview = document.getElementById('imagePreview');
            const videoPreview = document.getElementById('videoPreview');

            preview.classList.remove('hidden');

            if (type === 'image') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                    videoPreview.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            } else if (type === 'video') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    videoPreview.src = e.target.result;
                    videoPreview.classList.remove('hidden');
                    imagePreview.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function removeMedia() {
            const preview = document.getElementById('mediaPreview');
            const imagePreview = document.getElementById('imagePreview');
            const videoPreview = document.getElementById('videoPreview');
            
            preview.classList.add('hidden');
            imagePreview.classList.add('hidden');
            videoPreview.classList.add('hidden');
            imagePreview.src = '';
            videoPreview.src = '';
        }

        function publishPost() {
            const content = document.getElementById('postContent').value;
            
            if (!content.trim()) {
                alert('⚠️ Veuillez écrire quelque chose avant de publier !');
                return;
            }

            // Simulation de publication
            alert('✅ Votre publication a été créée avec succès ! 🎉');
            closeCreatePostModal();
            
            // Ici vous ajouteriez le code pour envoyer les données au serveur
        }

        // Modal - Event Request
        function openEventModal() {
            const modal = document.getElementById('eventModal');
            const modalContent = document.getElementById('eventModalContent');
            modal.classList.add('modal-show');
            setTimeout(() => {
                modalContent.classList.add('modal-content-show');
            }, 10);
        }

        function closeEventModal() {
            const modal = document.getElementById('eventModal');
            const modalContent = document.getElementById('eventModalContent');
            modalContent.classList.remove('modal-content-show');
            setTimeout(() => {
                modal.classList.remove('modal-show');
            }, 300);
        }

        function submitEventRequest() {
            // Validation simple
            alert('✅ Votre demande de devis a été envoyée avec succès ! 🎉\n\nNotre équipe vous contactera dans les 24 heures.');
            closeEventModal();
            
            // Ici vous ajouteriez le code pour envoyer les données au serveur
        }

        // Event type selection
        document.addEventListener('DOMContentLoaded', function() {
            const eventButtons = document.querySelectorAll('.event-type-btn');
            eventButtons.forEach(button => {
                button.addEventListener('click', function() {
                    eventButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Close modals on outside click
            document.getElementById('createPostModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeCreatePostModal();
                }
            });

            document.getElementById('eventModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeEventModal();
                }
            });

            // Close modals on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeCreatePostModal();
                    closeEventModal();
                }
            });
        });
    </script>

    </div>
    @endvolt    
</x-layouts.app>
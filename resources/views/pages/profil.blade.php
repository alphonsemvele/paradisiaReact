<x-layouts.app>
    @volt
    <div>
        <div class="w-full bg-gradient-to-br from-green-50 via-blue-50 to-yellow-50 min-h-screen">
            <!-- Animated Background Elements -->
            <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
                <div class="absolute top-10 left-10 w-32 h-32 bg-green-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob"></div>
                <div class="absolute top-20 right-20 w-40 h-40 bg-yellow-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-20 left-40 w-36 h-36 bg-teal-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-4000"></div>
                <div class="absolute bottom-40 right-40 w-32 h-32 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-6000"></div>
            </div>

            <div class="relative z-10 container mx-auto px-4 py-8" style="max-width: 1600px;">
                
                <!-- Cover Photo -->
                <div class="relative bg-gradient-to-r from-green-400 via-teal-400 to-blue-400 rounded-3xl shadow-2xl mb-6 overflow-hidden" style="height: 300px;">
                    <img src="https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=1600&h=300&fit=crop" 
                         alt="Cover" 
                         class="w-full h-full object-cover opacity-80">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    
                    <!-- Edit Cover Button -->
                    <button class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg font-semibold hover:bg-white/30 transition-all flex items-center gap-2 border border-white/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Modifier la couverture
                    </button>

                    <!-- Profile Picture -->
                    <div class="absolute -bottom-20 left-8">
                        <div class="relative">
                            <img src="https://ui-avatars.com/api/?name=Investisseur+Pro&background=10b981&color=fff&size=200" 
                                 alt="Profile" 
                                 class="w-40 h-40 rounded-full border-8 border-white shadow-2xl">
                            <button class="absolute bottom-2 right-2 bg-green-500 text-white p-3 rounded-full shadow-lg hover:bg-green-600 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                            <span class="absolute top-2 right-2 w-6 h-6 bg-green-500 border-4 border-white rounded-full"></span>
                        </div>
                    </div>
                </div>

                <!-- Profile Header -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 ml-0 md:ml-48">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Investisseur Pro</h1>
                            <p class="text-gray-600 mb-3">@investor_paradise • Membre depuis Janvier 2024</p>
                            <p class="text-gray-700 mb-4">🌴 Passionné par PARADISIA | 💰 Investisseur actif | 🌱 Pour un avenir naturel et durable</p>
                            
                            <div class="flex flex-wrap gap-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-gray-700">Douala, Cameroun</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-gray-700">Entrepreneur</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-gray-700">Rejoint le 15 janvier 2024</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button onclick="openEditProfileModal()" class="bg-gradient-to-r from-green-500 to-teal-500 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Modifier le profil
                            </button>
                            <button class="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                Partager le profil
                            </button>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200">
                        <div class="text-center">
                            <p class="text-3xl font-bold text-green-600">45</p>
                            <p class="text-sm text-gray-600">Publications</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-blue-600">1.2k</p>
                            <p class="text-sm text-gray-600">Abonnés</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-yellow-600">890</p>
                            <p class="text-sm text-gray-600">Abonnements</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-teal-600">250K</p>
                            <p class="text-sm text-gray-600">Investis</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="bg-white rounded-2xl shadow-lg mb-6">
                    <div class="flex border-b border-gray-200 overflow-x-auto">
                        <button onclick="switchTab('posts')" class="tab-btn active flex-1 min-w-max px-6 py-4 font-semibold text-gray-600 hover:text-green-600 hover:bg-green-50 transition-all border-b-2 border-transparent" data-tab="posts">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                Publications
                            </div>
                        </button>
                        <button onclick="switchTab('about')" class="tab-btn flex-1 min-w-max px-6 py-4 font-semibold text-gray-600 hover:text-green-600 hover:bg-green-50 transition-all border-b-2 border-transparent" data-tab="about">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                À propos
                            </div>
                        </button>
                        <button onclick="switchTab('investments')" class="tab-btn flex-1 min-w-max px-6 py-4 font-semibold text-gray-600 hover:text-green-600 hover:bg-green-50 transition-all border-b-2 border-transparent" data-tab="investments">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Investissements
                            </div>
                        </button>
                        <button onclick="switchTab('photos')" class="tab-btn flex-1 min-w-max px-6 py-4 font-semibold text-gray-600 hover:text-green-600 hover:bg-green-50 transition-all border-b-2 border-transparent" data-tab="photos">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Photos
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Tab Contents -->
                <div class="grid grid-cols-12 gap-6">
                    <!-- Main Content Area -->
                    <div class="col-span-12 lg:col-span-8">
                        
                        <!-- Posts Tab -->
                        <div id="postsTab" class="tab-content space-y-6">
                            <!-- Post 1 -->
                            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-green-100 hover:shadow-2xl transition-all duration-300">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <img src="https://ui-avatars.com/api/?name=Investisseur+Pro&background=10b981&color=fff&size=64" 
                                                 alt="Profile" 
                                                 class="w-12 h-12 rounded-full border-2 border-green-400">
                                            <div>
                                                <h4 class="font-bold text-gray-800">Investisseur Pro</h4>
                                                <p class="text-sm text-gray-500">Il y a 2 heures</p>
                                            </div>
                                        </div>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <p class="text-gray-800 mb-4">
                                        Excellente nouvelle ! Je viens d'investir dans le projet d'Expansion Tropicale 2024 de PARADISIA 🌴💰 
                                        Très confiant pour l'avenir de cette entreprise qui révolutionne le marché des jus naturels !
                                        <br><br>
                                        <span class="text-green-600 font-semibold">#ParadisiaInvestor #InvestissementDurable #ROI18%</span>
                                    </p>
                                </div>
                                
                                <img src="https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800&h=400&fit=crop" 
                                     alt="Investment" 
                                     class="w-full h-96 object-cover">
                                
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4 text-sm text-gray-600">
                                        <span>❤️ 234 J'adore</span>
                                        <span>45 commentaires • 12 partages</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                                        <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-red-50 text-red-500 transition-all font-semibold">
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

                            <!-- Post 2 -->
                            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-blue-100 hover:shadow-2xl transition-all duration-300">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <img src="https://ui-avatars.com/api/?name=Investisseur+Pro&background=10b981&color=fff&size=64" 
                                                 alt="Profile" 
                                                 class="w-12 h-12 rounded-full border-2 border-green-400">
                                            <div>
                                                <h4 class="font-bold text-gray-800">Investisseur Pro</h4>
                                                <p class="text-sm text-gray-500">Il y a 1 jour</p>
                                            </div>
                                        </div>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <p class="text-gray-800 mb-4">
                                        Matinée productive avec un délicieux jus PARADISIA ! 🍹☀️ 
                                        Rien de tel pour bien commencer la journée. Merci @PARADISIA pour ces produits de qualité !
                                        <br>
                                        <span class="text-blue-600 font-semibold">#ParadisiaMoment #JusNaturel #VitaminéeDuMatin</span>
                                    </p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-1">
                                    <img src="https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=400&h=300&fit=crop" 
                                         alt="Juice" 
                                         class="w-full h-64 object-cover">
                                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=300&fit=crop" 
                                         alt="Fruits" 
                                         class="w-full h-64 object-cover">
                                </div>
                                
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4 text-sm text-gray-600">
                                        <span>❤️ 189 J'adore</span>
                                        <span>32 commentaires</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                                        <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-red-50 text-red-500 transition-all font-semibold">
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

                            <!-- Post 3 -->
                            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-yellow-100 hover:shadow-2xl transition-all duration-300">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <img src="https://ui-avatars.com/api/?name=Investisseur+Pro&background=10b981&color=fff&size=64" 
                                                 alt="Profile" 
                                                 class="w-12 h-12 rounded-full border-2 border-green-400">
                                            <div>
                                                <h4 class="font-bold text-gray-800">Investisseur Pro</h4>
                                                <p class="text-sm text-gray-500">Il y a 3 jours</p>
                                            </div>
                                        </div>
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <p class="text-gray-800 mb-4">
                                        Fier d'avoir atteint mon objectif d'investissement de 250K FCFA dans PARADISIA ! 🎯💰
                                        L'aventure ne fait que commencer. Rendez-vous dans quelques années pour récolter les fruits ! 🌱📈
                                        <br>
                                        <span class="text-yellow-600 font-semibold">#ObjectifAtteint #InvestisseurActif #CroissanceVerte</span>
                                    </p>
                                </div>
                                
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4 text-sm text-gray-600">
                                        <span>❤️ 312 J'adore</span>
                                        <span>67 commentaires</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                                        <button class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-red-50 text-red-500 transition-all font-semibold">
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

                            <!-- Load More -->
                            <div class="text-center py-6">
                                <button class="bg-gradient-to-r from-green-400 to-teal-400 text-white font-bold py-4 px-8 rounded-full hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                                    Charger plus de publications
                                </button>
                            </div>
                        </div>

                        <!-- About Tab -->
                        <div id="aboutTab" class="tab-content hidden space-y-6">
                            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <span>👤</span>
                                    Informations personnelles
                                </h3>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-600">Nom complet</p>
                                            <p class="font-semibold text-gray-900">Jean-Pierre Dupont</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-600">Email</p>
                                            <p class="font-semibold text-gray-900">jean.dupont@email.com</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-600">Téléphone</p>
                                            <p class="font-semibold text-gray-900">+237 6 XX XXX XXX</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-600">Localisation</p>
                                            <p class="font-semibold text-gray-900">Douala, Littoral, Cameroun</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-blue-100">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <span>💼</span>
                                    Informations professionnelles
                                </h3>
                                
                                <div class="space-y-4">
                                    <div class="p-4 bg-blue-50 rounded-xl">
                                        <p class="text-sm text-gray-600 mb-1">Profession</p>
                                        <p class="font-semibold text-gray-900">Entrepreneur / Investisseur</p>
                                    </div>
                                    <div class="p-4 bg-blue-50 rounded-xl">
                                        <p class="text-sm text-gray-600 mb-1">Secteur d'activité</p>
                                        <p class="font-semibold text-gray-900">Commerce & Distribution</p>
                                    </div>
                                    <div class="p-4 bg-blue-50 rounded-xl">
                                        <p class="text-sm text-gray-600 mb-1">Centres d'intérêt</p>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">Agriculture durable</span>
                                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">Investissement</span>
                                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-semibold">Alimentation bio</span>
                                            <span class="bg-teal-100 text-teal-700 px-3 py-1 rounded-full text-sm font-semibold">Entrepreneuriat</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Investments Tab -->
                        <div id="investmentsTab" class="tab-content hidden space-y-6">
                            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100">
                                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                    <span>💰</span>
                                    Mes Investissements
                                </h3>

                                <div class="space-y-4">
                                    <!-- Investment 1 -->
                                    <div class="border-2 border-green-200 rounded-xl p-6 bg-gradient-to-r from-green-50 to-transparent hover:shadow-lg transition-all">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1">
                                                <h4 class="text-lg font-bold text-gray-800 mb-2">Expansion Tropicale 2024</h4>
                                                <p class="text-sm text-gray-600">Investissement actif depuis 2 mois</p>
                                            </div>
                                            <span class="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-bold">Actif</span>
                                        </div>

                                        <div class="grid grid-cols-3 gap-4 mb-4">
                                            <div class="text-center p-3 bg-white rounded-lg">
                                                <p class="text-xs text-gray-600 mb-1">Investi</p>
                                                <p class="text-xl font-bold text-green-600">150K</p>
                                            </div>
                                            <div class="text-center p-3 bg-white rounded-lg">
                                                <p class="text-xs text-gray-600 mb-1">Gains actuels</p>
                                                <p class="text-xl font-bold text-blue-600">+12.5K</p>
                                            </div>
                                            <div class="text-center p-3 bg-white rounded-lg">
                                                <p class="text-xs text-gray-600 mb-1">ROI</p>
                                                <p class="text-xl font-bold text-yellow-600">+8.3%</p>
                                            </div>
                                        </div>

                                        <div class="flex gap-2">
                                            <button class="flex-1 bg-green-100 text-green-700 py-2 rounded-lg font-semibold hover:bg-green-200 transition-all">
                                                Voir détails
                                            </button>
                                            <button class="flex-1 bg-blue-100 text-blue-700 py-2 rounded-lg font-semibold hover:bg-blue-200 transition-all">
                                                Augmenter
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Investment 2 -->
                                    <div class="border-2 border-blue-200 rounded-xl p-6 bg-gradient-to-r from-blue-50 to-transparent hover:shadow-lg transition-all">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1">
                                                <h4 class="text-lg font-bold text-gray-800 mb-2">Réseau de Distribution Premium</h4>
                                                <p class="text-sm text-gray-600">Investissement actif depuis 1 mois</p>
                                            </div>
                                            <span class="bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-bold">Actif</span>
                                        </div>

                                        <div class="grid grid-cols-3 gap-4 mb-4">
                                            <div class="text-center p-3 bg-white rounded-lg">
                                                <p class="text-xs text-gray-600 mb-1">Investi</p>
                                                <p class="text-xl font-bold text-green-600">100K</p>
                                            </div>
                                            <div class="text-center p-3 bg-white rounded-lg">
                                                <p class="text-xs text-gray-600 mb-1">Gains actuels</p>
                                                <p class="text-xl font-bold text-blue-600">+5.8K</p>
                                            </div>
                                            <div class="text-center p-3 bg-white rounded-lg">
                                                <p class="text-xs text-gray-600 mb-1">ROI</p>
                                                <p class="text-xl font-bold text-yellow-600">+5.8%</p>
                                            </div>
                                        </div>

                                        <div class="flex gap-2">
                                            <button class="flex-1 bg-blue-100 text-blue-700 py-2 rounded-lg font-semibold hover:bg-blue-200 transition-all">
                                                Voir détails
                                            </button>
                                            <button class="flex-1 bg-green-100 text-green-700 py-2 rounded-lg font-semibold hover:bg-green-200 transition-all">
                                                Augmenter
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 p-6 bg-gradient-to-br from-green-50 to-teal-50 rounded-xl border-2 border-green-200">
                                    <h4 class="font-bold text-gray-800 mb-4">Résumé Total</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600 mb-1">Capital Total Investi</p>
                                            <p class="text-3xl font-bold text-green-600">250,000 FCFA</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600 mb-1">Gains Totaux</p>
                                            <p class="text-3xl font-bold text-blue-600">+18,300 FCFA</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Photos Tab -->
                        <div id="photosTab" class="tab-content hidden">
                            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-yellow-100">
                                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                    <span>📸</span>
                                    Mes Photos
                                </h3>

                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div class="relative group cursor-pointer overflow-hidden rounded-xl">
                                        <img src="https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400&h=400&fit=crop" 
                                             alt="Photo 1" 
                                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white text-center">
                                                <p class="font-semibold">❤️ 45</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative group cursor-pointer overflow-hidden rounded-xl">
                                        <img src="https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=400&h=400&fit=crop" 
                                             alt="Photo 2" 
                                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white text-center">
                                                <p class="font-semibold">❤️ 67</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative group cursor-pointer overflow-hidden rounded-xl">
                                        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop" 
                                             alt="Photo 3" 
                                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white text-center">
                                                <p class="font-semibold">❤️ 89</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative group cursor-pointer overflow-hidden rounded-xl">
                                        <img src="https://images.unsplash.com/photo-1560493676-04071c5f467b?w=400&h=400&fit=crop" 
                                             alt="Photo 4" 
                                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white text-center">
                                                <p class="font-semibold">❤️ 52</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative group cursor-pointer overflow-hidden rounded-xl">
                                        <img src="https://images.unsplash.com/photo-1514995669114-6081e934b693?w=400&h=400&fit=crop" 
                                             alt="Photo 5" 
                                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white text-center">
                                                <p class="font-semibold">❤️ 73</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative group cursor-pointer overflow-hidden rounded-xl">
                                        <img src="https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400&h=400&fit=crop" 
                                             alt="Photo 6" 
                                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white text-center">
                                                <p class="font-semibold">❤️ 95</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-span-12 lg:col-span-4 space-y-6">
                        <!-- Quick Info -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-teal-100">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span>⚡</span>
                                Informations Rapides
                            </h4>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="text-2xl">🎂</span>
                                    <div>
                                        <p class="text-gray-600">Date de naissance</p>
                                        <p class="font-semibold text-gray-900">15 Mars 1990</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="text-2xl">🎓</span>
                                    <div>
                                        <p class="text-gray-600">Formation</p>
                                        <p class="font-semibold text-gray-900">Master en Finance</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="text-2xl">💼</span>
                                    <div>
                                        <p class="text-gray-600">Statut</p>
                                        <p class="font-semibold text-gray-900">Investisseur VIP</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Badges -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-yellow-100">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span>🏆</span>
                                Badges & Récompenses
                            </h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="text-center p-3 bg-yellow-50 rounded-xl">
                                    <span class="text-4xl block mb-2">🥇</span>
                                    <p class="text-xs font-semibold text-gray-700">Top Investisseur</p>
                                </div>
                                <div class="text-center p-3 bg-green-50 rounded-xl">
                                    <span class="text-4xl block mb-2">🌱</span>
                                    <p class="text-xs font-semibold text-gray-700">Eco Friendly</p>
                                </div>
                                <div class="text-center p-3 bg-blue-50 rounded-xl">
                                    <span class="text-4xl block mb-2">💎</span>
                                    <p class="text-xs font-semibold text-gray-700">Membre VIP</p>
                                </div>
                                <div class="text-center p-3 bg-teal-50 rounded-xl">
                                    <span class="text-4xl block mb-2">🎯</span>
                                    <p class="text-xs font-semibold text-gray-700">Goal Achieved</p>
                                </div>
                                <div class="text-center p-3 bg-orange-50 rounded-xl">
                                    <span class="text-4xl block mb-2">🔥</span>
                                    <p class="text-xs font-semibold text-gray-700">Streak 30j</p>
                                </div>
                                <div class="text-center p-3 bg-purple-50 rounded-xl">
                                    <span class="text-4xl block mb-2">⭐</span>
                                    <p class="text-xs font-semibold text-gray-700">Early Bird</p>
                                </div>
                            </div>
                        </div>

                        <!-- Friends -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-blue-100">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span>👥</span>
                                Amis (890)
                            </h4>
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <img src="https://ui-avatars.com/api/?name=User1&background=10b981&color=fff&size=100" class="w-full h-24 rounded-xl object-cover" alt="Friend">
                                <img src="https://ui-avatars.com/api/?name=User2&background=3b82f6&color=fff&size=100" class="w-full h-24 rounded-xl object-cover" alt="Friend">
                                <img src="https://ui-avatars.com/api/?name=User3&background=f59e0b&color=fff&size=100" class="w-full h-24 rounded-xl object-cover" alt="Friend">
                                <img src="https://ui-avatars.com/api/?name=User4&background=14b8a6&color=fff&size=100" class="w-full h-24 rounded-xl object-cover" alt="Friend">
                                <img src="https://ui-avatars.com/api/?name=User5&background=8b5cf6&color=fff&size=100" class="w-full h-24 rounded-xl object-cover" alt="Friend">
                                <img src="https://ui-avatars.com/api/?name=User6&background=ec4899&color=fff&size=100" class="w-full h-24 rounded-xl object-cover" alt="Friend">
                            </div>
                            <button class="w-full bg-blue-100 text-blue-700 py-2 rounded-lg font-semibold hover:bg-blue-200 transition-all">
                                Voir tous les amis
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal - Edit Profile -->
        <div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto transform transition-all scale-95 opacity-0" id="editProfileModalContent">
                <!-- Header -->
                <div class="sticky top-0 bg-gradient-to-r from-green-500 to-teal-500 p-6 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                            <span>✏️</span>
                            Modifier mon profil
                        </h3>
                        <button onclick="closeEditProfileModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-6">
                    <!-- Personal Info -->
                    <div>
                        <h4 class="font-bold text-gray-800 mb-4 text-lg">Informations personnelles</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom complet</label>
                                <input type="text" value="Jean-Pierre Dupont" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom d'utilisateur</label>
                                <input type="text" value="investor_paradise" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <input type="email" value="jean.dupont@email.com" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                                <input type="tel" value="+237 6 XX XXX XXX" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Biographie</label>
                        <textarea rows="4" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none">🌴 Passionné par PARADISIA | 💰 Investisseur actif | 🌱 Pour un avenir naturel et durable</textarea>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Localisation</label>
                        <input type="text" value="Douala, Cameroun" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <!-- Professional Info -->
                    <div>
                        <h4 class="font-bold text-gray-800 mb-4 text-lg">Informations professionnelles</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Profession</label>
                                <input type="text" value="Entrepreneur / Investisseur" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Secteur</label>
                                <select class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option>Commerce & Distribution</option>
                                    <option>Finance</option>
                                    <option>Technologie</option>
                                    <option>Agriculture</option>
                                    <option>Autre</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Settings -->
                    <div>
                        <h4 class="font-bold text-gray-800 mb-4 text-lg">Paramètres de confidentialité</h4>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-xl hover:border-green-500 cursor-pointer transition-all">
                                <input type="checkbox" checked class="w-5 h-5 text-green-500 rounded">
                                <div>
                                    <p class="font-semibold text-gray-800">Profil public</p>
                                    <p class="text-xs text-gray-500">Tout le monde peut voir votre profil</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-xl hover:border-green-500 cursor-pointer transition-all">
                                <input type="checkbox" checked class="w-5 h-5 text-green-500 rounded">
                                <div>
                                    <p class="font-semibold text-gray-800">Afficher mes investissements</p>
                                    <p class="text-xs text-gray-500">Les autres peuvent voir vos investissements</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 border-2 border-gray-200 rounded-xl hover:border-green-500 cursor-pointer transition-all">
                                <input type="checkbox" class="w-5 h-5 text-green-500 rounded">
                                <div>
                                    <p class="font-semibold text-gray-800">Recevoir les notifications par email</p>
                                    <p class="text-xs text-gray-500">Notifications d'activité sur votre profil</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6 rounded-b-2xl">
                    <div class="flex gap-3">
                        <button onclick="closeEditProfileModal()" class="flex-1 bg-gray-200 text-gray-700 font-bold py-4 rounded-xl hover:bg-gray-300 transition-all">
                            Annuler
                        </button>
                        <button onclick="saveProfile()" class="flex-1 bg-gradient-to-r from-green-500 to-teal-500 text-white font-bold py-4 rounded-xl hover:shadow-2xl transition-all transform hover:scale-105">
                            Enregistrer les modifications
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

            .tab-btn.active {
                color: #10b981;
                background-color: #d1fae5;
                border-bottom-color: #10b981;
            }

            .modal-show {
                display: flex !important;
            }

            .modal-content-show {
                transform: scale(1) !important;
                opacity: 1 !important;
            }
        </style>

        <script>
            // Tab Switching
            function switchTab(tabName) {
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.add('hidden');
                });

                // Remove active class from all buttons
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Show selected tab
                document.getElementById(tabName + 'Tab').classList.remove('hidden');

                // Add active class to clicked button
                event.target.closest('.tab-btn').classList.add('active');
            }

            // Modal Functions
            function openEditProfileModal() {
                const modal = document.getElementById('editProfileModal');
                const modalContent = document.getElementById('editProfileModalContent');
                modal.classList.add('modal-show');
                setTimeout(() => {
                    modalContent.classList.add('modal-content-show');
                }, 10);
            }

            function closeEditProfileModal() {
                const modal = document.getElementById('editProfileModal');
                const modalContent = document.getElementById('editProfileModalContent');
                modalContent.classList.remove('modal-content-show');
                setTimeout(() => {
                    modal.classList.remove('modal-show');
                }, 300);
            }

            function saveProfile() {
                alert('✅ Profil mis à jour avec succès ! 🎉');
                closeEditProfileModal();
            }

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                // Close modal on outside click
                document.getElementById('editProfileModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditProfileModal();
                    }
                });

                // Close modal on Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeEditProfileModal();
                    }
                });
            });
        </script>
    </div>
    @endvolt    
</x-layouts.app>
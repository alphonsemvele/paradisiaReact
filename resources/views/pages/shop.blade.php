<?php

use function Laravel\Folio\{name};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

name('shop');

new class extends Component {
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $priceMin = '';
    public $priceMax = '';
    public $sortBy = 'recent';
    public $viewMode = 'grid';
    
    // Panier
    public $cart = [];
    public $showCartModal = false;
    public $showProductModal = false;
    public $selectedProduct = null;
    public $quantity = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'sortBy' => ['except' => 'recent'],
    ];

    public function mount()
    {
        $this->cart = session()->get('cart', []);
    }

    #[\Livewire\Attributes\Computed]
    public function products()
    {
        $query = Product::query()
            ->with(['user', 'categories'])
            ->where('status', 'Success');

        // Recherche
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filtre catégorie
        if ($this->categoryFilter) {
            $query->where('id_category', $this->categoryFilter);
        }

        // Filtre prix
        if ($this->priceMin) {
            $query->where('price', '>=', $this->priceMin);
        }
        if ($this->priceMax) {
            $query->where('price', '<=', $this->priceMax);
        }

        // Tri
        switch ($this->sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'popular':
                $query->orderBy('created_at', 'desc'); // À remplacer par un vrai compteur de ventes
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        return $query->paginate(12);
    }

    #[\Livewire\Attributes\Computed]
    public function categories()
    {
        return Category::withCount(['products' => function($q) {
            $q->where('status', 'Success');
        }])->orderBy('name')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function featuredProducts()
    {
        return Product::with(['categories'])
            ->where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function cartTotal()
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    #[\Livewire\Attributes\Computed]
    public function cartCount()
    {
        $count = 0;
        foreach ($this->cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'categoryFilter', 'priceMin', 'priceMax', 'sortBy']);
        $this->resetPage();
    }

    public function setCategory($categoryId)
    {
        $this->categoryFilter = $categoryId;
        $this->resetPage();
    }

    public function openProductModal($productId)
    {
        $this->selectedProduct = Product::with(['categories', 'user'])->find($productId);
        $this->quantity = 1;
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->selectedProduct = null;
        $this->quantity = 1;
    }

    public function addToCart($productId = null)
    {
        $product = $productId ? Product::find($productId) : $this->selectedProduct;
        
        if (!$product) {
            $this->dispatch('notify', ['message' => 'Produit introuvable', 'type' => 'error']);
            return;
        }

        $qty = $productId ? 1 : $this->quantity;
        $cartKey = 'product_' . $product->id;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity'] += $qty;
        } else {
            $this->cart[$cartKey] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->img_1,
                'quantity' => $qty,
            ];
        }

        session()->put('cart', $this->cart);
        $this->dispatch('notify', ['message' => $product->name . ' ajouté au panier ! 🛒', 'type' => 'success']);
        $this->closeProductModal();
    }

    public function removeFromCart($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            session()->put('cart', $this->cart);
            $this->dispatch('notify', ['message' => 'Produit retiré du panier', 'type' => 'info']);
        }
    }

    public function updateCartQuantity($cartKey, $quantity)
    {
        if (isset($this->cart[$cartKey])) {
            if ($quantity <= 0) {
                $this->removeFromCart($cartKey);
            } else {
                $this->cart[$cartKey]['quantity'] = $quantity;
                session()->put('cart', $this->cart);
            }
        }
    }

    public function incrementQuantity()
    {
        $this->quantity++;
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function openCart()
    {
        $this->showCartModal = true;
    }

    public function closeCart()
    {
        $this->showCartModal = false;
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', ['message' => 'Votre panier est vide', 'type' => 'error']);
            return;
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Rediriger vers la page de paiement
        return redirect()->route('checkout');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}; ?>

<x-layouts.app>
    @volt('boutique')
    <div x-data="{ showFilters: false }" class="min-h-screen bg-gradient-to-br from-green-50 via-white to-teal-50">
        
        <!-- Notifications -->
        <div x-data x-on:notify.window="
            if ($event.detail.type === 'success') toastr.success($event.detail.message);
            else if ($event.detail.type === 'error') toastr.error($event.detail.message);
            else toastr.info($event.detail.message);
        "></div>

        <!-- Hero Banner -->
        <div class="relative bg-gradient-to-r from-green-500 via-teal-500 to-emerald-600 overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0">
                <div class="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute bottom-10 right-10 w-40 h-40 bg-yellow-300/20 rounded-full blur-3xl"></div>
            </div>
            <div class="relative container mx-auto px-4 py-12 text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 drop-shadow-lg">
                    🛒 Boutique PARADISIA
                </h1>
                <!-- <p class="text-xl text-white/90 mb-6 max-w-2xl mx-auto">
                    Découvrez nos jus de fruits 100% naturels, préparés avec amour pour votre bien-être
                </p> -->
                
                <!-- Barre de recherche -->
                <!-- <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               placeholder="Rechercher un produit..." 
                               class="w-full pl-12 pr-4 py-4 rounded-2xl border-0 shadow-xl text-lg focus:ring-4 focus:ring-green-300/50 transition-all">
                    </div>
                </div> -->
            </div>
            
            <!-- Vague décorative -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 50L48 45.7C96 41.3 192 32.7 288 30.8C384 29 480 34 576 41.2C672 48.3 768 57.7 864 55.8C960 54 1056 41 1152 36.7C1248 32.3 1344 36.7 1392 38.8L1440 41V100H1392C1344 100 1248 100 1152 100C1056 100 960 100 864 100C768 100 672 100 576 100C480 100 384 100 288 100C192 100 96 100 48 100H0V50Z" fill="white" fill-opacity="0.1"/>
                    <path d="M0 70L48 68.3C96 66.7 192 63.3 288 61.7C384 60 480 60 576 63.3C672 66.7 768 73.3 864 75C960 76.7 1056 73.3 1152 71.7C1248 70 1344 70 1392 70L1440 70V100H1392C1344 100 1248 100 1152 100C1056 100 960 100 864 100C768 100 672 100 576 100C480 100 384 100 288 100C192 100 96 100 48 100H0V70Z" fill="white"/>
                </svg>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8" style="max-width: 1400px;">
            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Sidebar Filtres (Desktop) -->
                <div class="hidden lg:block w-72 flex-shrink-0">
                    <div class="sticky top-4 space-y-6">
                        
                        <!-- Catégories -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="text-xl">🏷️</span> Catégories
                            </h3>
                            <div class="space-y-2">
                                <button wire:click="setCategory('')" 
                                        class="w-full text-left px-4 py-2.5 rounded-xl transition-all flex items-center justify-between {{ !$categoryFilter ? 'bg-green-100 text-green-700 font-semibold' : 'hover:bg-gray-100 text-gray-700' }}">
                                    <span>Tous les produits</span>
                                    <span class="text-sm bg-gray-200 px-2 py-0.5 rounded-full">{{ $this->products->total() }}</span>
                                </button>
                                @foreach($this->categories as $category)
                                <button wire:click="setCategory({{ $category->id }})" 
                                        class="w-full text-left px-4 py-2.5 rounded-xl transition-all flex items-center justify-between {{ $categoryFilter == $category->id ? 'bg-green-100 text-green-700 font-semibold' : 'hover:bg-gray-100 text-gray-700' }}">
                                    <span>{{ $category->name }}</span>
                                    <span class="text-sm bg-gray-200 px-2 py-0.5 rounded-full">{{ $category->products_count }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Filtre Prix -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="text-xl">💰</span> Prix (FCFA)
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm text-gray-600 mb-1 block">Minimum</label>
                                    <input type="number" 
                                           wire:model.live.debounce.500ms="priceMin" 
                                           placeholder="0" 
                                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600 mb-1 block">Maximum</label>
                                    <input type="number" 
                                           wire:model.live.debounce.500ms="priceMax" 
                                           placeholder="100000" 
                                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                                </div>
                            </div>
                        </div>

                        <!-- Bouton Reset -->
                        <button wire:click="resetFilters" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Réinitialiser les filtres
                        </button>

                        <!-- Promo Card -->
                        
                    </div>
                </div>

                <!-- Contenu Principal -->
                <div class="flex-1">
                    
                    <!-- Barre d'outils -->
                    <div class="bg-white rounded-2xl shadow-lg p-4 mb-6 border border-gray-100">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <!-- Bouton filtres mobile -->
                                <button @click="showFilters = !showFilters" class="lg:hidden flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                    </svg>
                                    Filtres
                                </button>
                                
                                <p class="text-gray-600">
                                    <span class="font-semibold text-gray-800">{{ $this->products->total() }}</span> produits trouvés
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <!-- Tri -->
                                <select wire:model.live="sortBy" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 bg-white">
                                    <option value="recent">Plus récents</option>
                                    <option value="price_asc">Prix croissant</option>
                                    <option value="price_desc">Prix décroissant</option>
                                    <option value="name">Nom A-Z</option>
                                    <option value="popular">Populaires</option>
                                </select>

                                <!-- Vue grille/liste -->
                                <div class="hidden md:flex items-center bg-gray-100 rounded-xl p-1">
                                    <button wire:click="$set('viewMode', 'grid')" class="p-2 rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-white shadow text-green-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="$set('viewMode', 'list')" class="p-2 rounded-lg transition-all {{ $viewMode === 'list' ? 'bg-white shadow text-green-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres Mobile -->
                    <div x-show="showFilters" x-collapse class="lg:hidden mb-6">
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 space-y-6">
                            <!-- Catégories Mobile -->
                            <div>
                                <h3 class="font-bold text-gray-800 mb-3">Catégories</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button wire:click="setCategory('')" 
                                            class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ !$categoryFilter ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        Tous
                                    </button>
                                    @foreach($this->categories as $category)
                                    <button wire:click="setCategory({{ $category->id }})" 
                                            class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $categoryFilter == $category->id ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        {{ $category->name }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Prix Mobile -->
                            <div>
                                <h3 class="font-bold text-gray-800 mb-3">Prix (FCFA)</h3>
                                <div class="flex gap-4">
                                    <input type="number" wire:model.live.debounce.500ms="priceMin" placeholder="Min" class="flex-1 px-4 py-2 border rounded-xl">
                                    <input type="number" wire:model.live.debounce.500ms="priceMax" placeholder="Max" class="flex-1 px-4 py-2 border rounded-xl">
                                </div>
                            </div>
                            
                            <button wire:click="resetFilters" @click="showFilters = false" class="w-full bg-gray-100 text-gray-700 font-semibold py-3 rounded-xl">
                                Réinitialiser
                            </button>
                        </div>
                    </div>

                    <!-- Grille Produits -->
                    @if($viewMode === 'grid')
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($this->products as $product)
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 group">
                            <!-- Image -->
                            <div class="relative overflow-hidden">
                                @if($product->img_1)
                                    <img src="{{ Storage::url($product->img_1) }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-56 object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-56 bg-gradient-to-br from-green-100 to-teal-100 flex items-center justify-center">
                                        <span class="text-7xl">🍹</span>
                                    </div>
                                @endif
                                
                                <!-- Badges -->
                                <div class="absolute top-3 left-3 flex flex-col gap-2">
                                    @if($product->categories)
                                        <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                                            {{ $product->categories->name }}
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Quick Actions -->
                                <div class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="openProductModal({{ $product->id }})" 
                                            class="w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center text-gray-700 hover:bg-green-500 hover:text-white transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="addToCart({{ $product->id }})" 
                                            class="w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center text-gray-700 hover:bg-green-500 hover:text-white transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Contenu -->
                            <div class="p-5">
                                <h3 class="font-bold text-gray-800 text-lg mb-2 line-clamp-1 group-hover:text-green-600 transition-colors">
                                    {{ $product->name }}
                                </h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                    {{ $product->description }}
                                </p>
                                
                                <div class="flex items-center justify-between">
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ number_format($product->price, 0, ',', ' ') }} <span class="text-sm font-normal">FCFA</span>
                                    </p>
                                    <button wire:click="addToCart({{ $product->id }})" 
                                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl font-semibold transition-all flex items-center gap-2 shadow-lg shadow-green-500/30">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Ajouter
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full">
                            <div class="bg-white rounded-2xl shadow-lg p-12 text-center border border-gray-100">
                                <div class="text-6xl mb-4">🔍</div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Aucun produit trouvé</h3>
                                <p class="text-gray-600 mb-6">Essayez de modifier vos critères de recherche</p>
                                <button wire:click="resetFilters" class="bg-green-500 text-white font-semibold py-3 px-8 rounded-xl hover:bg-green-600 transition-all">
                                    Voir tous les produits
                                </button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    @else
                    <!-- Vue Liste -->
                    <div class="space-y-4">
                        @forelse($this->products as $product)
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition-all flex flex-col md:flex-row">
                            <div class="md:w-64 flex-shrink-0">
                                @if($product->img_1)
                                    <img src="{{ Storage::url($product->img_1) }}" alt="{{ $product->name }}" class="w-full h-48 md:h-full object-cover">
                                @else
                                    <div class="w-full h-48 md:h-full bg-gradient-to-br from-green-100 to-teal-100 flex items-center justify-center">
                                        <span class="text-6xl">🍹</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 p-6 flex flex-col justify-between">
                                <div>
                                    @if($product->categories)
                                        <span class="inline-block bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full mb-2">
                                            {{ $product->categories->name }}
                                        </span>
                                    @endif
                                    <h3 class="font-bold text-gray-800 text-xl mb-2">{{ $product->name }}</h3>
                                    <p class="text-gray-600 mb-4">{{ $product->description }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-2xl font-bold text-green-600">{{ number_format($product->price, 0, ',', ' ') }} FCFA</p>
                                    <div class="flex gap-3">
                                        <button wire:click="openProductModal({{ $product->id }})" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                                            Voir détails
                                        </button>
                                        <button wire:click="addToCart({{ $product->id }})" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl font-semibold transition-all">
                                            Ajouter au panier
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                            <div class="text-6xl mb-4">🔍</div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Aucun produit trouvé</h3>
                            <button wire:click="resetFilters" class="bg-green-500 text-white font-semibold py-3 px-8 rounded-xl hover:bg-green-600 transition-all">
                                Voir tous les produits
                            </button>
                        </div>
                        @endforelse
                    </div>
                    @endif

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $this->products->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton Panier Flottant -->
        <button wire:click="openCart" class="fixed bottom-6 right-6 z-40 bg-green-500 hover:bg-green-600 text-white p-4 rounded-full shadow-2xl transition-all transform hover:scale-110 flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            @if($this->cartCount > 0)
            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">
                {{ $this->cartCount }}
            </span>
            @endif
        </button>

        <!-- Modal Produit -->
        @if($showProductModal && $selectedProduct)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.style.overflow = 'hidden'" x-on:close-modal.window="document.body.style.overflow = 'auto'">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeProductModal"></div>
                
                <div class="relative bg-white rounded-3xl shadow-2xl max-w-4xl w-full overflow-hidden">
                    <button wire:click="closeProductModal" class="absolute top-4 right-4 z-10 p-2 bg-white/80 hover:bg-white rounded-full shadow-lg transition-all">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    
                    <div class="flex flex-col md:flex-row">
                        <!-- Image -->
                        <div class="md:w-1/2">
                            @if($selectedProduct->img_1)
                                <img src="{{ Storage::url($selectedProduct->img_1) }}" alt="{{ $selectedProduct->name }}" class="w-full h-72 md:h-full object-cover">
                            @else
                                <div class="w-full h-72 md:h-full bg-gradient-to-br from-green-100 to-teal-100 flex items-center justify-center">
                                    <span class="text-8xl">🍹</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Détails -->
                        <div class="md:w-1/2 p-8">
                            @if($selectedProduct->categories)
                                <span class="inline-block bg-green-100 text-green-700 text-sm font-bold px-4 py-1 rounded-full mb-4">
                                    {{ $selectedProduct->categories->name }}
                                </span>
                            @endif
                            
                            <h2 class="text-3xl font-bold text-gray-800 mb-4">{{ $selectedProduct->name }}</h2>
                            <p class="text-gray-600 mb-6 leading-relaxed">{{ $selectedProduct->description }}</p>
                            
                            <div class="mb-6">
                                <p class="text-4xl font-bold text-green-600">
                                    {{ number_format($selectedProduct->price, 0, ',', ' ') }} <span class="text-lg font-normal">FCFA</span>
                                </p>
                            </div>
                            
                            <!-- Quantité -->
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Quantité</label>
                                <div class="flex items-center gap-4">
                                    <button wire:click="decrementQuantity" class="w-12 h-12 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-2xl font-bold text-gray-700 transition-all">
                                        -
                                    </button>
                                    <span class="text-2xl font-bold text-gray-800 w-12 text-center">{{ $quantity }}</span>
                                    <button wire:click="incrementQuantity" class="w-12 h-12 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-2xl font-bold text-gray-700 transition-all">
                                        +
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Total -->
                            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Total</span>
                                    <span class="text-2xl font-bold text-green-600">{{ number_format($selectedProduct->price * $quantity, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                            
                            <!-- Boutons -->
                            <div class="flex gap-4">
                                <button wire:click="addToCart" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-green-500/30">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Ajouter au panier
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Modal Panier -->
        @if($showCartModal)
        <div class="fixed inset-0 z-50 overflow-hidden">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeCart"></div>
            
            <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col">
                <!-- Header -->
                <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-green-500 to-emerald-600 text-white">
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Mon Panier ({{ $this->cartCount }})
                    </h3>
                    <button wire:click="closeCart" class="p-2 hover:bg-white/20 rounded-lg transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Contenu -->
                <div class="flex-1 overflow-y-auto p-6">
                    @if(count($cart) > 0)
                    <div class="space-y-4">
                        @foreach($cart as $key => $item)
                        <div class="flex gap-4 p-4 bg-gray-50 rounded-xl">
                            @if($item['image'])
                                <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" class="w-20 h-20 object-cover rounded-lg">
                            @else
                                <div class="w-20 h-20 bg-green-100 rounded-lg flex items-center justify-center">
                                    <span class="text-3xl">🍹</span>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800 line-clamp-1">{{ $item['name'] }}</h4>
                                <p class="text-green-600 font-bold">{{ number_format($item['price'], 0, ',', ' ') }} FCFA</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <button wire:click="updateCartQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-lg flex items-center justify-center text-gray-700 font-bold transition-all">-</button>
                                    <span class="w-8 text-center font-semibold">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateCartQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-lg flex items-center justify-center text-gray-700 font-bold transition-all">+</button>
                                </div>
                            </div>
                            <div class="flex flex-col items-end justify-between">
                                <button wire:click="removeFromCart('{{ $key }}')" class="text-red-500 hover:text-red-700 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <p class="font-bold text-gray-800">{{ number_format($item['price'] * $item['quantity'], 0, ',', ' ') }} FCFA</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🛒</div>
                        <h4 class="text-xl font-bold text-gray-800 mb-2">Votre panier est vide</h4>
                        <p class="text-gray-600 mb-6">Découvrez nos délicieux produits !</p>
                        <button wire:click="closeCart" class="bg-green-500 text-white font-semibold py-3 px-8 rounded-xl hover:bg-green-600 transition-all">
                            Continuer mes achats
                        </button>
                    </div>
                    @endif
                </div>
                
                <!-- Footer -->
                @if(count($cart) > 0)
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-lg text-gray-600">Total</span>
                        <span class="text-2xl font-bold text-green-600">{{ number_format($this->cartTotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <button wire:click="checkout" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                        Passer la commande
                    </button>
                    <button wire:click="closeCart" class="w-full mt-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-xl transition-all">
                        Continuer mes achats
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Toastr -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3000 };
        </script>
    </div>
    @endvolt
</x-layouts.app>
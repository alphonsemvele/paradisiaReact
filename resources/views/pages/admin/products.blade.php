<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

middleware(['auth']);

name('admin.products');

new class extends Component {
    use WithPagination, WithFileUploads;

    // Filtres
    public $search = '';
    public $statusFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    // Modal CRUD
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $productId = null;

    // Champs formulaire
    public $name = '';
    public $description = '';
    public $status = 'pending';
    public $price = 0;
    public $id_category = '';
    public $img_1;              // Fichier upload image 1
    public $img_2;              // Fichier upload image 2 (optionnel)
    public $existingImg1 = '';  // Chemin actuel img_1 (édition)
    public $existingImg2 = '';  // Chemin actuel img_2 (édition)

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function rules()
    {
        $rules = [
            'name'        => 'required|string|min:3|max:120',
            'description' => 'nullable|string|max:1500',
            'status'      => 'required|in:pending,Success,failed',
            'price'       => 'required|numeric|min:0',
            'id_category' => 'required|exists:categories,id',
        ];

        // img_1 obligatoire à la création, optionnel à l'édition
        $rules['img_1'] = $this->editMode ? 'nullable|image|max:5120' : 'required|image|max:5120';
        $rules['img_2'] = 'nullable|image|max:5120';

        return $rules;
    }

    #[\Livewire\Attributes\Computed]
    public function products()
    {
        return Product::query()
            ->with(['user', 'categories'])
            ->when($this->search, fn($q) => 
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
            )
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[\Livewire\Attributes\Computed]
    public function categories()
    {
        return Category::where('status', 'Success')
            ->orderBy('name')
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function stats()
    {
        return [
            'total'   => Product::count(),
            'pending' => Product::where('status', 'pending')->count(),
            'success' => Product::where('status', 'Success')->count(),
            'failed'  => Product::where('status', 'failed')->count(),
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        $this->productId     = $id;
        $this->name          = $product->name;
        $this->description   = $product->description;
        $this->status        = $product->status;
        $this->price         = $product->price;
        $this->id_category   = $product->id_category;
        $this->existingImg1  = $product->img_1;
        $this->existingImg2  = $product->img_2;
        $this->editMode      = true;
        $this->showModal     = true;
    }

    public function saveProduct()
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'status'      => $this->status,
            'price'       => $this->price,
            'id_category' => $this->id_category,
            'id_user'     => Auth::id(),
        ];

        // Gestion image principale
        if ($this->img_1) {
            // Supprimer l'ancienne si édition
            if ($this->editMode && $this->existingImg1) {
                Storage::disk('public')->delete($this->existingImg1);
            }
            $data['img_1'] = $this->img_1->store('products/images', 'public');
        }

        // Gestion image secondaire (optionnelle)
        if ($this->img_2) {
            if ($this->editMode && $this->existingImg2) {
                Storage::disk('public')->delete($this->existingImg2);
            }
            $data['img_2'] = $this->img_2->store('products/images', 'public');
        }

        if ($this->editMode) {
            Product::find($this->productId)->update($data);
            session()->flash('success', 'Produit mis à jour avec succès !');
        } else {
            Product::create($data);
            session()->flash('success', 'Produit créé avec succès !');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->productId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteProduct()
    {
        $product = Product::findOrFail($this->productId);

        // Supprimer les images du disque
        if ($product->img_1) Storage::disk('public')->delete($product->img_1);
        if ($product->img_2) Storage::disk('public')->delete($product->img_2);

        $product->delete();

        session()->flash('success', 'Produit supprimé avec succès !');
        $this->showDeleteModal = false;
        $this->productId = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'productId', 'name', 'description', 'status', 'price', 'id_category',
            'img_1', 'img_2', 'existingImg1', 'existingImg2', 'editMode'
        ]);
        $this->status = 'pending';
        $this->price = 0;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}; ?>

<x-layouts.admin>
    @volt('admin.products')
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Produits</h1>
                <p class="text-sm text-gray-500 mt-1">Gérez tous les produits de la plateforme</p>
            </div>
            <button wire:click="openModal" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-400 text-white font-semibold rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg shadow-amber-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouveau Produit
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($this->stats['total']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Total produits</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($this->stats['pending']) }}</p>
                <p class="text-xs text-gray-500 mt-1">En attente</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($this->stats['success']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Validés</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p class="text-2xl font-bold text-red-600">{{ number_format($this->stats['failed']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Rejetés</p>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher par nom ou description..." class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                    </div>
                </div>

                <div>
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                        <option value="">Tous les statuts</option>
                        <option value="pending">⏳ En attente</option>
                        <option value="Success">✅ Validé</option>
                        <option value="failed">❌ Rejeté</option>
                    </select>
                </div>

                <div>
                    <button wire:click="resetFilters" class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all font-medium">
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <!-- Tableau des produits -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-max">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-4 text-left">
                                <button wire:click="sortBy('status')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    Statut
                                    @if($sortBy === 'status')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    Date
                                    @if($sortBy === 'created_at')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->products as $product)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4">
                                @if($product->img_1)
                                    <img src="{{ Storage::url($product->img_1) }}" alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded-lg shadow-sm border border-gray-200">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center text-gray-500 text-xs">Pas d'image</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ Str::limit($product->name, 40) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $product->categories?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                {{ number_format($product->price, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4">
                                @if($product->status === 'pending')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⏳ En attente</span>
                                @elseif($product->status === 'Success')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">✅ Validé</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">❌ Rejeté</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $product->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="editProduct({{ $product->id }})" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Modifier">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $product->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Supprimer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4a2 2 0 00-2 2v3m-4-5v5m-8-5h4a2 2 0 002-2v-3"></path>
                                </svg>
                                Aucun produit trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Afficher</span>
                    <select wire:model.live="perPage" class="border border-gray-200 rounded-lg text-sm px-3 py-1.5 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                        <option>10</option>
                        <option>15</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                    <span class="text-sm text-gray-600">par page</span>
                </div>
                {{ $this->products->links() }}
            </div>
        </div>

        <!-- Modal Création / Édition -->
        @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all w-full max-w-2xl mx-auto overflow-hidden">
                    <!-- Header modal -->
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold">
                                {{ $editMode ? 'Modifier le produit' : 'Nouveau produit' }}
                            </h3>
                            <button wire:click="closeModal" class="p-1 hover:bg-white/20 rounded-lg transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Contenu formulaire -->
                    <form wire:submit="saveProduct" class="p-6 max-h-[75vh] overflow-y-auto space-y-6">
                        <!-- Nom -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom du produit *</label>
                            <input type="text" wire:model="name" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" placeholder="Ex: T-shirt Premium Homme">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                            <textarea wire:model="description" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none" placeholder="Décrivez les caractéristiques, matériaux, tailles..."></textarea>
                        </div>

                        <!-- Prix + Catégorie -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Prix (FCFA) *</label>
                                <input type="number" wire:model="price" min="0" step="100" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                                @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Catégorie *</label>
                                <select wire:model="id_category" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                                    <option value="">— Sélectionner une catégorie —</option>
                                    @foreach($this->categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('id_category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Statut *</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'pending' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model="status" value="pending" class="text-yellow-600 focus:ring-yellow-500">
                                    <span class="text-sm">⏳ En attente</span>
                                </label>
                                <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'Success' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model="status" value="Success" class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm">✅ Validé</span>
                                </label>
                                <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'failed' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model="status" value="failed" class="text-red-600 focus:ring-red-500">
                                    <span class="text-sm">❌ Rejeté</span>
                                </label>
                            </div>
                            @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Images -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Image principale * {{ $editMode ? '(laissez vide pour conserver l\'actuelle)' : '' }}
                                </label>
                                <input type="file" wire:model="img_1" accept="image/*" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                                @error('img_1') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                                @if($editMode && $existingImg1)
                                    <div class="mt-3">
                                        <img src="{{ Storage::url($existingImg1) }}" class="w-32 h-32 object-cover rounded-lg shadow-sm border border-gray-200" alt="Image actuelle principale">
                                        <p class="text-xs text-gray-500 mt-1">Image actuelle</p>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Image secondaire (optionnelle)</label>
                                <input type="file" wire:model="img_2" accept="image/*" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                                @error('img_2') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                                @if($editMode && $existingImg2)
                                    <div class="mt-3">
                                        <img src="{{ Storage::url($existingImg2) }}" class="w-32 h-32 object-cover rounded-lg shadow-sm border border-gray-200" alt="Image actuelle secondaire">
                                        <p class="text-xs text-gray-500 mt-1">Image actuelle</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                                Annuler
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="saveProduct">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="saveProduct">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ $editMode ? 'Mettre à jour' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Modal Suppression -->
        @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="fixed inset-0 bg-gray-900/60"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer ce produit ?</h3>
                <p class="text-sm text-gray-500 mb-6">Les images associées seront également supprimées. Cette action est irréversible.</p>

                <div class="flex justify-center gap-4">
                    <button wire:click="$set('showDeleteModal', false)" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all">
                        Annuler
                    </button>
                    <button wire:click="deleteProduct" class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all flex items-center gap-2">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endvolt
</x-layouts.admin>
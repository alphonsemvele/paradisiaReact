<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

middleware(['auth']);

name('admin.categories');

new class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $categoryId = null;

    public $name = '';
    public $description = '';
    public $status = 'pending';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function rules()
    {
        return [
            'name'        => 'required|string|min:2|max:100|unique:categories,name,' . $this->categoryId,
            'description' => 'nullable|string|max:500',
            'status'      => 'required|in:pending,Success,failed',
        ];
    }

    #[\Livewire\Attributes\Computed]
    public function categories()
    {
        return Category::query()
            ->withCount('products')  // ← Ajout du compte de produits
            ->when($this->search, fn($q) => 
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
            )
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortBy === 'products_count' ? 'products_count' : $this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[\Livewire\Attributes\Computed]
    public function stats()
    {
        return [
            'total'   => Category::count(),
            'pending' => Category::where('status', 'pending')->count(),
            'success' => Category::where('status', 'Success')->count(),
            'failed'  => Category::where('status', 'failed')->count(),
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

    public function editCategory($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId   = $id;
        $this->name         = $category->name;
        $this->description  = $category->description;
        $this->status       = $category->status;
        $this->editMode     = true;
        $this->showModal    = true;
    }

    public function saveCategory()
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'status'      => $this->status,
            'id_user'     => Auth::id(),
        ];

        if ($this->editMode) {
            Category::find($this->categoryId)->update($data);
            session()->flash('success', 'Catégorie mise à jour !');
        } else {
            Category::create($data);
            session()->flash('success', 'Catégorie créée !');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->categoryId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteCategory()
    {
        Category::findOrFail($this->categoryId)->delete();
        session()->flash('success', 'Catégorie supprimée !');
        $this->showDeleteModal = false;
        $this->categoryId = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'categoryId', 'name', 'description', 'status', 'editMode'
        ]);
        $this->status = 'pending';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}; ?>

<x-layouts.admin>
    @volt('admin.categories')
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Catégories</h1>
                <p class="text-sm text-gray-500 mt-1">Gérez vos catégories</p>
            </div>
            <button wire:click="openModal" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvelle Catégorie
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($this->stats['total']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Total</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($this->stats['pending']) }}</p>
                <p class="text-xs text-gray-500 mt-1">En attente</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($this->stats['success']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Validées</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <p class="text-2xl font-bold text-red-600">{{ number_format($this->stats['failed']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Rejetées</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher par nom ou description..." class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="">Tous statuts</option>
                        <option value="pending">⏳ En attente</option>
                        <option value="Success">✅ Validée</option>
                        <option value="failed">❌ Rejetée</option>
                    </select>
                </div>

                <div>
                    <button wire:click="resetFilters" class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200">
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <!-- Tableau avec nombre de produits -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-max">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <button wire:click="sortBy('id')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase hover:text-gray-700">
                                    ID
                                    @if($sortBy === 'id') <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> @endif
                                </button>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-4 text-left">
                                <button wire:click="sortBy('products_count')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase hover:text-gray-700">
                                    Produits
                                    @if($sortBy === 'products_count') <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> @endif
                                </button>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <button wire:click="sortBy('status')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase hover:text-gray-700">
                                    Statut
                                    @if($sortBy === 'status') <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> @endif
                                </button>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase hover:text-gray-700">
                                    Créée le
                                    @if($sortBy === 'created_at') <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> @endif
                                </button>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->categories as $category)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-6 py-4 text-sm font-mono text-gray-500">#{{ $category->id }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 line-clamp-2 max-w-xs">
                                {{ $category->description ? Str::limit($category->description, 80) : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($category->products_count > 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                        {{ $category->products_count }}
                                        <span class="ml-1 text-xs opacity-75">produit{{ $category->products_count > 1 ? 's' : '' }}</span>
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($category->status === 'pending')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⏳ En attente</span>
                                @elseif($category->status === 'Success')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">✅ Validée</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">❌ Rejetée</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $category->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="editCategory({{ $category->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Modifier">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $category->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Supprimer">
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
                                Aucune catégorie trouvée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Afficher</span>
                    <select wire:model.live="perPage" class="border border-gray-200 rounded-lg text-sm px-3 py-1.5">
                        <option>10</option>
                        <option>15</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                    <span class="text-sm text-gray-600">par page</span>
                </div>
                {{ $this->categories->links() }}
            </div>
        </div>

        <!-- Modal (inchangé) -->
        @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="fixed inset-0 bg-gray-900/60" wire:click="closeModal"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                    <h3 class="text-lg font-bold">{{ $editMode ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</h3>
                </div>

                <form wire:submit="saveCategory" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de la catégorie *</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="Ex: Premium, Éducation...">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea wire:model="description" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="Description optionnelle..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Statut *</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'pending' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model="status" value="pending" class="text-yellow-600 focus:ring-yellow-500">
                                <span class="text-sm">⏳ En attente</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'Success' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model="status" value="Success" class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm">✅ Validée</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'failed' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model="status" value="failed" class="text-red-600 focus:ring-red-500">
                                <span class="text-sm">❌ Rejetée</span>
                            </label>
                        </div>
                        @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">
                            Annuler
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">
                            {{ $editMode ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Delete Modal -->
        @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="fixed inset-0 bg-gray-900/60"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold mb-2">Supprimer cette catégorie ?</h3>
                <p class="text-sm text-gray-500 mb-6">Cette action est irréversible.</p>

                <div class="flex justify-center gap-4">
                    <button wire:click="$set('showDeleteModal', false)" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200">
                        Annuler
                    </button>
                    <button wire:click="deleteCategory" class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endvolt
</x-layouts.admin>
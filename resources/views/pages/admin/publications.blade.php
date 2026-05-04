<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Publication;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

middleware(['auth']);

name('admin.publications');

new class extends Component {
    use WithPagination, WithFileUploads;

    // Filtres
    public $search = '';
    public $statusFilter = '';
    public $pageFilter = '';
    public $mediaFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    // Modal CRUD
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $publicationId = null;

    // Formulaire
    public $title = '';
    public $text = '';
    public $status = 'pending';
    public $id_page = '';
    public $img_1;
    public $img_2;
    public $img_3;
    public $img_4;
    public $img_5;
    public $existingImg1 = '';
    public $existingImg2 = '';
    public $existingImg3 = '';
    public $existingImg4 = '';
    public $existingImg5 = '';

    // Vue détaillée
    public $viewPublication = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function rules()
    {
        return [
            'title'     => 'required|string|min:3|max:150',
            'text'      => 'required|string|min:1',
            'status'    => 'required|in:pending,Success,failed,waiting',
            'id_page'   => 'nullable|exists:pages,id',
            'img_1'     => $this->editMode ? 'nullable|image|max:5120' : 'required|image|max:5120',
            'img_2'     => 'nullable|image|max:5120',
            'img_3'     => 'nullable|image|max:5120',
            'img_4'     => 'nullable|image|max:5120',
            'img_5'     => 'nullable|image|max:5120',
        ];
    }

    #[\Livewire\Attributes\Computed]
    public function publications()
    {
        return Publication::query()
            ->with(['user', 'page', 'comments', 'likes', 'shares', 'views'])
            ->when($this->search, fn($q) => 
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('text', 'like', '%' . $this->search . '%')
            )
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->pageFilter, fn($q) => $q->where('id_page', $this->pageFilter))
            ->when($this->mediaFilter === 'images', fn($q) => $q->whereNotNull('img_1'))
            ->when($this->mediaFilter === 'videos', fn($q) => $q->whereNotNull('video'))
            ->when($this->mediaFilter === 'text', fn($q) => $q->whereNull('img_1')->whereNull('video'))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[\Livewire\Attributes\Computed]
    public function pages()
    {
        return Page::orderBy('name')->get(['id', 'name']);
    }

    #[\Livewire\Attributes\Computed]
    public function stats()
    {
        return [
            'total'     => Publication::count(),
            'active'    => Publication::where('status', 'Success')->count(),
            'pending'   => Publication::where('status', 'pending')->count(),
            'today'     => Publication::whereDate('created_at', today())->count(),
            'totalLikes'    => \App\Models\Like::whereNotNull('id_publication')->count(),
            'totalComments' => \App\Models\Comment::whereNotNull('id_publication')->count(),
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
        $this->reset(['search', 'statusFilter', 'pageFilter', 'mediaFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editPublication($id)
    {
        $publication = Publication::findOrFail($id);
        $this->publicationId  = $id;
        $this->title          = $publication->title ?? '';
        $this->text           = $publication->text ?? $publication->text ?? '';
        $this->status         = $publication->status;
        $this->id_page        = $publication->id_page;
        $this->existingImg1   = $publication->img_1;
        $this->existingImg2   = $publication->img_2;
        $this->existingImg3   = $publication->img_3;
        $this->existingImg4   = $publication->img_4;
        $this->existingImg5   = $publication->img_5;
        $this->editMode       = true;
        $this->showModal      = true;
    }

    public function viewPublication($id)
    {
        $this->viewPublication = Publication::with(['user', 'page', 'comments.user', 'comments.replies.user', 'likes.user', 'shares', 'views'])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function savePublication()
    {
        $this->validate();

        $data = [
            'title'       => $this->title,
            'text'        => $this->text,
            'status'      => $this->status,
            'id_user'     => Auth::id(),
            'id_page'     => $this->id_page ?: null,
        ];

        // Gestion des images 1 à 5
        foreach (range(1, 5) as $i) {
            $field = "img_{$i}";
            $existing = "existingImg{$i}";

            if ($this->$field) {
                if ($this->editMode && $this->$existing) {
                    Storage::disk('public')->delete($this->$existing);
                }
                $data[$field] = $this->$field->store('publications/images', 'public');
            }
        }

        if ($this->editMode) {
            Publication::find($this->publicationId)->update($data);
            session()->flash('success', 'Publication mise à jour avec succès !');
        } else {
            Publication::create($data);
            session()->flash('success', 'Publication créée avec succès !');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->publicationId = $id;
        $this->showDeleteModal = true;
    }

    public function deletePublication()
    {
        $publication = Publication::findOrFail($this->publicationId);

        foreach (range(1, 5) as $i) {
            $field = "img_{$i}";
            if ($publication->$field) {
                Storage::disk('public')->delete($publication->$field);
            }
        }

        $publication->delete();
        session()->flash('success', 'Publication supprimée avec succès !');
        $this->showDeleteModal = false;
        $this->publicationId = null;
    }

    public function updateStatus($id, $status)
    {
        Publication::find($id)->update(['status' => $status]);
        session()->flash('success', 'Statut mis à jour !');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showViewModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'publicationId', 'title', 'text', 'id_page',
            'img_1', 'img_2', 'img_3', 'img_4', 'img_5',
            'existingImg1', 'existingImg2', 'existingImg3', 'existingImg4', 'existingImg5',
            'editMode'
        ]);
        $this->status = 'pending';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}; ?>

<x-layouts.admin>
    @volt('admin.publications')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Publications</h1>
                <p class="text-sm text-gray-500 mt-1">Gérez toutes les publications de la plateforme</p>
            </div>
            <button wire:click="openModal" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-400 text-white font-semibold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all shadow-lg shadow-green-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvelle Publication
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($this->stats['total']) }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($this->stats['active']) }}</p>
                        <p class="text-xs text-gray-500">Actives</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($this->stats['pending']) }}</p>
                        <p class="text-xs text-gray-500">En attente</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($this->stats['today']) }}</p>
                        <p class="text-xs text-gray-500">Aujourd'hui</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($this->stats['totalLikes']) }}</p>
                        <p class="text-xs text-gray-500">Likes</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($this->stats['totalComments']) }}</p>
                        <p class="text-xs text-gray-500">Commentaires</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters (sans filtre auteur) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher titre ou contenu..." class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                        <option value="">Tous les statuts</option>
                        <option value="Success">✅ Validée</option>
                        <option value="pending">⏳ En attente</option>
                        <option value="failed">❌ Rejetée</option>
                        <option value="waiting">🔄 En révision</option>
                    </select>
                </div>

                <!-- Page Filter -->
                <div>
                    <select wire:model.live="pageFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                        <option value="">Toutes les pages</option>
                        @foreach($this->pages as $page)
                            <option value="{{ $page->id }}">{{ $page->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Media Filter -->
                <div>
                    <select wire:model.live="mediaFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                        <option value="">Tous les médias</option>
                        <option value="images">🖼️ Avec images</option>
                        <option value="videos">🎬 Avec vidéos</option>
                        <option value="text">📝 Texte seul</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <input type="date" wire:model.live="dateFrom" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all" placeholder="Date début">
                </div>

                <!-- Reset -->
                <div>
                    <button wire:click="resetFilters" class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all font-medium">
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <!-- Publications Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <button wire:click="sortBy('id')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    ID
                                    @if($sortBy === 'id')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ref</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Titre</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contenu</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Images</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Engagement</th>
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
                        @forelse($this->publications as $publication)
                        <tr class="hover:bg-gray-50/50 transition-all">
                            <td class="px-6 py-4">
                                <span class="text-sm font-mono text-gray-500">#{{ $publication->id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-mono text-gray-600">{{ $publication->ref }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ Str::limit($publication->title ?? 'Sans titre', 60) }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-700 line-clamp-2 max-w-xs">{{ Str::limit($publication->text, 100) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @php
                                        $imagesCount = 0;
                                        for ($i = 1; $i <= 5; $i++) {
                                            $imgField = "img_{$i}";
                                            if ($publication->$imgField) $imagesCount++;
                                        }
                                    @endphp
                                    @if($imagesCount > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg">
                                            🖼️ {{ $imagesCount }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Aucune image</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="inline-flex items-center gap-1 text-red-600" title="Likes">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        {{ $publication->likes->count() }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-blue-600" title="Commentaires">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        {{ $publication->comments->count() }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-green-600" title="Partages">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                        </svg>
                                        {{ $publication->shares->count() }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all
                                        @if($publication->status === 'Success') bg-green-100 text-green-700 hover:bg-green-200
                                        @elseif($publication->status === 'pending') bg-yellow-100 text-yellow-700 hover:bg-yellow-200
                                        @elseif($publication->status === 'failed') bg-red-100 text-red-700 hover:bg-red-200
                                        @else bg-gray-100 text-gray-700 hover:bg-gray-200
                                        @endif">
                                        @if($publication->status === 'Success') ✅ Validée
                                        @elseif($publication->status === 'pending') ⏳ En attente
                                        @elseif($publication->status === 'failed') ❌ Rejetée
                                        @else 🔄 En révision
                                        @endif
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition class="absolute z-20 mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1">
                                        <button wire:click="updateStatus({{ $publication->id }}, 'Success')" class="w-full px-4 py-2 text-left text-sm hover:bg-green-50 text-green-700">✅ Valider</button>
                                        <button wire:click="updateStatus({{ $publication->id }}, 'pending')" class="w-full px-4 py-2 text-left text-sm hover:bg-yellow-50 text-yellow-700">⏳ En attente</button>
                                        <button wire:click="updateStatus({{ $publication->id }}, 'failed')" class="w-full px-4 py-2 text-left text-sm hover:bg-red-50 text-red-700">❌ Rejeter</button>
                                        <button wire:click="updateStatus({{ $publication->id }}, 'waiting')" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 text-gray-700">🔄 En révision</button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm text-gray-800">{{ $publication->created_at->format('d/m/Y') }}</span>
                                    <span class="text-xs text-gray-500">{{ $publication->created_at->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <button wire:click="viewPublication({{ $publication->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="editPublication({{ $publication->id }})" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $publication->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-gray-500">Aucune publication trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Afficher</span>
                    <select wire:model.live="perPage" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span class="text-sm text-gray-500">par page</span>
                </div>
                <div>
                    {{ $this->publications->links() }}
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all w-full max-w-2xl mx-auto overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold">
                                {{ $editMode ? 'Modifier la publication' : 'Nouvelle publication' }}
                            </h3>
                            <button wire:click="closeModal" class="p-1 hover:bg-white/20 rounded-lg transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Form -->
                    <form wire:submit="savePublication" class="p-6 max-h-[70vh] overflow-y-auto">
                        <div class="space-y-5">
                            <!-- Titre -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Titre *</label>
                                <input type="text" wire:model="title" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all" placeholder="Titre de la publication...">
                                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Contenu -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Contenu *</label>
                                <textarea wire:model="text" rows="5" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all resize-none" placeholder="Écrivez votre publication..."></textarea>
                                @error('text') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Page (optional) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Page (optionnel)</label>
                                <select wire:model="id_page" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                    <option value="">Aucune page</option>
                                    @foreach($this->pages as $page)
                                        <option value="{{ $page->id }}">{{ $page->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Statut -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Statut *</label>
                                <div class="flex flex-wrap gap-3">
                                    <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'Success' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300' }}">
                                        <input type="radio" wire:model="status" value="Success" class="text-green-600 focus:ring-green-500">
                                        <span class="text-sm">✅ Validée</span>
                                    </label>
                                    <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'pending' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 hover:border-gray-300' }}">
                                        <input type="radio" wire:model="status" value="pending" class="text-yellow-600 focus:ring-yellow-500">
                                        <span class="text-sm">⏳ En attente</span>
                                    </label>
                                    <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'failed' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                                        <input type="radio" wire:model="status" value="failed" class="text-red-600 focus:ring-red-500">
                                        <span class="text-sm">❌ Rejetée</span>
                                    </label>
                                    <label class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer transition-all {{ $status === 'waiting' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                                        <input type="radio" wire:model="status" value="waiting" class="text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm">🔄 En révision</span>
                                    </label>
                                </div>
                                @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Images -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @for($i = 1; $i <= 5; $i++)
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Image {{ $i }} {{ $i === 1 ? '*' : '(optionnelle)' }}
                                            @if($editMode && ${"existingImg{$i}"})
                                                <span class="text-xs text-gray-500">(actuelle)</span>
                                            @endif
                                        </label>
                                        <input type="file" wire:model="img_{{ $i }}" accept="image/*" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                                        @error("img_{$i}") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                                        @if($editMode && ${"existingImg{$i}"})
                                            <div class="mt-3">
                                                <img src="{{ Storage::url(${"existingImg{$i}"}) }}" class="w-24 h-24 object-cover rounded-xl shadow-sm">
                                            </div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                                Annuler
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-green-400 rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="savePublication">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="savePublication">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ $editMode ? 'Mettre à jour' : 'Publier' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- View Modal -->
        @if($showViewModal && $viewPublication)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all w-full max-w-3xl mx-auto overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold">Détails de la publication #{{ $viewPublication->id }}</h3>
                            <button wire:click="closeModal" class="p-1 hover:bg-white/20 rounded-lg transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                        <!-- Titre -->
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            {{ $viewPublication->title ?? 'Sans titre' }}
                        </h2>

                        <!-- Ref -->
                        <p class="text-sm text-gray-600 mb-4">
                            Référence : <strong>{{ $viewPublication->ref }}</strong>
                        </p>

                        <!-- text -->
                        <div class="mb-6">
                            <p class="text-gray-800 whitespace-pre-wrap">{{ $viewPublication->text }}</p>
                        </div>

                        <!-- Images -->
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            @for($i = 1; $i <= 5; $i++)
                                @php $img = "img_{$i}"; @endphp
                                @if($viewPublication->$img)
                                    <img src="{{ Storage::url($viewPublication->$img) }}" class="w-full h-32 object-cover rounded-xl">
                                @endif
                            @endfor
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-red-600">{{ $viewPublication->likes->count() }}</p>
                                <p class="text-xs text-gray-500">Likes</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-blue-600">{{ $viewPublication->comments->count() }}</p>
                                <p class="text-xs text-gray-500">Commentaires</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600">{{ $viewPublication->shares->count() }}</p>
                                <p class="text-xs text-gray-500">Partages</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-purple-600">{{ $viewPublication->views->count() }}</p>
                                <p class="text-xs text-gray-500">Vues</p>
                            </div>
                        </div>

                        <!-- Recent Comments -->
                        @if($viewPublication->comments->count() > 0)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">💬 Commentaires récents</h4>
                            <div class="space-y-3 max-h-48 overflow-y-auto">
                                @foreach($viewPublication->comments->take(5) as $comment)
                                <div class="flex gap-3 p-3 bg-gray-50 rounded-xl">
                                    <img src="{{ $comment->user && $comment->user->photo ? Storage::url($comment->user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name ?? 'U') . '&background=6366f1&color=fff&size=32' }}" 
                                         class="w-8 h-8 rounded-full object-cover">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-gray-800">{{ $comment->user->name ?? 'Utilisateur' }}</span>
                                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">{{ $comment->body }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button wire:click="editPublication({{ $viewPublication->id }})" class="px-4 py-2 text-sm font-medium text-amber-700 bg-amber-100 rounded-xl hover:bg-amber-200 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Modifier
                        </button>
                        <button wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-xl hover:bg-gray-300 transition-all">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Delete Modal -->
        @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all w-full max-w-md mx-auto overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer la publication ?</h3>
                        <p class="text-sm text-gray-500 mb-6">Cette action est irréversible. Tous les commentaires, likes et partages associés seront également supprimés.</p>
                        <div class="flex items-center justify-center gap-3">
                            <button wire:click="cancelDelete" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                                Annuler
                            </button>
                            <button wire:click="deletePublication" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="deletePublication">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="deletePublication">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endvolt
</x-layouts.admin>
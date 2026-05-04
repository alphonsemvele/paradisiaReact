<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
middleware(['auth']);
name('admin.users');
// middleware(['auth', 'admin']);

new class extends Component {
    use WithPagination, WithFileUploads;

    // Filtres et recherche
    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    public $countryFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $userId = null;

    // Formulaire
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $role = 'user';
    public $status = 'pending';
    public $photo = null;
    public $currentPhoto = null;
    public $country = '';
    public $id_country = null;
    public $ville = '';

    // Suppression
    public $userToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'countryFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCountryFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getCountriesProperty()
    {
        return Country::orderBy('name')->get();
    }

    public function getUsersProperty()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('country', 'like', '%' . $this->search . '%')
                      ->orWhere('ville', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->countryFilter, function ($query) {
                $query->where('id_country', $this->countryFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        return [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'agents' => User::where('role', 'agent')->count(),
            'users' => User::where('role', 'user')->count(),
            'active' => User::where('status', 'Success')->count(),
            'pending' => User::where('status', 'pending')->count(),
        ];
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $this->editMode = true;
        $this->userId = $id;

        $user = User::findOrFail($id);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role ?? 'user';
        $this->status = $user->status ?? 'pending';
        $this->currentPhoto = $user->photo;
        $this->country = $user->country;
        $this->id_country = $user->id_country;
        $this->ville = $user->ville;

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->role = 'user';
        $this->status = 'pending';
        $this->photo = null;
        $this->currentPhoto = null;
        $this->userId = null;
        $this->country = '';
        $this->id_country = null;
        $this->ville = '';
        $this->resetValidation();
    }

    public function updatedIdCountry($value)
    {
        if ($value) {
            $country = Country::find($value);
            if ($country) {
                $this->country = $country->name;
            }
        } else {
            $this->country = '';
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->userId,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,agent,admin',
            'status' => 'required|in:pending,Success,failed,waiting',
            'photo' => 'nullable|image|max:2048',
            'id_country' => 'nullable|exists:countries,id',
            'ville' => 'nullable|string|max:100',
        ];

        if (!$this->editMode) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
        }

        $this->validate($rules);

        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'role' => $this->role,
                'status' => $this->status,
                'country' => $this->country,
                'id_country' => $this->id_country,
                'ville' => $this->ville,
            ];

            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }

            if ($this->photo) {
                if ($this->currentPhoto) {
                    Storage::disk('public')->delete($this->currentPhoto);
                }
                $data['photo'] = $this->photo->store('users/photos', 'public');
            }

            if ($this->editMode) {
                $user = User::findOrFail($this->userId);
                $user->update($data);
                $message = 'Utilisateur modifié avec succès !';
            } else {
                User::create($data);
                $message = 'Utilisateur créé avec succès !';
            }

            $this->closeModal();
            $this->dispatch('notify', ['message' => $message, 'type' => 'success']);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Erreur: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function confirmDelete($id)
    {
        $this->userToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->userToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteUser()
    {
        try {
            $user = User::findOrFail($this->userToDelete);
            
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $user->delete();

            $this->cancelDelete();
            $this->dispatch('notify', ['message' => 'Utilisateur supprimé avec succès !', 'type' => 'success']);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Erreur: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->status = $user->status === 'Success' ? 'pending' : 'Success';
            $user->save();

            $this->dispatch('notify', [
                'message' => 'Statut mis à jour !',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Erreur: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function exportUsers()
    {
        $this->dispatch('notify', ['message' => 'Export en cours...', 'type' => 'info']);
    }
};
?>

<x-layouts.admin>
    @volt
    <div class="max-w-full overflow-hidden" x-data="{ }">
        <!-- Notifications -->
        <div x-data x-on:notify.window="
            if ($event.detail.type === 'success') {
                toastr.success($event.detail.message);
            } else if ($event.detail.type === 'error') {
                toastr.error($event.detail.message);
            } else {
                toastr.info($event.detail.message);
            }
        "></div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Gestion des Utilisateurs</h1>
                <p class="text-sm text-gray-500 mt-1">Gérez les comptes utilisateurs de la plateforme</p>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="exportUsers" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Exporter
                </button>
                <button wire:click="openCreateModal" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nouvel Utilisateur
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Total</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $this->stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Admins</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $this->stats['admins'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Agents</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $this->stats['agents'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Users</p>
                        <p class="text-2xl font-bold text-gray-600">{{ $this->stats['users'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Actifs</p>
                        <p class="text-2xl font-bold text-green-600">{{ $this->stats['active'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">En attente</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $this->stats['pending'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               placeholder="Rechercher par nom, email, téléphone, pays..." 
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                    </div>
                </div>

                <!-- Country Filter -->
                <div class="w-full lg:w-48">
                    <select wire:model.live="countryFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        <option value="">Tous les pays</option>
                        @foreach($this->countries as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Role Filter -->
                <div class="w-full lg:w-40">
                    <select wire:model.live="roleFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        <option value="">Tous les rôles</option>
                        <option value="admin">Admin</option>
                        <option value="agent">Agent</option>
                        <option value="user">Utilisateur</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-full lg:w-40">
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        <option value="">Tous les statuts</option>
                        <option value="Success">Actif</option>
                        <option value="pending">En attente</option>
                        <option value="failed">Bloqué</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div class="w-full lg:w-32">
                    <select wire:model.live="perPage" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('name')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    Utilisateur
                                    @if($sortField === 'name')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('email')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    Contact
                                    @if($sortField === 'email')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('country')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    Localisation
                                    @if($sortField === 'country')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rôle</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                    Inscrit le
                                    @if($sortField === 'created_at')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->users as $user)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->photo ? Storage::url($user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=10b981&color=fff' }}" 
                                         alt="{{ $user->name }}" 
                                         class="w-10 h-10 rounded-full object-cover flex-shrink-0 border-2 border-gray-100">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">ID: {{ $user->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-600 truncate max-w-[180px]">{{ $user->email }}</p>
                                    <p class="text-xs text-gray-400">{{ $user->phone ?? '-' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($user->country)
                                        <span class="text-lg">🌍</span>
                                        <div class="min-w-0">
                                            <p class="text-sm text-gray-600 truncate">{{ $user->country }}</p>
                                            @if($user->ville)
                                                <p class="text-xs text-gray-400 truncate">{{ $user->ville }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $roleColors = [
                                        'admin' => 'bg-purple-100 text-purple-800',
                                        'agent' => 'bg-blue-100 text-blue-800',
                                        'user' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $roleColor = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $roleColor }}">
                                    {{ ucfirst($user->role ?? 'user') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="toggleStatus({{ $user->id }})" class="focus:outline-none">
                                    @if($user->status === 'Success')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                            Actif
                                        </span>
                                    @elseif($user->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></span>
                                            En attente
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span>
                                            Bloqué
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-600">{{ $user->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $user->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openEditModal({{ $user->id }})" 
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all" 
                                            title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $user->id }})" 
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all" 
                                            title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Aucun utilisateur trouvé</p>
                                    <p class="text-gray-400 text-sm mt-1">Essayez de modifier vos filtres</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($this->users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $this->users->links() }}
            </div>
            @endif
        </div>

        <!-- Create/Edit Modal -->
        @if($showModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="closeModal">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $editMode ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
                        </h3>
                        <button wire:click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition-all">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <!-- Photo -->
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            @if($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="w-20 h-20 rounded-full object-cover border-4 border-green-100">
                            @elseif($currentPhoto)
                                <img src="{{ Storage::url($currentPhoto) }}" class="w-20 h-20 rounded-full object-cover border-4 border-green-100">
                            @else
                                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border-4 border-gray-50">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="cursor-pointer">
                                <span class="px-4 py-2 text-sm font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-all inline-block">
                                    Changer la photo
                                </span>
                                <input type="file" wire:model="photo" accept="image/*" class="hidden">
                            </label>
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG. Max 2MB</p>
                            @error('photo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                        <input type="text" wire:model="name" 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="John Doe">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" wire:model="email" 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="john@example.com">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input type="text" wire:model="phone" 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="+237 6XX XXX XXX">
                        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Country & City -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                            <select wire:model.live="id_country" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Sélectionner un pays</option>
                                @foreach($this->countries as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('id_country') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input type="text" wire:model="ville" 
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="Douala">
                            @error('ville') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe {{ $editMode ? '(laisser vide pour ne pas changer)' : '*' }}
                        </label>
                        <input type="password" wire:model="password" 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="••••••••">
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Role & Status -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rôle *</label>
                            <select wire:model="role" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="user">Utilisateur</option>
                                <option value="agent">Agent</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                            <select wire:model="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="pending">En attente</option>
                                <option value="Success">Actif</option>
                                <option value="failed">Bloqué</option>
                            </select>
                            @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                            Annuler
                        </button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="save">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="save">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ $editMode ? 'Modifier' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Delete Confirmation Modal -->
        @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="cancelDelete">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Confirmer la suppression</h3>
                    <p class="text-gray-600 mb-6">Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
                    <div class="flex items-center justify-center gap-3">
                        <button wire:click="cancelDelete" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                            Annuler
                        </button>
                        <button wire:click="deleteUser" class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="deleteUser">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="deleteUser">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    @endpush

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 3000
        };
    </script>
    @endpush
    @endvolt
</x-layouts.admin>
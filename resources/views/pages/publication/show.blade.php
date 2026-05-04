<?php

use function Laravel\Folio\{name};
use Livewire\Volt\Component;
use App\Models\Publication;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Share;
use App\Models\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

name('publication.show');
$id = Route::current()->parameter('id');

new class extends Component {
    public Publication $publication;
    
    // Commentaires
    public $commentText = '';
    public $replyText = [];
    public $showReplies = [];
    public $editingCommentId = null;
    public $editCommentText = '';
    
    // Partage
    public $showShareModal = false;

    public function mount($id)
    {

        dd($id);

        $this->publication = Publication::with([
            'user', 
            'comments' => function($q) {
                $q->whereNull('parent_id')->where('status', 'Success')->orderBy('created_at', 'desc');
            },
            'comments.user',
            'comments.replies' => function($q) {
                $q->where('status', 'Success')->orderBy('created_at', 'asc');
            },
            'comments.replies.user',
            'likes.user',
            'shares',
            'views'
        ])->findOrFail($id);

        // Enregistrer la vue
        $this->recordView();
    }

    private function recordView()
    {
        $ipAddress = request()->ip();
        
        // Éviter les doublons de vue par IP dans les dernières 24h
        $existingView = View::where('id_publication', $this->publication->id)
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if (!$existingView) {
            View::create([
                'id_publication' => $this->publication->id,
                'id_user' => Auth::id(),
                'ip_address' => $ipAddress,
                'status' => 'Success'
            ]);

            // Incrémenter le compteur
            $this->publication->increment('nbr_vews');
        }
    }

    // ==================== LIKES ====================
    
    public function toggleLike()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $existingLike = Like::where('id_user', Auth::id())
            ->where('id_publication', $this->publication->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $this->dispatch('notify', ['message' => 'Like retiré', 'type' => 'info']);
        } else {
            Like::create([
                'id_user' => Auth::id(),
                'id_publication' => $this->publication->id,
                'ip_address' => request()->ip(),
                'status' => 'Success'
            ]);
            $this->dispatch('notify', ['message' => 'Publication aimée ! ❤️', 'type' => 'success']);
        }

        $this->publication->refresh();
    }

    public function hasLiked()
    {
        if (!Auth::check()) return false;
        return $this->publication->likes->where('id_user', Auth::id())->count() > 0;
    }

    // ==================== COMMENTAIRES ====================

    public function addComment()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (empty(trim($this->commentText))) {
            $this->dispatch('notify', ['message' => 'Le commentaire ne peut pas être vide', 'type' => 'error']);
            return;
        }

        Comment::create([
            'id_user' => Auth::id(),
            'id_publication' => $this->publication->id,
            'body' => trim($this->commentText),
            'status' => 'Success'
        ]);

        $this->commentText = '';
        $this->publication->refresh();
        $this->dispatch('notify', ['message' => 'Commentaire ajouté ! 💬', 'type' => 'success']);
    }

    public function toggleReplies($commentId)
    {
        $this->showReplies[$commentId] = !($this->showReplies[$commentId] ?? false);
    }

    public function addReply($commentId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $text = $this->replyText[$commentId] ?? '';
        
        if (empty(trim($text))) {
            $this->dispatch('notify', ['message' => 'La réponse ne peut pas être vide', 'type' => 'error']);
            return;
        }

        Comment::create([
            'id_user' => Auth::id(),
            'id_publication' => $this->publication->id,
            'body' => trim($text),
            'status' => 'Success',
            'parent_id' => $commentId
        ]);

        $this->replyText[$commentId] = '';
        $this->showReplies[$commentId] = true;
        $this->publication->refresh();
        $this->dispatch('notify', ['message' => 'Réponse ajoutée ! 💬', 'type' => 'success']);
    }

    public function startEditComment($commentId, $currentText)
    {
        $this->editingCommentId = $commentId;
        $this->editCommentText = $currentText;
    }

    public function cancelEditComment()
    {
        $this->editingCommentId = null;
        $this->editCommentText = '';
    }

    public function updateComment($commentId)
    {
        if (!Auth::check()) return;

        $comment = Comment::find($commentId);
        
        if (!$comment || $comment->id_user !== Auth::id()) {
            $this->dispatch('notify', ['message' => 'Action non autorisée', 'type' => 'error']);
            return;
        }

        if (empty(trim($this->editCommentText))) {
            $this->dispatch('notify', ['message' => 'Le commentaire ne peut pas être vide', 'type' => 'error']);
            return;
        }

        $comment->update(['body' => trim($this->editCommentText)]);
        
        $this->editingCommentId = null;
        $this->editCommentText = '';
        $this->publication->refresh();
        $this->dispatch('notify', ['message' => 'Commentaire modifié ! ✏️', 'type' => 'success']);
    }

    public function deleteComment($commentId)
    {
        if (!Auth::check()) return;

        $comment = Comment::find($commentId);
        
        if (!$comment || $comment->id_user !== Auth::id()) {
            $this->dispatch('notify', ['message' => 'Action non autorisée', 'type' => 'error']);
            return;
        }

        Comment::where('parent_id', $commentId)->delete();
        $comment->delete();
        
        $this->publication->refresh();
        $this->dispatch('notify', ['message' => 'Commentaire supprimé ! 🗑️', 'type' => 'success']);
    }

    // ==================== PARTAGE ====================

    public function openShareModal()
    {
        $this->showShareModal = true;
    }

    public function closeShareModal()
    {
        $this->showShareModal = false;
    }

    public function shareToFacebook()
    {
        $this->recordShare();
        $url = route('publication.show', $this->publication->id);
        $this->dispatch('openWindow', ['url' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url)]);
        $this->closeShareModal();
    }

    public function shareToWhatsApp()
    {
        $this->recordShare();
        $url = route('publication.show', $this->publication->id);
        $text = Str::limit($this->publication->text, 100) . "\n\n" . $url;
        $this->dispatch('openWindow', ['url' => "https://wa.me/?text=" . urlencode($text)]);
        $this->closeShareModal();
    }

    public function shareToTwitter()
    {
        $this->recordShare();
        $url = route('publication.show', $this->publication->id);
        $text = Str::limit($this->publication->text, 200);
        $this->dispatch('openWindow', ['url' => "https://twitter.com/intent/tweet?text=" . urlencode($text) . "&url=" . urlencode($url)]);
        $this->closeShareModal();
    }

    public function shareToTelegram()
    {
        $this->recordShare();
        $url = route('publication.show', $this->publication->id);
        $this->dispatch('openWindow', ['url' => "https://t.me/share/url?url=" . urlencode($url)]);
        $this->closeShareModal();
    }

    public function copyLink()
    {
        $this->recordShare();
        $this->dispatch('copyToClipboard', ['url' => route('publication.show', $this->publication->id)]);
        $this->dispatch('notify', ['message' => 'Lien copié ! 📋', 'type' => 'success']);
        $this->closeShareModal();
    }

    private function recordShare()
    {
        if (Auth::check()) {
            Share::create([
                'id_user' => Auth::id(),
                'id_publication' => $this->publication->id,
                'status' => 'Success',
                'ip_address' => request()->ip()
            ]);
            $this->publication->refresh();
        }
    }

    #[\Livewire\Attributes\Computed]
    public function relatedPublications()
    {
        return Publication::with(['user'])
            ->where('status', 'Success')
            ->where('id', '!=', $this->publication->id)
            ->when($this->publication->id_user, function($q) {
                $q->where('id_user', $this->publication->id_user);
            })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    }
}; ?>

<x-layouts.app>
    @volt

    <div x-data="{
        showLightbox: false,
        currentIndex: 0,
        images: [],
        openLightbox(index, imgs) {
            this.images = imgs;
            this.currentIndex = index;
            this.showLightbox = true;
        }
    }"
    x-on:open-window.window="window.open($event.detail.url, '_blank', 'width=600,height=400')"
    x-on:copy-to-clipboard.window="navigator.clipboard.writeText($event.detail.url)"
    class="min-h-screen bg-gradient-to-br from-green-50 via-white to-teal-50">
        
        <!-- Notifications -->
        <div x-data x-on:notify.window="
            if ($event.detail.type === 'success') toastr.success($event.detail.message);
            else if ($event.detail.type === 'error') toastr.error($event.detail.message);
            else toastr.info($event.detail.message);
        "></div>

        <div class="container mx-auto px-4 py-8" style="max-width: 1200px;">
            
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center gap-2 text-sm">
                    <li><a href="{{ route('accueil') }}" class="text-green-600 hover:underline">Accueil</a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-600">Publication</li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Contenu Principal -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Publication Card -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                        
                        <!-- Header -->
                        <div class="p-6 pb-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    @php
                                        $user = $publication->user;
                                        $userName = $user ? $user->name : 'Utilisateur supprimé';
                                        $userPhoto = ($user && $user->photo) ? Storage::url($user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=10b981&color=fff&size=128';
                                    @endphp
                                    <a href="#" class="flex-shrink-0">
                                        <img src="{{ $userPhoto }}" 
                                             alt="{{ $userName }}" 
                                             class="w-14 h-14 rounded-full border-3 border-green-400 object-cover hover:scale-105 transition-transform">
                                    </a>
                                    <div>
                                        <a href="#" class="font-bold text-gray-900 text-lg hover:underline">{{ $userName }}</a>
                                        @if($publication->page)
                                            <span class="text-gray-500">▸</span>
                                            <a href="#" class="font-semibold text-green-600 hover:underline">{{ $publication->page->name }}</a>
                                        @endif
                                        <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                                            <span>{{ $publication->created_at->diffForHumans() }}</span>
                                            <span>•</span>
                                            <span title="Public">🌍</span>
                                            @if($publication->country)
                                                <span>•</span>
                                                <span>{{ $publication->country->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="p-2 hover:bg-gray-100 rounded-full transition-all">
                                        <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak
                                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-20">
                                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 text-gray-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                            </svg>
                                            Enregistrer
                                        </a>
                                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 text-gray-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                            </svg>
                                            Masquer
                                        </a>
                                        <hr class="my-2 border-gray-100">
                                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 text-red-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            Signaler
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Titre -->
                        @if($publication->title)
                        <div class="px-6 pb-2">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $publication->title }}</h1>
                        </div>
                        @endif
                        
                        <!-- Texte -->
                        <div class="px-6 pb-4">
                            <p class="text-gray-800 text-lg leading-relaxed whitespace-pre-wrap">{{ $publication->text }}</p>
                        </div>

                        <!-- Médias -->
                        @php
                            $images = collect([
                                $publication->img_1,
                                $publication->img_2,
                                $publication->img_3,
                                $publication->img_4,
                                $publication->img_5,
                            ])->filter()->values();
                            $imageCount = $images->count();
                            $imagesJson = $images->map(fn($img) => Storage::url($img))->toJson();
                        @endphp

                        @if($imageCount > 0)
                        <div class="relative">
                            @if($imageCount === 1)
                            <div class="cursor-pointer" @click="openLightbox(0, {{ $imagesJson }})">
                                <img src="{{ Storage::url($images[0]) }}" alt="Publication" class="w-full max-h-[600px] object-cover hover:opacity-95 transition-opacity">
                            </div>
                            
                            @elseif($imageCount === 2)
                            <div class="grid grid-cols-2 gap-1">
                                @foreach($images as $index => $img)
                                <div class="cursor-pointer aspect-[4/3] overflow-hidden" @click="openLightbox({{ $index }}, {{ $imagesJson }})">
                                    <img src="{{ Storage::url($img) }}" alt="Publication" class="w-full h-full object-cover hover:opacity-95 transition-opacity">
                                </div>
                                @endforeach
                            </div>
                            
                            @elseif($imageCount === 3)
                            <div class="grid grid-cols-2 gap-1" style="height: 500px;">
                                <div class="row-span-2 cursor-pointer overflow-hidden" @click="openLightbox(0, {{ $imagesJson }})">
                                    <img src="{{ Storage::url($images[0]) }}" alt="Publication" class="w-full h-full object-cover hover:opacity-95 transition-opacity">
                                </div>
                                <div class="cursor-pointer overflow-hidden" @click="openLightbox(1, {{ $imagesJson }})">
                                    <img src="{{ Storage::url($images[1]) }}" alt="Publication" class="w-full h-full object-cover hover:opacity-95 transition-opacity">
                                </div>
                                <div class="cursor-pointer overflow-hidden" @click="openLightbox(2, {{ $imagesJson }})">
                                    <img src="{{ Storage::url($images[2]) }}" alt="Publication" class="w-full h-full object-cover hover:opacity-95 transition-opacity">
                                </div>
                            </div>
                            
                            @elseif($imageCount === 4)
                            <div class="grid grid-cols-2 gap-1">
                                @foreach($images as $index => $img)
                                <div class="cursor-pointer aspect-square overflow-hidden" @click="openLightbox({{ $index }}, {{ $imagesJson }})">
                                    <img src="{{ Storage::url($img) }}" alt="Publication" class="w-full h-full object-cover hover:opacity-95 transition-opacity">
                                </div>
                                @endforeach
                            </div>
                            
                            @else
                            <div class="grid grid-cols-2 gap-1">
                                <div class="col-span-2 cursor-pointer" @click="openLightbox(0, {{ $imagesJson }})">
                                    <img src="{{ Storage::url($images[0]) }}" alt="Publication" class="w-full h-72 object-cover hover:opacity-95 transition-opacity">
                                </div>
                                @for($i = 1; $i < 4; $i++)
                                <div class="cursor-pointer aspect-square overflow-hidden relative" @click="openLightbox({{ $i }}, {{ $imagesJson }})">
                                    <img src="{{ Storage::url($images[$i]) }}" alt="Publication" class="w-full h-full object-cover hover:opacity-95 transition-opacity">
                                    @if($i === 3 && $imageCount > 4)
                                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                                        <span class="text-white text-4xl font-bold">+{{ $imageCount - 4 }}</span>
                                    </div>
                                    @endif
                                </div>
                                @endfor
                            </div>
                            @endif
                        </div>
                        @endif

                        @if($publication->video)
                        <div class="bg-black">
                            <video class="w-full max-h-[600px]" controls preload="metadata">
                                <source src="{{ Storage::url($publication->video) }}" type="video/mp4">
                            </video>
                        </div>
                        @endif

                        @if($publication->audio)
                        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">🎵 Fichier audio</p>
                                    <audio controls class="w-full">
                                        <source src="{{ Storage::url($publication->audio) }}" type="audio/mpeg">
                                    </audio>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Stats -->
                        <div class="px-6 py-3 flex items-center justify-between text-sm text-gray-500 border-t border-gray-100">
                            <div class="flex items-center gap-1">
                                @if($publication->likes->count() > 0)
                                <div class="flex items-center">
                                    <span class="bg-blue-500 text-white rounded-full p-1.5 -mr-1 z-10">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                        </svg>
                                    </span>
                                    <span class="bg-red-500 text-white rounded-full p-1.5">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                    <span class="ml-2 hover:underline cursor-pointer font-medium">
                                        {{ $publication->likes->count() }} personne{{ $publication->likes->count() > 1 ? 's' : '' }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                <span>{{ $publication->comments->count() }} commentaire{{ $publication->comments->count() > 1 ? 's' : '' }}</span>
                                <span>{{ $publication->shares->count() }} partage{{ $publication->shares->count() > 1 ? 's' : '' }}</span>
                                <span>{{ number_format($publication->nbr_vews ?? 0) }} vue{{ ($publication->nbr_vews ?? 0) > 1 ? 's' : '' }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="px-4 py-2 flex items-center justify-around border-t border-gray-100">
                            <button wire:click="toggleLike" 
                                    class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg hover:bg-gray-100 transition-all {{ $this->hasLiked() ? 'text-blue-600' : 'text-gray-600' }}">
                                @if($this->hasLiked())
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                </svg>
                                @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                </svg>
                                @endif
                                <span class="font-semibold">J'aime</span>
                            </button>

                            <button onclick="document.getElementById('comment-input').focus()" 
                                    class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg hover:bg-gray-100 text-gray-600 transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span class="font-semibold">Commenter</span>
                            </button>

                            <button wire:click="openShareModal" 
                                    class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg hover:bg-gray-100 text-gray-600 transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                <span class="font-semibold">Partager</span>
                            </button>
                        </div>

                        <!-- Section Commentaires -->
                        <div class="border-t border-gray-100">
                            <!-- Ajouter un commentaire -->
                            <div class="p-4 flex items-start gap-3">
                                @auth
                                <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=10b981&color=fff&size=64' }}" 
                                     class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                @endauth
                                
                                <div class="flex-1">
                                    <div class="bg-gray-100 rounded-2xl flex items-center pr-3">
                                        <input type="text" 
                                               id="comment-input"
                                               wire:model="commentText"
                                               wire:keydown.enter="addComment"
                                               placeholder="{{ Auth::check() ? 'Écrire un commentaire...' : 'Connectez-vous pour commenter' }}"
                                               {{ Auth::check() ? '' : 'disabled' }}
                                               class="flex-1 bg-transparent px-4 py-3 focus:outline-none rounded-2xl">
                                        @auth
                                        <button wire:click="addComment" class="p-2 text-green-600 hover:bg-green-100 rounded-full transition-all">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                                            </svg>
                                        </button>
                                        @endauth
                                    </div>
                                </div>
                            </div>

                            <!-- Liste des commentaires -->
                            <div class="px-4 pb-4 space-y-4">
                                @forelse($publication->comments->whereNull('parent_id') as $comment)
                                <div class="flex items-start gap-3" wire:key="comment-{{ $comment->id }}">
                                    @php
                                        $commentUser = $comment->user;
                                        $commentUserName = $commentUser ? $commentUser->name : 'Utilisateur';
                                        $commentUserPhoto = ($commentUser && $commentUser->photo) ? Storage::url($commentUser->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($commentUserName) . '&background=6b7280&color=fff&size=64';
                                    @endphp
                                    <img src="{{ $commentUserPhoto }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                    
                                    <div class="flex-1">
                                        @if($editingCommentId === $comment->id)
                                        <div class="bg-white rounded-2xl border-2 border-blue-400 p-3">
                                            <input type="text" 
                                                   wire:model="editCommentText"
                                                   wire:keydown.enter="updateComment({{ $comment->id }})"
                                                   wire:keydown.escape="cancelEditComment"
                                                   class="w-full bg-transparent focus:outline-none">
                                            <div class="flex justify-end gap-2 mt-2 text-sm">
                                                <button wire:click="cancelEditComment" class="text-gray-500 hover:underline">Annuler</button>
                                                <button wire:click="updateComment({{ $comment->id }})" class="text-blue-600 font-semibold hover:underline">Enregistrer</button>
                                            </div>
                                        </div>
                                        @else
                                        <div class="bg-gray-100 rounded-2xl px-4 py-2.5 inline-block max-w-full">
                                            <p class="font-semibold text-sm text-gray-900">{{ $commentUserName }}</p>
                                            <p class="text-gray-800">{{ $comment->body }}</p>
                                        </div>
                                        
                                        <div class="flex items-center gap-4 mt-1 ml-2 text-sm">
                                            <span class="text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            <button class="font-semibold text-gray-600 hover:underline">J'aime</button>
                                            <button wire:click="toggleReplies({{ $comment->id }})" class="font-semibold text-gray-600 hover:underline">Répondre</button>
                                            @auth
                                            @if(Auth::id() === $comment->id_user)
                                            <button wire:click="startEditComment({{ $comment->id }}, '{{ addslashes($comment->body) }}')" class="font-semibold text-gray-600 hover:underline">Modifier</button>
                                            <button wire:click="deleteComment({{ $comment->id }})" onclick="return confirm('Supprimer ?')" class="font-semibold text-red-500 hover:underline">Supprimer</button>
                                            @endif
                                            @endauth
                                        </div>
                                        @endif

                                        <!-- Réponses -->
                                        @php $replies = $comment->replies ?? collect(); @endphp
                                        @if($replies->count() > 0)
                                        <div class="mt-3 space-y-3">
                                            @if(!($showReplies[$comment->id] ?? false) && $replies->count() > 1)
                                            <button wire:click="toggleReplies({{ $comment->id }})" class="text-sm font-semibold text-gray-600 hover:underline flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                </svg>
                                                Voir {{ $replies->count() }} réponse{{ $replies->count() > 1 ? 's' : '' }}
                                            </button>
                                            @endif
                                            
                                            @foreach(($showReplies[$comment->id] ?? false) ? $replies : $replies->take(1) as $reply)
                                            <div class="flex items-start gap-2 ml-4" wire:key="reply-{{ $reply->id }}">
                                                @php
                                                    $replyUser = $reply->user;
                                                    $replyUserName = $replyUser ? $replyUser->name : 'Utilisateur';
                                                    $replyUserPhoto = ($replyUser && $replyUser->photo) ? Storage::url($replyUser->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($replyUserName) . '&background=9ca3af&color=fff&size=64';
                                                @endphp
                                                <img src="{{ $replyUserPhoto }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                                <div class="flex-1">
                                                    <div class="bg-gray-100 rounded-2xl px-3 py-2 inline-block">
                                                        <p class="font-semibold text-xs text-gray-900">{{ $replyUserName }}</p>
                                                        <p class="text-sm text-gray-800">{{ $reply->body }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-3 mt-1 ml-2 text-xs">
                                                        <span class="text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                                        <button class="font-semibold text-gray-600 hover:underline">J'aime</button>
                                                        @auth
                                                        @if(Auth::id() === $reply->id_user)
                                                        <button wire:click="deleteComment({{ $reply->id }})" onclick="return confirm('Supprimer ?')" class="font-semibold text-red-500 hover:underline">Supprimer</button>
                                                        @endif
                                                        @endauth
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif

                                        <!-- Input réponse -->
                                        @if($showReplies[$comment->id] ?? false)
                                        @auth
                                        <div class="mt-3 ml-4 flex items-start gap-2">
                                            <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=10b981&color=fff&size=64' }}" 
                                                 class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                            <div class="flex-1 bg-gray-100 rounded-2xl flex items-center pr-2">
                                                <input type="text" 
                                                       wire:model="replyText.{{ $comment->id }}"
                                                       wire:keydown.enter="addReply({{ $comment->id }})"
                                                       placeholder="Répondre..."
                                                       class="flex-1 bg-transparent px-3 py-2 text-sm focus:outline-none rounded-2xl">
                                                <button wire:click="addReply({{ $comment->id }})" class="p-1.5 text-green-600 hover:bg-green-100 rounded-full">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @endauth
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <p class="text-center text-gray-500 py-6">Soyez le premier à commenter ! 💬</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Auteur -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4">À propos de l'auteur</h3>
                        <div class="flex items-center gap-4">
                            <img src="{{ $userPhoto }}" class="w-16 h-16 rounded-full object-cover border-2 border-green-400">
                            <div>
                                <p class="font-bold text-gray-900">{{ $userName }}</p>
                                <p class="text-sm text-gray-500">Membre PARADISIA</p>
                            </div>
                        </div>
                        <button class="w-full mt-4 bg-green-500 hover:bg-green-600 text-white font-semibold py-2.5 rounded-xl transition-all">
                            Suivre
                        </button>
                    </div>

                    <!-- Publications similaires -->
                    @if($this->relatedPublications->count() > 0)
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4">Autres publications</h3>
                        <div class="space-y-4">
                            @foreach($this->relatedPublications as $related)
                            <a href="{{ route('publication.show', $related->id) }}" class="block group">
                                <div class="flex gap-3">
                                    @if($related->img_1)
                                    <img src="{{ Storage::url($related->img_1) }}" class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
                                    @else
                                    <div class="w-20 h-20 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-3xl">📝</span>
                                    </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-800 line-clamp-2 group-hover:text-green-600 transition-colors">{{ Str::limit($related->text, 80) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $related->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Retour -->
                    <a href="{{ route('accueil') }}" class="flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <div x-show="showLightbox" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center"
             @keydown.escape.window="showLightbox = false"
             @keydown.arrow-left.window="currentIndex = (currentIndex - 1 + images.length) % images.length"
             @keydown.arrow-right.window="currentIndex = (currentIndex + 1) % images.length">
            
            <button @click="showLightbox = false" class="absolute top-4 right-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <button x-show="images.length > 1" @click="currentIndex = (currentIndex - 1 + images.length) % images.length" 
                    class="absolute left-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <button x-show="images.length > 1" @click="currentIndex = (currentIndex + 1) % images.length" 
                    class="absolute right-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            
            <div class="max-w-6xl max-h-[90vh] px-16">
                <template x-for="(img, index) in images" :key="index">
                    <img x-show="currentIndex === index"
                         x-transition
                         :src="img" 
                         class="max-w-full max-h-[85vh] object-contain mx-auto rounded-lg shadow-2xl">
                </template>
            </div>
            
            <div x-show="images.length > 1" class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex items-center gap-2">
                <template x-for="(img, index) in images" :key="'dot-'+index">
                    <button @click="currentIndex = index" 
                            class="w-2.5 h-2.5 rounded-full transition-all"
                            :class="currentIndex === index ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/80'">
                    </button>
                </template>
            </div>
            
            <div x-show="images.length > 1" class="absolute top-4 left-4 bg-black/50 text-white px-4 py-2 rounded-full text-sm font-medium">
                <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
            </div>
        </div>

        <!-- Modal Partage -->
        @if($showShareModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:click.self="closeShareModal">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b">
                    <h3 class="text-xl font-bold text-gray-800">Partager</h3>
                    <button wire:click="closeShareModal" class="p-2 hover:bg-gray-100 rounded-full">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-4 gap-4 mb-6">
                        <button wire:click="shareToFacebook" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-blue-50 transition-all">
                            <div class="w-14 h-14 bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </div>
                            <span class="text-xs font-medium">Facebook</span>
                        </button>
                        <button wire:click="shareToWhatsApp" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-green-50 transition-all">
                            <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </div>
                            <span class="text-xs font-medium">WhatsApp</span>
                        </button>
                        <button wire:click="shareToTwitter" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-gray-100 transition-all">
                            <div class="w-14 h-14 bg-black rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </div>
                            <span class="text-xs font-medium">X</span>
                        </button>
                        <button wire:click="shareToTelegram" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-blue-50 transition-all">
                            <div class="w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            </div>
                            <span class="text-xs font-medium">Telegram</span>
                        </button>
                    </div>
                    <button wire:click="copyLink" class="w-full flex items-center justify-center gap-3 py-3 px-4 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                        <span class="font-semibold text-gray-700">Copier le lien</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <style>[x-cloak] { display: none !important; }</style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3000 };</script>
    </div>
    @endvolt
</x-layouts.app>
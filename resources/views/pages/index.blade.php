<?php
use function Laravel\Folio\{name};
use Livewire\Volt\Component;
use App\Models\Publication;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Share;
use App\Models\User;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\Product;
use App\Models\Category;

name('accueil');

new class extends Component {
    use WithFileUploads;

    public $postContent = '';
    public $postImage = null;
    public $postVideo = null;
    public $postVisibility = 'public';
    public $showCreatePostModal = false;
    
    // Commentaires
    public $commentText = [];
    public $replyText = [];
    public $showComments = [];
    public $showReplies = [];
    public $editingCommentId = null;
    public $editCommentText = '';
    
    // Partage
    public $showShareModal = false;
    public $sharePublicationId = null;
    public $sharePublicationData = null;

    public function mount()
    {
        // Initialisation
    }

    public function getPublicationsProperty()
{
    $highlightId = request()->query('highlight');

    $query = Publication::with(['user','comments.user', 'comments.replies.user', 'likes'])
        ->where('status', 'Success');

    $publications = $query->orderBy('created_at', 'desc')->limit(20)->get(); // on prend un peu plus pour avoir du choix

    if ($highlightId && $highlight = $publications->firstWhere('id', $highlightId)) {
        // On retire la publication mise en avant de la liste normale
        $publications = $publications->where('id', '!=', $highlightId);

        // On la remet en premier
        $publications = collect([$highlight])->merge($publications);
    }

    return $publications->take(10); // on garde seulement 10 au final
}
   #[\Livewire\Attributes\Computed]
    public function products()
    {
        return Product::with(['user', 'categories'])
            ->where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function featuredProducts()
    {
        return Product::with(['user', 'categories'])
            ->where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    }
    // ==================== LIKES ====================
    
    public function toggleLike($publicationId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $existingLike = Like::where('id_user', Auth::id())
            ->where('id_publication', $publicationId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $this->dispatch('notify', ['message' => 'Like retiré', 'type' => 'info']);
        } else {
            Like::create([
                'id_user' => Auth::id(),
                'id_publication' => $publicationId,
                'ip_address' => request()->ip(),
                'status' => 'Success'
            ]);
            $this->dispatch('notify', ['message' => 'Publication aimée ! ❤️', 'type' => 'success']);
        }
    }

    public function hasLiked($publicationId)
    {
        if (!Auth::check()) return false;
        
        return Like::where('id_user', Auth::id())
            ->where('id_publication', $publicationId)
            ->exists();
    }

    public function getLikesCount($publicationId)
    {
        return Like::where('id_publication', $publicationId)->count();
    }

    public function getLikedUsers($publicationId)
    {
        return Like::where('id_publication', $publicationId)
            ->with('user')
            ->limit(3)
            ->get()
            ->pluck('user')
            ->filter();
    }

    // ==================== COMMENTAIRES ====================

    public function toggleComments($publicationId)
    {
        $this->showComments[$publicationId] = !($this->showComments[$publicationId] ?? false);
    }

    public function addComment($publicationId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $text = $this->commentText[$publicationId] ?? '';
        
        if (empty(trim($text))) {
            $this->dispatch('notify', ['message' => 'Le commentaire ne peut pas être vide', 'type' => 'error']);
            return;
        }

        Comment::create([
            'id_user' => Auth::id(),
            'id_publication' => $publicationId,
            'body' => trim($text),
            'status' => 'Success'
        ]);

        $this->commentText[$publicationId] = '';
        $this->showComments[$publicationId] = true;
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

        $parentComment = Comment::find($commentId);
        
        if (!$parentComment) {
            $this->dispatch('notify', ['message' => 'Commentaire introuvable', 'type' => 'error']);
            return;
        }

        Comment::create([
            'id_user' => Auth::id(),
            'id_publication' => $parentComment->id_publication,
            'id_page' => null,
            'body' => trim($text),
            'status' => 'Success',
            'parent_id' => $commentId
        ]);

        $this->replyText[$commentId] = '';
        $this->showReplies[$commentId] = true;
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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

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
        $this->dispatch('notify', ['message' => 'Commentaire modifié ! ✏️', 'type' => 'success']);
    }

    public function deleteComment($commentId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $comment = Comment::find($commentId);
        
        if (!$comment || $comment->id_user !== Auth::id()) {
            $this->dispatch('notify', ['message' => 'Action non autorisée', 'type' => 'error']);
            return;
        }

        // Supprimer les réponses d'abord
        Comment::where('parent_id', $commentId)->delete();
        $comment->delete();
        
        $this->dispatch('notify', ['message' => 'Commentaire supprimé ! 🗑️', 'type' => 'success']);
    }

    public function likeComment($commentId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Logique simplifiée - vous pouvez créer une table comment_likes si nécessaire
        $this->dispatch('notify', ['message' => 'Commentaire aimé ! ❤️', 'type' => 'success']);
    }

    public function getCommentsCount($publicationId)
    {
        return Comment::where('id_publication', $publicationId)
            ->where('status', 'Success')
            ->count();
    }

    // ==================== PARTAGE ====================

    public function openShareModal($publicationId)
    {
        $this->sharePublicationId = $publicationId;
        $publication = Publication::with('user')->find($publicationId);
        $this->sharePublicationData = $publication;
        $this->showShareModal = true;
    }

    public function closeShareModal()
    {
        $this->showShareModal = false;
        $this->sharePublicationId = null;
        $this->sharePublicationData = null;
    }

    public function shareToFacebook()
    {
        if (!$this->sharePublicationData) return;

       $url = route('accueil') . '?highlight=' . $this->sharePublicationId;
$text = $this->sharePublicationData->text ?? 'Découvrez cette publication sur PARADISIA!';
$this->dispatch('openShareWindow', [
    'url' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url) . "&quote=" . urlencode($text)
]);
        
        // Enregistrer le partage
        $this->recordShare('facebook');
        
        $this->dispatch('openShareWindow', [
            'url' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url) . "&quote=" . urlencode($text)
        ]);
        
        $this->closeShareModal();
    }

    public function shareToWhatsApp()
    {
        if (!$this->sharePublicationData) return;

        $url = route('accueil') . '?highlight=' . $this->sharePublicationId;
        $text = $this->sharePublicationData->text ?? 'Découvrez cette publication sur PARADISIA!';
        $message = $text . "\n\n" . $url;
$this->dispatch('openShareWindow', [
    'url' => "https://wa.me/?text=" . urlencode($message)
]);
        
        // Enregistrer le partage
        $this->recordShare('whatsapp');
        
        $this->dispatch('openShareWindow', [
            'url' => "https://wa.me/?text=" . urlencode($message)
        ]);
        
        $this->closeShareModal();
    }

    public function shareToTwitter()
    {
        if (!$this->sharePublicationData) return;

         $url = route('accueil') . '?highlight=' . $this->sharePublicationId;
        $text = \Illuminate\Support\Str::limit($this->sharePublicationData->text ?? 'Découvrez PARADISIA!', 200);
        
        $this->recordShare('twitter');
        
      $this->dispatch('openShareWindow', [
    'url' => "https://twitter.com/intent/tweet?text=" . urlencode($text) . "&url=" . urlencode($url)
]);
        
        $this->closeShareModal();
    }

    public function shareToTelegram()
    {
        if (!$this->sharePublicationData) return;

          $url = route('accueil') . '?highlight=' . $this->sharePublicationId;
        $text = $this->sharePublicationData->text ?? 'Découvrez cette publication sur PARADISIA!';
        
        $this->recordShare('telegram');
        
       $this->dispatch('openShareWindow', [
    'url' => "https://t.me/share/url?url=" . urlencode($url) . "&text=" . urlencode($text)
]);
        
        $this->closeShareModal();
    }

    public function copyLink()
    {
        if (!$this->sharePublicationId) return;
        
        $url = route('publication.show', $this->sharePublicationId);
        
        $this->recordShare('copy_link');
        
        $this->dispatch('copyToClipboard', ['url' => $url]);
        $this->dispatch('notify', ['message' => 'Lien copié ! 📋', 'type' => 'success']);
        $this->closeShareModal();
    }

    private function recordShare($platform)
    {
        if (Auth::check()) {
            Share::create([
                'id_user' => Auth::id(),
                'id_publication' => $this->sharePublicationId,
                'status' => 'Success',
                'ip_address' => request()->ip()
            ]);
        }
    }

    public function getSharesCount($publicationId)
    {
        return Share::where('id_publication', $publicationId)->count();
    }

    // ==================== PUBLICATIONS ====================

    public function checkAuthAndOpenModal($type = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $this->showCreatePostModal = true;
    }

    public function closeCreatePostModal()
    {
        $this->showCreatePostModal = false;
        $this->resetPostForm();
    }

    public function resetPostForm()
    {
        $this->postContent = '';
        $this->postImage = null;
        $this->postVideo = null;
        $this->postVisibility = 'public';
        $this->resetValidation();
    }

    public function publishPost()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate([
            'postContent' => 'required|string|max:5000',
            'postImage' => 'nullable|image|max:5120',
            'postVideo' => 'nullable|mimes:mp4,mov,avi|max:51200',
        ]);

        try {
            $data = [
                'ref' => 'PUB_' . \Illuminate\Support\Str::random(10),
                'text' => $this->postContent,
                'id_user' => Auth::id(),
                'status' => 'Success',
                'type' => 'publication',
            ];

            if ($this->postImage) {
                $data['image'] = $this->postImage->store('publications/images', 'public');
            }

            if ($this->postVideo) {
                $data['video'] = $this->postVideo->store('publications/videos', 'public');
            }

            Publication::create($data);

            $this->dispatch('notify', ['message' => 'Publication créée avec succès ! 🎉', 'type' => 'success']);
            $this->closeCreatePostModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Erreur: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function redirectToInvest()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        return redirect()->route('invest');
    }

    public function openEventModal()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $this->dispatch('openEventModal');
    }
};
?>

<x-layouts.app>
    @volt
    <div x-data="{
        showEmojiPicker: null,
        emojis: ['😀', '😂', '😍', '🥰', '😊', '🤔', '😢', '😮', '👍', '❤️', '🔥', '🎉', '👏', '💯', '🙏', '😎'],
        insertEmoji(emoji, target, id) {
            if (target === 'comment') {
                $wire.commentText[id] = ($wire.commentText[id] || '') + emoji;
            } else if (target === 'reply') {
                $wire.replyText[id] = ($wire.replyText[id] || '') + emoji;
            }
            this.showEmojiPicker = null;
        }
    }" 
    x-on:open-share-window.window="window.open($event.detail.url, '_blank', 'width=600,height=400')"
    x-on:copy-to-clipboard.window="navigator.clipboard.writeText($event.detail.url)">
        
        <!-- Notifications -->
        <div x-data x-on:notify.window="toastr[$event.detail.type]($event.detail.message)"></div>

        <div class="w-full bg-gradient-to-br from-green-50 via-blue-50 to-yellow-50 min-h-screen">
            <!-- Animated Background Elements -->
            <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
                <div class="absolute top-10 left-10 w-32 h-32 bg-green-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob"></div>
                <div class="absolute top-20 right-20 w-40 h-40 bg-yellow-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-20 left-40 w-36 h-36 bg-teal-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-4000"></div>
                <div class="absolute bottom-40 right-40 w-32 h-32 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-6000"></div>
            </div>

            <div class="relative z-10 container mx-auto px-4 py-6" style="max-width: 1600px;">
                <!-- Hero Section -->
                <div class="bg-gradient-to-r from-green-400 via-teal-400 to-blue-400 rounded-3xl shadow-2xl mb-6 overflow-hidden relative">
                    <div class="absolute inset-0 bg-black opacity-20"></div>
                    <div class="relative z-10 p-8 text-center">
                        <div class="flex items-center justify-center mb-4">
                            <img src="{{ asset('logo.png') }}" 
                                 alt="PARADISIA Logo" 
                                 class="w-24 h-24 rounded-full border-4 border-white shadow-lg transform hover:scale-110 transition-transform duration-300">
                        </div>
                        <h1 class="text-5xl font-bold text-white mb-2 drop-shadow-lg">🌴 PARADISIA 🍹</h1>
                    </div>
                    <div class="absolute top-0 right-0 text-6xl opacity-30 transform rotate-12">🌺</div>
                    <div class="absolute bottom-0 left-0 text-6xl opacity-30 transform -rotate-12">🥥</div>
                </div>

                <div class="grid grid-cols-12 gap-6">
                    <!-- Left Sidebar -->
                    <div class="col-span-12 lg:col-span-3 space-y-4">
                        <!-- Profil Card -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="text-center">
                                <div class="relative inline-block">
                                    @auth
                                        <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=10b981&color=fff&size=128' }}" 
                                             alt="Profile" 
                                             class="w-24 h-24 rounded-full border-4 border-green-400 shadow-lg mx-auto object-cover">
                                        <span class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-2 border-white rounded-full"></span>
                                    @else
                                        <img src="https://ui-avatars.com/api/?name=Guest&background=10b981&color=fff&size=128" 
                                             alt="Profile" 
                                             class="w-24 h-24 rounded-full border-4 border-gray-400 shadow-lg mx-auto">
                                    @endauth
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mt-4">{{ Auth::check() ? Auth::user()->name : 'Visiteur' }}</h3>
                                
                                @auth
                                <div class="flex items-center justify-center gap-4 mt-4 text-sm">
                                    <div class="text-center">
                                        <p class="font-bold text-green-600">0</p>
                                        <p class="text-gray-500 text-xs">Abonnés</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-bold text-blue-600">0</p>
                                        <p class="text-gray-500 text-xs">Suivis</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-bold text-yellow-600">0</p>
                                        <p class="text-gray-500 text-xs">Posts</p>
                                    </div>
                                </div>
                                @else
                                <div class="mt-4">
                                    <a href="{{ route('login') }}" class="inline-block bg-green-400 text-white font-semibold py-2 px-6 rounded-lg hover:shadow-lg transition-all">
                                        Se connecter
                                    </a>
                                </div>
                                @endauth
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-2xl shadow-lg p-4 border-2 border-blue-100">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <span class="text-2xl">🚀</span>
                                Actions Rapides
                            </h4>
                            <div class="space-y-2">
                                <button wire:click="redirectToInvest" class="w-full bg-gradient-to-r from-green-400 to-green-600 text-white rounded-xl py-3 px-4 font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                                    <span>💰</span> Investir Maintenant
                                </button>
                                <button wire:click="checkAuthAndOpenModal" class="w-full bg-gradient-to-r from-blue-400 to-blue-600 text-white rounded-xl py-3 px-4 font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                                    <span>📸</span> Créer un Post
                                </button>
                            </div>
                        </div>

                        <!-- Categories -->

                        <div class="bg-white rounded-2xl shadow-lg p-4 border-2 border-orange-100" 
     x-data="pointsDeVente()"
     x-init="init()">
    <h4 class="font-bold text-gray-800 mb-3 flex items-center justify-between">
        <span class="flex items-center gap-2">
            <span class="text-2xl">📍</span>
            Nos Points de Vente
        </span>
        <button @click="getUserLocation()" 
                class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded-lg hover:bg-green-200 transition-all flex items-center gap-1"
                :class="{ 'animate-pulse': locating }">
            <svg class="w-3 h-3" :class="{ 'animate-spin': locating }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span x-text="locating ? 'Localisation...' : 'Me localiser'"></span>
        </button>
    </h4>

    <!-- Message de localisation -->
    <div x-show="userLocation" x-cloak class="mb-3 p-2 bg-green-50 rounded-lg text-xs text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <span>Trié par distance depuis votre position</span>
    </div>

    <!-- Erreur de localisation -->
    <div x-show="locationError" x-cloak class="mb-3 p-2 bg-red-50 rounded-lg text-xs text-red-600 flex items-center gap-2">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <span x-text="locationError"></span>
    </div>

    <!-- Liste des points de vente -->
    <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
        <template x-for="(point, index) in sortedPoints" :key="point.id">
            <div class="p-3 rounded-xl border border-gray-100 hover:border-green-300 hover:shadow-md transition-all cursor-pointer group"
                 :class="{ 'bg-green-50 border-green-300': selectedPoint === point.id }"
                 @click="selectPoint(point)">
                <div class="flex items-start gap-3">
                    <!-- Numéro / Distance -->
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                         :class="point.isOpen ? 'bg-green-500' : 'bg-gray-400'">
                        <span x-show="!userLocation" x-text="index + 1"></span>
                        <span x-show="userLocation" class="text-xs" x-text="point.distance ? point.distance.toFixed(1) + 'km' : '-'"></span>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h5 class="font-semibold text-gray-800 text-sm truncate" x-text="point.name"></h5>
                            <span x-show="point.isOpen" class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        </div>
                        <p class="text-xs text-gray-500 truncate mt-0.5" x-text="point.address"></p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  :class="point.isOpen ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                                  x-text="point.isOpen ? 'Ouvert' : 'Fermé'"></span>
                            <span class="text-xs text-gray-400" x-text="point.hours"></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click.stop="openInMaps(point)" 
                                class="p-1.5 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-all"
                                title="Itinéraire">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                        </button>
                        <button @click.stop="callPoint(point)" 
                                class="p-1.5 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-all"
                                title="Appeler">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Mini carte -->
    <div x-show="selectedPoint" x-cloak class="mt-3">
        <div id="mini-map" class="w-full h-32 rounded-xl bg-gray-100 overflow-hidden relative">
            <!-- Placeholder carte -->
            <div x-show="!mapLoaded" class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-green-100 to-teal-100">
                <div class="text-center">
                    <svg class="w-8 h-8 text-green-500 mx-auto animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <p class="text-xs text-green-600 mt-1">Chargement...</p>
                </div>
            </div>
            <!-- Iframe Google Maps -->
            <iframe x-show="mapLoaded && selectedPointData"
                    :src="'https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8&q=' + encodeURIComponent(selectedPointData?.address || '')"
                    class="w-full h-full border-0"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
        
        <!-- Bouton voir sur la carte complète -->
        <button @click="openFullMap()" 
                class="w-full mt-2 text-xs bg-gradient-to-r from-green-500 to-teal-500 text-white py-2 px-4 rounded-lg font-semibold hover:shadow-lg transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            Voir tous les points sur la carte
        </button>
    </div>

    <!-- Bouton voir tous -->
    <a href="" 
       class="mt-3 w-full bg-orange-100 hover:bg-orange-200 text-orange-700 font-semibold py-2.5 px-4 rounded-xl transition-all flex items-center justify-center gap-2 text-sm">
        <span>Voir tous les points de vente</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </a>
</div>
                        
                    </div>

                    <!-- Center - Feed -->
                    <div class="col-span-12 lg:col-span-6 space-y-6">
                        <!-- Create Post Box -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-100 hover:shadow-2xl transition-all duration-300">
                            <div class="flex items-center gap-4 mb-4">
                                @if(Auth::check() && Auth::user()->photo)
                                    <img src="{{ Storage::url(Auth::user()->photo) }}" 
                                         alt="Your avatar" 
                                         class="w-12 h-12 rounded-full border-2 border-purple-400 object-cover">
                                @elseif(Auth::check())
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=8b5cf6&color=fff&size=64" 
                                         alt="Your avatar" 
                                         class="w-12 h-12 rounded-full border-2 border-purple-400">
                                @else
                                    <img src="https://ui-avatars.com/api/?name=You&background=8b5cf6&color=fff&size=64" 
                                         alt="Your avatar" 
                                         class="w-12 h-12 rounded-full border-2 border-purple-400">
                                @endif
                                <input type="text" 
                                       wire:click="checkAuthAndOpenModal"
                                       readonly
                                       placeholder="Partagez votre expérience Paradisia... 🌴" 
                                       class="flex-1 bg-gray-100 rounded-full px-6 py-3 cursor-pointer hover:bg-gray-200 transition-all outline-none">
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex gap-2">
                                    <button wire:click="checkAuthAndOpenModal('photo')" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-green-50 text-green-600 transition-all">
                                        <span class="text-xl">📸</span>
                                        <span class="text-sm font-semibold">Photo</span>
                                    </button>
                                    <button wire:click="checkAuthAndOpenModal('video')" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-blue-50 text-blue-600 transition-all">
                                        <span class="text-xl">🎥</span>
                                        <span class="text-sm font-semibold">Vidéo</span>
                                    </button>
                                    <button wire:click="checkAuthAndOpenModal('feeling')" class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-yellow-50 text-yellow-600 transition-all">
                                        <span class="text-xl">😊</span>
                                        <span class="text-sm font-semibold">Humeur</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                      <div class="rounded-2xl shadow-2xl overflow-hidden bg-white">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-black flex items-center gap-2">
                <span>🛒</span>
                Boutique PARADISIA
            </h3>
            <a href="{{ route('shop') }}" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold hover:shadow-lg transition-all">
                Voir tout →
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($this->featuredProducts as $product)
            <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-2 cursor-pointer border border-gray-100">
                <div class="relative">
                    @if($product->img_1)
                        <img src="{{ Storage::url($product->img_1) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-green-100 to-teal-100 flex items-center justify-center">
                            <span class="text-6xl">🍹</span>
                        </div>
                    @endif
                    
                    {{-- Badge catégorie --}}
                    @if($product->categories)
                        <span class="absolute top-3 left-3 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                            {{ $product->categories->name }}
                        </span>
                    @endif
                    
                    {{-- Badge promo (exemple) --}}
                    @if($loop->first)
                        <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                            Populaire 🔥
                        </span>
                    @endif
                </div>
                <div class="p-4">
                    <h4 class="font-bold text-gray-800 mb-2 line-clamp-1">{{ $product->name }}</h4>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ Str::limit($product->description, 60) }}</p>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xl font-bold text-green-600">{{ number_format($product->price, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <button 
                        
                                class="bg-green-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-green-600 transition-all text-sm flex items-center gap-1">
                            <span>🛍️</span> Acheter
                        </button>
                    </div>
                </div>
            </div>
            @empty
            {{-- Si aucun produit, afficher des placeholders --}}
           <div class="bg-gray-100 rounded-xl overflow-hidden shadow-lg border border-gray-200 flex items-center justify-center h-72">
    <div class="text-center p-6">
        <span class="text-5xl mb-4 block">🚫</span>
        <p class="text-gray-500 font-semibold text-lg">Produit indisponible</p>
    </div>
</div>
            @endforelse
        </div>

        {{-- Section produits supplémentaires si plus de 3 produits --}}
        @if($this->products->count() > 3)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>✨</span> Autres produits populaires
            </h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($this->products->skip(3)->take(4) as $product)
                <div class="bg-white rounded-xl overflow-hidden shadow hover:shadow-lg transition-all cursor-pointer border border-gray-100 group">
                    <div class="relative">
                        @if($product->img_1)
                            <img src="{{ Storage::url($product->img_1) }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-32 bg-gradient-to-br from-green-50 to-teal-50 flex items-center justify-center">
                                <span class="text-4xl">🍹</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-3">
                        <h5 class="font-semibold text-gray-800 text-sm line-clamp-1">{{ $product->name }}</h5>
                        <p class="text-green-600 font-bold text-sm mt-1">{{ number_format($product->price, 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

                        <!-- Publications Feed -->
                        @forelse ($this->publications as $publication)
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300" wire:key="publication-{{ $publication->id }}">
                            <!-- Header -->
                            <div class="p-4 pb-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $user = $publication->user;
                                            $userName = $user ? $user->name : 'Utilisateur supprimé';
                                            $userPhoto = ($user && $user->photo) ? Storage::url($user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=10b981&color=fff&size=64';
                                        @endphp
                                        <img src="{{ $userPhoto }}" 
                                             alt="{{ $userName }}" 
                                             class="w-11 h-11 rounded-full border-2 border-green-400 object-cover">
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm hover:underline cursor-pointer">{{ $userName }}</h4>
                                            <p class="text-xs text-gray-500">{{ $publication->created_at->diffForHumans() }} · 🌍</p>
                                        </div>
                                    </div>
                                    <button class="p-2 hover:bg-gray-100 rounded-full transition-all">
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="px-4 py-3">
                                <p class="text-gray-800 text-sm leading-relaxed">{!! nl2br(e($publication->text)) !!}</p>
                            </div>

                          


@php
    // Collecter toutes les images
    $images = collect([
        $publication->img_1,
        $publication->img_2,
        $publication->img_3,
        $publication->img_4,
        $publication->img_5,
    ])->filter()->values();
    
    $imageCount = $images->count();
    $hasVideo = !empty($publication->video);
    $hasAudio = !empty($publication->audio);
@endphp

{{-- Affichage des images --}}
@if($imageCount > 0)
<div class="relative" x-data="{ showLightbox: false, currentIndex: 0 }">
    
    {{-- 1 image --}}
    @if($imageCount === 1)
    <div class="cursor-pointer" @click="showLightbox = true; currentIndex = 0">
        <img src="{{ Storage::url($images[0]) }}" 
             alt="Publication" 
             class="w-full object-cover max-h-[500px] hover:opacity-95 transition-opacity">
    </div>
    
    {{-- 2 images --}}
    @elseif($imageCount === 2)
    <div class="grid grid-cols-2 gap-1">
        @foreach($images as $index => $img)
        <div class="cursor-pointer aspect-square overflow-hidden" @click="showLightbox = true; currentIndex = {{ $index }}">
            <img src="{{ Storage::url($img) }}" 
                 alt="Publication" 
                 class="w-full h-full object-cover hover:opacity-95 transition-opacity">
        </div>
        @endforeach
    </div>
    
    {{-- 3 images --}}
    @elseif($imageCount === 3)
    <div class="grid grid-cols-2 gap-1">
        <div class="row-span-2 cursor-pointer" @click="showLightbox = true; currentIndex = 0">
            <img src="{{ Storage::url($images[0]) }}" 
                 alt="Publication" 
                 class="w-full h-full object-cover hover:opacity-95 transition-opacity">
        </div>
        <div class="cursor-pointer aspect-square overflow-hidden" @click="showLightbox = true; currentIndex = 1">
            <img src="{{ Storage::url($images[1]) }}" 
                 alt="Publication" 
                 class="w-full h-full object-cover hover:opacity-95 transition-opacity">
        </div>
        <div class="cursor-pointer aspect-square overflow-hidden" @click="showLightbox = true; currentIndex = 2">
            <img src="{{ Storage::url($images[2]) }}" 
                 alt="Publication" 
                 class="w-full h-full object-cover hover:opacity-95 transition-opacity">
        </div>
    </div>
    
    {{-- 4 images --}}
    @elseif($imageCount === 4)
    <div class="grid grid-cols-2 gap-1">
        @foreach($images as $index => $img)
        <div class="cursor-pointer aspect-square overflow-hidden" @click="showLightbox = true; currentIndex = {{ $index }}">
            <img src="{{ Storage::url($img) }}" 
                 alt="Publication" 
                 class="w-full h-full object-cover hover:opacity-95 transition-opacity">
        </div>
        @endforeach
    </div>
    
    {{-- 5+ images --}}
    @else
    <div class="grid grid-cols-2 gap-1">
        {{-- Première grande image --}}
        <div class="col-span-2 cursor-pointer" @click="showLightbox = true; currentIndex = 0">
            <img src="{{ Storage::url($images[0]) }}" 
                 alt="Publication" 
                 class="w-full h-64 object-cover hover:opacity-95 transition-opacity">
        </div>
        {{-- 3 petites images + overlay --}}
        @for($i = 1; $i < 4; $i++)
        <div class="cursor-pointer aspect-square overflow-hidden relative" @click="showLightbox = true; currentIndex = {{ $i }}">
            <img src="{{ Storage::url($images[$i]) }}" 
                 alt="Publication" 
                 class="w-full h-full object-cover hover:opacity-95 transition-opacity">
            
            {{-- Overlay +X sur la dernière image visible --}}
            @if($i === 3 && $imageCount > 4)
            <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                <span class="text-white text-3xl font-bold">+{{ $imageCount - 4 }}</span>
            </div>
            @endif
        </div>
        @endfor
    </div>
    @endif

    {{-- Lightbox / Carrousel --}}
    <div x-show="showLightbox" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center"
         @keydown.escape.window="showLightbox = false"
         @keydown.arrow-left.window="currentIndex = (currentIndex - 1 + {{ $imageCount }}) % {{ $imageCount }}"
         @keydown.arrow-right.window="currentIndex = (currentIndex + 1) % {{ $imageCount }}">
        
        {{-- Bouton fermer --}}
        <button @click="showLightbox = false" 
                class="absolute top-4 right-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        {{-- Navigation gauche --}}
        @if($imageCount > 1)
        <button @click="currentIndex = (currentIndex - 1 + {{ $imageCount }}) % {{ $imageCount }}" 
                class="absolute left-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        
        {{-- Navigation droite --}}
        <button @click="currentIndex = (currentIndex + 1) % {{ $imageCount }}" 
                class="absolute right-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        @endif
        
        {{-- Image principale --}}
        <div class="max-w-5xl max-h-[90vh] px-16">
            @foreach($images as $index => $img)
            <img x-show="currentIndex === {{ $index }}"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 src="{{ Storage::url($img) }}" 
                 alt="Publication" 
                 class="max-w-full max-h-[85vh] object-contain mx-auto rounded-lg shadow-2xl">
            @endforeach
        </div>
        
        {{-- Indicateurs --}}
        @if($imageCount > 1)
        <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex items-center gap-2">
            @foreach($images as $index => $img)
            <button @click="currentIndex = {{ $index }}" 
                    class="w-2.5 h-2.5 rounded-full transition-all"
                    :class="currentIndex === {{ $index }} ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/80'">
            </button>
            @endforeach
        </div>
        
        {{-- Compteur --}}
        <div class="absolute top-4 left-4 bg-black/50 text-white px-4 py-2 rounded-full text-sm font-medium">
            <span x-text="currentIndex + 1"></span> / {{ $imageCount }}
        </div>
        @endif
    </div>
</div>
@endif

{{-- Affichage de la vidéo --}}
@if($hasVideo)
<div class="relative bg-black">
    <video class="w-full max-h-[500px]" controls preload="metadata" poster="{{ $imageCount > 0 ? Storage::url($images[0]) : '' }}">
        <source src="{{ Storage::url($publication->video) }}" type="video/mp4">
        Votre navigateur ne supporte pas la lecture vidéo.
    </video>
</div>
@endif

{{-- Affichage de l'audio --}}
@if($hasAudio)
<div class="px-4 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 border-t border-b border-gray-100">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-700 mb-1">🎵 Audio</p>
            <audio controls class="w-full h-10" style="max-height: 40px;">
                <source src="{{ Storage::url($publication->audio) }}" type="audio/mpeg">
                Votre navigateur ne supporte pas la lecture audio.
            </audio>
        </div>
    </div>
</div>
@endif

{{-- Style pour x-cloak --}}
<style>
    [x-cloak] { display: none !important; }
</style>



















                            <!-- Stats (Likes, Comments, Shares count) -->
                            @php
                                $likesCount = $this->getLikesCount($publication->id);
                                $commentsCount = $this->getCommentsCount($publication->id);
                                $sharesCount = $this->getSharesCount($publication->id);
                                $hasLiked = $this->hasLiked($publication->id);
                                $likedUsers = $this->getLikedUsers($publication->id);
                            @endphp
                            
                            <div class="px-4 py-2 flex items-center justify-between text-xs text-gray-500 border-b border-gray-100">
                                <div class="flex items-center gap-1">
                                    @if($likesCount > 0)
                                    <div class="flex items-center">
                                        <span class="bg-blue-500 text-white rounded-full p-1 -mr-1 z-10">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                            </svg>
                                        </span>
                                        <span class="bg-red-500 text-white rounded-full p-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                        <span class="ml-2 hover:underline cursor-pointer">
                                            @if($hasLiked && $likesCount == 1)
                                                Vous
                                            @elseif($hasLiked && $likesCount > 1)
                                                Vous et {{ $likesCount - 1 }} autre{{ $likesCount > 2 ? 's' : '' }}
                                            @else
                                                {{ $likesCount }}
                                            @endif
                                        </span>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($commentsCount > 0)
                                    <span wire:click="toggleComments({{ $publication->id }})" class="hover:underline cursor-pointer">{{ $commentsCount }} commentaire{{ $commentsCount > 1 ? 's' : '' }}</span>
                                    @endif
                                    @if($sharesCount > 0)
                                    <span class="hover:underline cursor-pointer">{{ $sharesCount }} partage{{ $sharesCount > 1 ? 's' : '' }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="px-2 py-1 flex items-center justify-around border-b border-gray-100">
                                <!-- Like Button -->
                                <button wire:click="toggleLike({{ $publication->id }})" 
                                        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-100 transition-all {{ $hasLiked ? 'text-blue-600' : 'text-gray-600' }}">
                                    @if($hasLiked)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                    </svg>
                                    @endif
                                    <span class="font-semibold text-sm">J'aime</span>
                                </button>

                                <!-- Comment Button -->
                                <button wire:click="toggleComments({{ $publication->id }})" 
                                        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <span class="font-semibold text-sm">Commenter</span>
                                </button>

                                <!-- Share Button -->
                                <button wire:click="openShareModal({{ $publication->id }})" 
                                        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                    <span class="font-semibold text-sm">Partager</span>
                                </button>
                            </div>

                            <!-- Comments Section -->
                            @if($showComments[$publication->id] ?? false)
                            <div class="p-4 bg-gray-50">
                                <!-- Add Comment -->
                                <div class="flex items-start gap-2 mb-4">
                                    @auth
                                    <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=10b981&color=fff&size=64' }}" 
                                         alt="Your avatar" 
                                         class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                    @else
                                    <img src="https://ui-avatars.com/api/?name=Guest&background=gray&color=fff&size=64" 
                                         class="w-8 h-8 rounded-full flex-shrink-0">
                                    @endauth
                                    
                                    <div class="flex-1 relative">
                                        <div class="bg-white rounded-2xl border border-gray-200 flex items-center pr-2">
                                            <input type="text" 
                                                   wire:model.defer="commentText.{{ $publication->id }}"
                                                   wire:keydown.enter="addComment({{ $publication->id }})"
                                                   placeholder="Écrire un commentaire..."
                                                   class="flex-1 bg-transparent px-4 py-2 text-sm focus:outline-none rounded-2xl">
                                            
                                            <!-- Emoji Button -->
                                            <button type="button" 
                                                    @click="showEmojiPicker = showEmojiPicker === 'comment-{{ $publication->id }}' ? null : 'comment-{{ $publication->id }}'"
                                                    class="p-1 hover:bg-gray-100 rounded-full text-gray-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>
                                            
                                            <button wire:click="addComment({{ $publication->id }})" 
                                                    class="p-1 hover:bg-gray-100 rounded-full text-blue-500">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Emoji Picker -->
                                        <div x-show="showEmojiPicker === 'comment-{{ $publication->id }}'" 
                                             x-cloak
                                             @click.away="showEmojiPicker = null"
                                             class="absolute bottom-full left-0 mb-2 bg-white rounded-lg shadow-lg border border-gray-200 p-2 z-50">
                                            <div class="grid grid-cols-8 gap-1">
                                                <template x-for="emoji in emojis" :key="emoji">
                                                    <button type="button"
                                                            @click="insertEmoji(emoji, 'comment', {{ $publication->id }})"
                                                            class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded text-lg"
                                                            x-text="emoji"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Comments List -->
                                @php
                                    $comments = $publication->comments->whereNull('parent_id')->sortByDesc('created_at');
                                @endphp
                                
                                @foreach($comments as $comment)
                                <div class="mb-3" wire:key="comment-{{ $comment->id }}">
                                    <div class="flex items-start gap-2">
                                        @php
                                            $commentUser = $comment->user;
                                            $commentUserName = $commentUser ? $commentUser->name : 'Utilisateur supprimé';
                                            $commentUserPhoto = ($commentUser && $commentUser->photo) ? Storage::url($commentUser->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($commentUserName) . '&background=6b7280&color=fff&size=64';
                                        @endphp
                                        <img src="{{ $commentUserPhoto }}" 
                                             alt="{{ $commentUserName }}" 
                                             class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                        
                                        <div class="flex-1">
                                            @if($editingCommentId === $comment->id)
                                            <!-- Edit Mode -->
                                            <div class="bg-white rounded-2xl border border-blue-300 p-2">
                                                <input type="text" 
                                                       wire:model.defer="editCommentText"
                                                       wire:keydown.enter="updateComment({{ $comment->id }})"
                                                       wire:keydown.escape="cancelEditComment"
                                                       class="w-full bg-transparent text-sm focus:outline-none">
                                                <div class="flex justify-end gap-2 mt-2">
                                                    <button wire:click="cancelEditComment" class="text-xs text-gray-500 hover:underline">Annuler</button>
                                                    <button wire:click="updateComment({{ $comment->id }})" class="text-xs text-blue-500 hover:underline font-semibold">Enregistrer</button>
                                                </div>
                                            </div>
                                            @else
                                            <!-- Display Mode -->
                                            <div class="bg-gray-200 rounded-2xl px-3 py-2 inline-block max-w-full">
                                                <p class="font-semibold text-xs text-gray-900">{{ $commentUserName }}</p>
                                                <p class="text-sm text-gray-800">{{ $comment->body }}</p>
                                            </div>
                                            
                                            <!-- Comment Actions -->
                                            <div class="flex items-center gap-3 mt-1 ml-2 text-xs">
                                                <span class="text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                                <button wire:click="likeComment({{ $comment->id }})" class="font-semibold text-gray-600 hover:underline">J'aime</button>
                                                <button wire:click="toggleReplies({{ $comment->id }})" class="font-semibold text-gray-600 hover:underline">Répondre</button>
                                                @if(Auth::check() && Auth::id() === $comment->id_user)
                                                <button wire:click="startEditComment({{ $comment->id }}, '{{ addslashes($comment->body) }}')" class="font-semibold text-gray-600 hover:underline">Modifier</button>
                                                <button wire:click="deleteComment({{ $comment->id }})" 
                                                        onclick="return confirm('Supprimer ce commentaire ?')"
                                                        class="font-semibold text-red-500 hover:underline">Supprimer</button>
                                                @endif
                                            </div>
                                            @endif
                                            
                                            <!-- Replies -->
                                            @php
                                                $replies = $publication->comments->where('parent_id', $comment->id)->sortBy('created_at');
                                            @endphp
                                            
                                            @if($replies->count() > 0)
                                            <div class="mt-2 ml-4 space-y-2">
                                                @if(!($showReplies[$comment->id] ?? false) && $replies->count() > 1)
                                                <button wire:click="toggleReplies({{ $comment->id }})" 
                                                        class="text-xs font-semibold text-gray-600 hover:underline flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                    </svg>
                                                    Voir {{ $replies->count() }} réponse{{ $replies->count() > 1 ? 's' : '' }}
                                                </button>
                                                @endif
                                                
                                                @foreach(($showReplies[$comment->id] ?? false) ? $replies : $replies->take(1) as $reply)
                                                <div class="flex items-start gap-2" wire:key="reply-{{ $reply->id }}">
                                                    @php
                                                        $replyUser = $reply->user;
                                                        $replyUserName = $replyUser ? $replyUser->name : 'Utilisateur supprimé';
                                                        $replyUserPhoto = ($replyUser && $replyUser->photo) ? Storage::url($replyUser->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($replyUserName) . '&background=9ca3af&color=fff&size=64';
                                                    @endphp
                                                    <img src="{{ $replyUserPhoto }}" 
                                                         alt="{{ $replyUserName }}" 
                                                         class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                                                    
                                                    <div class="flex-1">
                                                        <div class="bg-gray-200 rounded-2xl px-3 py-2 inline-block max-w-full">
                                                            <p class="font-semibold text-xs text-gray-900">{{ $replyUserName }}</p>
                                                            <p class="text-sm text-gray-800">{{ $reply->body }}</p>
                                                        </div>
                                                        <div class="flex items-center gap-3 mt-1 ml-2 text-xs">
                                                            <span class="text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                                            <button class="font-semibold text-gray-600 hover:underline">J'aime</button>
                                                            @if(Auth::check() && Auth::id() === $reply->id_user)
                                                            <button wire:click="deleteComment({{ $reply->id }})" 
                                                                    onclick="return confirm('Supprimer cette réponse ?')"
                                                                    class="font-semibold text-red-500 hover:underline">Supprimer</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                            
                                            <!-- Reply Input -->
                                            @if($showReplies[$comment->id] ?? false)
                                            <div class="mt-2 ml-4 flex items-start gap-2">
                                                @auth
                                                <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=10b981&color=fff&size=64' }}" 
                                                     alt="Your avatar" 
                                                     class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                                                @endauth
                                                
                                                <div class="flex-1 relative">
                                                    <div class="bg-white rounded-2xl border border-gray-200 flex items-center pr-2">
                                                        <input type="text" 
                                                               wire:model.defer="replyText.{{ $comment->id }}"
                                                               wire:keydown.enter="addReply({{ $comment->id }})"
                                                               placeholder="Répondre à {{ $commentUserName }}..."
                                                               class="flex-1 bg-transparent px-3 py-1.5 text-sm focus:outline-none rounded-2xl">
                                                        
                                                        <button type="button"
                                                                @click="showEmojiPicker = showEmojiPicker === 'reply-{{ $comment->id }}' ? null : 'reply-{{ $comment->id }}'"
                                                                class="p-1 hover:bg-gray-100 rounded-full text-gray-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </button>
                                                        
                                                        <button wire:click="addReply({{ $comment->id }})" 
                                                                class="p-1 hover:bg-gray-100 rounded-full text-blue-500">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    
                                                    <!-- Emoji Picker for Reply -->
                                                    <div x-show="showEmojiPicker === 'reply-{{ $comment->id }}'" 
                                                         x-cloak
                                                         @click.away="showEmojiPicker = null"
                                                         class="absolute bottom-full left-0 mb-2 bg-white rounded-lg shadow-lg border border-gray-200 p-2 z-50">
                                                        <div class="grid grid-cols-8 gap-1">
                                                            <template x-for="emoji in emojis" :key="emoji">
                                                                <button type="button"
                                                                        @click="insertEmoji(emoji, 'reply', {{ $comment->id }})"
                                                                        class="w-7 h-7 flex items-center justify-center hover:bg-gray-100 rounded text-sm"
                                                                        x-text="emoji"></button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                
                                @if($comments->isEmpty())
                                <p class="text-center text-gray-500 text-sm py-4">Soyez le premier à commenter ! 💬</p>
                                @endif
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="bg-white rounded-2xl shadow-lg p-12 text-center border-2 border-gray-100">
                            <div class="text-6xl mb-4">📝</div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Aucune publication</h3>
                            <p class="text-gray-600 mb-6">Soyez le premier à partager quelque chose !</p>
                            <button wire:click="checkAuthAndOpenModal" class="bg-green-400 text-white font-semibold py-3 px-8 rounded-lg hover:shadow-lg transition-all">
                                Créer une publication
                            </button>
                        </div>
                        @endforelse

                        <!-- Load More -->
                        <div class="text-center py-6">
                            <button class="bg-green-500 text-white font-bold py-3 px-8 rounded-full hover:shadow-xl transition-all duration-300 transform hover:scale-105 flex items-center gap-2 mx-auto">
                                <span>Charger plus</span>
                                <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="col-span-12 lg:col-span-3 space-y-4">
                        <!-- Services -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-100">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="text-2xl">🎯</span>
                                Nos Services
                            </h4>
                            <div class="space-y-3">
                                <a href="#" class="block p-4 rounded-xl bg-gradient-to-br from-pink-50 to-purple-50 border-2 border-pink-200 hover:shadow-lg transition-all group">
                                    <div class="flex items-start gap-3">
                                        <div class="bg-pink-500 text-white p-3 rounded-lg text-xl group-hover:scale-110 transition-transform">💒</div>
                                        <div class="flex-1">
                                            <h5 class="font-bold text-gray-800 mb-1 text-sm">Prestations Événements</h5>
                                            <p class="text-xs text-gray-600">Mariages, anniversaires, baptêmes</p>
                                        </div>
                                    </div>
                                </a>

                                <a href="#" class="block p-4 rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-200 hover:shadow-lg transition-all group">
                                    <div class="flex items-start gap-3">
                                        <div class="bg-blue-500 text-white p-3 rounded-lg text-xl group-hover:scale-110 transition-transform">🎓</div>
                                        <div class="flex-1">
                                            <h5 class="font-bold text-gray-800 mb-1 text-sm">Formation Présentiel</h5>
                                            <p class="text-xs text-gray-600">Production de jus naturels</p>
                                        </div>
                                    </div>
                                </a>

                                <a href="#" class="block p-4 rounded-xl bg-gradient-to-br from-green-50 to-teal-50 border-2 border-green-200 hover:shadow-lg transition-all group">
                                    <div class="flex items-start gap-3">
                                        <div class="bg-green-500 text-white p-3 rounded-lg text-xl group-hover:scale-110 transition-transform">💻</div>
                                        <div class="flex-1">
                                            <h5 class="font-bold text-gray-800 mb-1 text-sm">Formation En Ligne</h5>
                                            <p class="text-xs text-gray-600">Cours vidéo, webinaires</p>
                                        </div>
                                    </div>
                                </a>

                                <a href="#" class="block p-4 rounded-xl bg-gradient-to-br from-purple-50 to-indigo-50 border-2 border-purple-200 hover:shadow-lg transition-all group">
                                    <div class="flex items-start gap-3">
                                        <div class="bg-purple-500 text-white p-3 rounded-lg text-xl group-hover:scale-110 transition-transform">🏪</div>
                                        <div class="flex-1">
                                            <h5 class="font-bold text-gray-800 mb-1 text-sm">Franchise PARADISIA</h5>
                                            <p class="text-xs text-gray-600">Ouvrez votre point de vente</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Promo -->
                        <div class="bg-gradient-to-br from-green-400 via-teal-400 to-blue-500 rounded-2xl shadow-lg overflow-hidden">
                            <div class="p-6 text-center text-white">
                                <span class="text-5xl mb-3 block">🎁</span>
                                <h4 class="font-bold text-xl mb-2">Offre Spéciale !</h4>
                                <p class="text-sm mb-4 opacity-90">Inscrivez-vous à nos formations et recevez un pack cadeau exclusif</p>
                                <button class="bg-white text-green-600 font-bold py-3 px-6 rounded-full hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                                    En savoir plus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Modal -->
        @if($showShareModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click.self="closeShareModal">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-scale-in">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800">Partager</h3>
                    <button wire:click="closeShareModal" class="p-2 hover:bg-gray-100 rounded-full transition-all">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Share Options -->
                <div class="p-4">
                    <div class="grid grid-cols-4 gap-4 mb-6">
                        <!-- Facebook -->
                        <button wire:click="shareToFacebook" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-blue-50 transition-all group">
                            <div class="w-14 h-14 bg-blue-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700">Facebook</span>
                        </button>

                        <!-- WhatsApp -->
                        <button wire:click="shareToWhatsApp" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-green-50 transition-all group">
                            <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700">WhatsApp</span>
                        </button>

                        <!-- Twitter/X -->
                        <button wire:click="shareToTwitter" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-gray-100 transition-all group">
                            <div class="w-14 h-14 bg-black rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700">X</span>
                        </button>

                        <!-- Telegram -->
                        <button wire:click="shareToTelegram" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-blue-50 transition-all group">
                            <div class="w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700">Telegram</span>
                        </button>
                    </div>

                    <!-- Copy Link -->
                    <div class="border-t border-gray-200 pt-4">
                        <button wire:click="copyLink" class="w-full flex items-center justify-center gap-3 py-3 px-4 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                            </svg>
                            <span class="font-semibold text-gray-700">Copier le lien</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Create Post Modal -->
        @if($showCreatePostModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click.self="closeCreatePostModal">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-gray-200 p-4 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800">Créer une publication</h3>
                        <button wire:click="closeCreatePostModal" class="p-2 hover:bg-gray-100 rounded-full transition-all">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4">
                    <form wire:submit.prevent="publishPost">
                        @auth
                        <div class="flex items-center gap-3 mb-4">
                            <img src="{{ Auth::user()->photo ? Storage::url(Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=10b981&color=fff&size=64' }}" 
                                 class="w-10 h-10 rounded-full object-cover">
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">{{ Auth::user()->name }}</h4>
                                <select wire:model="postVisibility" class="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded-lg border-0">
                                    <option value="public">🌍 Public</option>
                                    <option value="friends">👥 Amis</option>
                                    <option value="private">🔒 Privé</option>
                                </select>
                            </div>
                        </div>
                        @endauth

                        <textarea wire:model="postContent" 
                                  rows="5" 
                                  placeholder="Que voulez-vous partager ? 🌴" 
                                  class="w-full p-3 border-0 focus:outline-none resize-none text-gray-800 text-lg"></textarea>
                        @error('postContent') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                        <div class="mt-4 p-3 border border-gray-200 rounded-xl">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Ajouter à votre publication</p>
                            <div class="flex gap-2">
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-green-50 text-green-600 cursor-pointer transition-all border border-green-200">
                                    <span>📸</span>
                                    <span class="text-sm font-semibold">Photo</span>
                                    <input type="file" wire:model="postImage" accept="image/*" class="hidden">
                                </label>
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-50 text-blue-600 cursor-pointer transition-all border border-blue-200">
                                    <span>🎥</span>
                                    <span class="text-sm font-semibold">Vidéo</span>
                                    <input type="file" wire:model="postVideo" accept="video/*" class="hidden">
                                </label>
                            </div>
                            @error('postImage') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                            @error('postVideo') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition-all">
                            Publier
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <style>
            [x-cloak] { display: none !important; }
            
            @keyframes blob {
                0% { transform: translate(0px, 0px) scale(1); }
                33% { transform: translate(30px, -50px) scale(1.1); }
                66% { transform: translate(-20px, 20px) scale(0.9); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            
            @keyframes scale-in {
                0% { transform: scale(0.9); opacity: 0; }
                100% { transform: scale(1); opacity: 1; }
            }
            
            .animate-blob { animation: blob 7s infinite; }
            .animate-scale-in { animation: scale-in 0.2s ease-out; }
            .animation-delay-2000 { animation-delay: 2s; }
            .animation-delay-4000 { animation-delay: 4s; }
            .animation-delay-6000 { animation-delay: 6s; }
            .custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #10b981;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #059669;
}
        </style>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3000 };
        </script>


<script>
function pointsDeVente() {
    return {
        // Données des points de vente (à remplacer par des données dynamiques si nécessaire)
        points: [
            {
                id: 1,
                name: 'PARADISIA Akwa',
                address: 'Rue Joss, Akwa, Douala',
                phone: '+237 6XX XXX XXX',
                lat: 4.0511,
                lng: 9.7679,
                hours: '8h - 20h',
                isOpen: true
            },
            {
                id: 2,
                name: 'PARADISIA Bonamoussadi',
                address: 'Carrefour Maetur, Bonamoussadi, Douala',
                phone: '+237 6XX XXX XXX',
                lat: 4.0833,
                lng: 9.7333,
                hours: '8h - 21h',
                isOpen: true
            },
            {
                id: 3,
                name: 'PARADISIA Bonapriso',
                address: 'Avenue De Gaulle, Bonapriso, Douala',
                phone: '+237 6XX XXX XXX',
                lat: 4.0167,
                lng: 9.7000,
                hours: '9h - 19h',
                isOpen: false
            },
            {
                id: 4,
                name: 'PARADISIA Yaoundé Centre',
                address: 'Avenue Kennedy, Centre-ville, Yaoundé',
                phone: '+237 6XX XXX XXX',
                lat: 3.8480,
                lng: 11.5021,
                hours: '8h - 20h',
                isOpen: true
            },
            {
                id: 5,
                name: 'PARADISIA Bastos',
                address: 'Quartier Bastos, Yaoundé',
                phone: '+237 6XX XXX XXX',
                lat: 3.8833,
                lng: 11.5167,
                hours: '9h - 20h',
                isOpen: true
            }
        ],
        userLocation: null,
        locating: false,
        locationError: null,
        selectedPoint: null,
        selectedPointData: null,
        mapLoaded: false,

        init() {
            // Vérifier les horaires d'ouverture
            this.updateOpenStatus();
            setInterval(() => this.updateOpenStatus(), 60000); // Mise à jour chaque minute
        },

        get sortedPoints() {
            if (!this.userLocation) {
                return this.points;
            }
            
            // Calculer les distances et trier
            return this.points
                .map(point => ({
                    ...point,
                    distance: this.calculateDistance(
                        this.userLocation.lat, 
                        this.userLocation.lng, 
                        point.lat, 
                        point.lng
                    )
                }))
                .sort((a, b) => a.distance - b.distance);
        },

        updateOpenStatus() {
            const now = new Date();
            const hour = now.getHours();
            
            this.points.forEach(point => {
                // Logique simplifiée - à adapter selon vos horaires réels
                const [openHour] = point.hours.split(' - ')[0].replace('h', '').split(':').map(Number);
                const [closeHour] = point.hours.split(' - ')[1].replace('h', '').split(':').map(Number);
                point.isOpen = hour >= openHour && hour < closeHour;
            });
        },

        getUserLocation() {
            if (!navigator.geolocation) {
                this.locationError = 'Géolocalisation non supportée';
                return;
            }

            this.locating = true;
            this.locationError = null;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    this.locating = false;
                    
                    // Sélectionner automatiquement le point le plus proche
                    if (this.sortedPoints.length > 0) {
                        this.selectPoint(this.sortedPoints[0]);
                    }
                },
                (error) => {
                    this.locating = false;
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            this.locationError = 'Accès à la position refusé';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            this.locationError = 'Position non disponible';
                            break;
                        case error.TIMEOUT:
                            this.locationError = 'Délai dépassé';
                            break;
                        default:
                            this.locationError = 'Erreur de localisation';
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 minutes de cache
                }
            );
        },

        calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Rayon de la Terre en km
            const dLat = this.deg2rad(lat2 - lat1);
            const dLon = this.deg2rad(lon2 - lon1);
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        },

        deg2rad(deg) {
            return deg * (Math.PI/180);
        },

        selectPoint(point) {
            this.selectedPoint = point.id;
            this.selectedPointData = point;
            this.mapLoaded = true;
        },

        openInMaps(point) {
            const url = this.userLocation 
                ? `https://www.google.com/maps/dir/${this.userLocation.lat},${this.userLocation.lng}/${point.lat},${point.lng}`
                : `https://www.google.com/maps/search/?api=1&query=${point.lat},${point.lng}`;
            window.open(url, '_blank');
        },

        callPoint(point) {
            window.location.href = `tel:${point.phone.replace(/\s/g, '')}`;
        },

        openFullMap() {
            // Ouvrir une carte avec tous les points
            const points = this.points.map(p => `${p.lat},${p.lng}`).join('/');
            window.open(`https://www.google.com/maps/dir/${points}`, '_blank');
        }
    }
}
</script>
    </div>
    @endvolt
</x-layouts.app>
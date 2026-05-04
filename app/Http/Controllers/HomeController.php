<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Like;
use App\Models\Product;
use App\Models\Publication;
use App\Models\Share;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    // ... (gardez la méthode index existante mais utilisez la nouvelle formatPublication ci-dessous)

    public function index(Request $request): Response
{
    $highlightId = $request->query('highlight');

    // Récupérer les publications normales
    $publications = Publication::with([
        'user',
        'comments.user',
        'comments.replies.user',
        'likes',
    ])
        ->where('status', 'Success')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    // 🆕 Récupérer la publication highlight séparément (pour le modal)
    $highlightedPublication = null;
    if ($highlightId) {
        $highlightedPublication = Publication::with([
            'user',
            'comments.user',
            'comments.replies.user',
            'likes',
        ])
            ->where('id', $highlightId)
            ->where('status', 'Success')
            ->first();
    }

    // Récupérer tous les IDs des commentaires pour les likes
    $allCommentIds = $publications
        ->flatMap(fn ($p) => $p->comments->pluck('id'))
        ->merge(
            $highlightedPublication
                ? $highlightedPublication->comments->pluck('id')
                : []
        )
        ->toArray();

    $userCommentLikes = Auth::check()
        ? CommentLike::where('id_user', Auth::id())
            ->whereIn('id_comment', $allCommentIds)
            ->pluck('id_comment')
            ->toArray()
        : [];

    $commentLikesCounts = CommentLike::whereIn('id_comment', $allCommentIds)
        ->selectRaw('id_comment, count(*) as count')
        ->groupBy('id_comment')
        ->pluck('count', 'id_comment')
        ->toArray();

    $formattedPublications = $publications->map(
        fn ($pub) => $this->formatPublication($pub, $userCommentLikes, $commentLikesCounts)
    );

    // 🆕 Formatter la publication highlight
    $formattedHighlight = $highlightedPublication
        ? $this->formatPublication($highlightedPublication, $userCommentLikes, $commentLikesCounts)
        : null;

    $featuredProducts = Product::with(['user', 'categories'])
        ->where('status', 'Success')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get()
        ->map(fn ($p) => $this->formatProduct($p));

    $otherProducts = Product::with(['user', 'categories'])
        ->where('status', 'Success')
        ->orderBy('created_at', 'desc')
        ->skip(3)
        ->take(4)
        ->get()
        ->map(fn ($p) => $this->formatProduct($p));

    return Inertia::render('dashboard/home/index', [
        'publications' => $formattedPublications,
        'highlightedPublication' => $formattedHighlight, // 🆕
        'featuredProducts' => $featuredProducts,
        'otherProducts' => $otherProducts,
        'pointsDeVente' => $this->getPointsDeVente(),
    ]);
}
    public function toggleLike(Request $request, int $publicationId): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $existingLike = Like::where('id_user', Auth::id())
            ->where('id_publication', $publicationId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            Like::create([
                'id_user' => Auth::id(),
                'id_publication' => $publicationId,
                'ip_address' => $request->ip(),
                'status' => 'Success',
            ]);
        }

        return back();
    }

    /**
     * 🆕 Liker / Unliker un commentaire
     */
    public function toggleCommentLike(Request $request, int $commentId): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $existingLike = CommentLike::where('id_user', Auth::id())
            ->where('id_comment', $commentId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            CommentLike::create([
                'id_user' => Auth::id(),
                'id_comment' => $commentId,
                'ip_address' => $request->ip(),
                'status' => 'Success',
            ]);
        }

        return back();
    }

    public function addComment(Request $request, int $publicationId): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        Comment::create([
            'id_user' => Auth::id(),
            'id_publication' => $publicationId,
            'body' => trim($validated['body']),
            'parent_id' => $validated['parent_id'] ?? null,
            'status' => 'Success',
        ]);

        return back();
    }

    public function updateComment(Request $request, int $commentId): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $comment = Comment::find($commentId);

        if (! $comment || $comment->id_user !== Auth::id()) {
            return back()->withErrors(['error' => 'Action non autorisée']);
        }

        $validated = $request->validate(['body' => 'required|string|max:1000']);

        $comment->update(['body' => trim($validated['body'])]);

        return back();
    }

    public function deleteComment(int $commentId): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $comment = Comment::find($commentId);

        if (! $comment || $comment->id_user !== Auth::id()) {
            return back()->withErrors(['error' => 'Action non autorisée']);
        }

        Comment::where('parent_id', $commentId)->delete();
        $comment->delete();

        return back();
    }

    public function publishPost(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|mimes:mp4,mov,avi|max:51200',
            'visibility' => 'nullable|in:public,friends,private',
        ]);

        $data = [
            'ref' => 'PUB_'.Str::random(10),
            'text' => $validated['content'],
            'id_user' => Auth::id(),
            'status' => 'Success',
            'type' => 'publication',
        ];

        if ($request->hasFile('image')) {
            $data['img_1'] = $request->file('image')->store('publications/images', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('publications/videos', 'public');
        }

        Publication::create($data);

        return back();
    }

    /**
     * 🆕 Modifier une publication
     */
    public function updatePost(Request $request, int $publicationId): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $publication = Publication::find($publicationId);

        if (! $publication || $publication->id_user !== Auth::id()) {
            return back()->withErrors(['error' => 'Action non autorisée']);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $publication->update(['text' => $validated['content']]);

        return back();
    }

    /**
     * 🆕 Supprimer une publication
     */
    public function deletePost(int $publicationId): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $publication = Publication::find($publicationId);

        if (! $publication || $publication->id_user !== Auth::id()) {
            return back()->withErrors(['error' => 'Action non autorisée']);
        }

        // Supprimer les fichiers associés
        if ($publication->img_1) {
            Storage::disk('public')->delete($publication->img_1);
        }
        if ($publication->video) {
            Storage::disk('public')->delete($publication->video);
        }

        // Supprimer les commentaires, likes, shares en cascade
        Comment::where('id_publication', $publicationId)->delete();
        Like::where('id_publication', $publicationId)->delete();
        Share::where('id_publication', $publicationId)->delete();

        $publication->delete();

        return back();
    }

    public function recordShare(Request $request, int $publicationId): RedirectResponse
    {
        if (! Auth::check()) {
            return back();
        }

        Share::create([
            'id_user' => Auth::id(),
            'id_publication' => $publicationId,
            'status' => 'Success',
            'ip_address' => $request->ip(),
        ]);

        return back();
    }

    /**
     * 🔄 Mise à jour : inclut les likes des commentaires
     */
    private function formatPublication(Publication $pub, array $userCommentLikes, array $commentLikesCounts): array
    {
        $images = collect([
            $pub->img_1, $pub->img_2, $pub->img_3, $pub->img_4, $pub->img_5,
        ])->filter()->map(fn ($img) => Storage::url($img))->values();

        return [
            'id' => $pub->id,
            'text' => $pub->text,
            'images' => $images,
            'video' => $pub->video ? Storage::url($pub->video) : null,
            'audio' => $pub->audio ? Storage::url($pub->audio) : null,
            'created_at' => $pub->created_at,
            'created_at_human' => $pub->created_at->diffForHumans(),
            'user' => $this->formatUser($pub->user),
            'is_owner' => Auth::check() && Auth::id() === $pub->id_user, // 🆕
            'likes_count' => $pub->likes->count(),
            'comments_count' => $pub->comments->where('status', 'Success')->count(),
            'shares_count' => Share::where('id_publication', $pub->id)->count(),
            'has_liked' => Auth::check()
                ? $pub->likes->where('id_user', Auth::id())->isNotEmpty()
                : false,
            'comments' => $pub->comments
                ->whereNull('parent_id')
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($comment) use ($pub, $userCommentLikes, $commentLikesCounts) {
                    return [
                        'id' => $comment->id,
                        'body' => $comment->body,
                        'created_at_human' => $comment->created_at->diffForHumans(),
                        'user' => $this->formatUser($comment->user),
                        'is_owner' => Auth::check() && Auth::id() === $comment->id_user,
                        'likes_count' => $commentLikesCounts[$comment->id] ?? 0, // 🆕
                        'has_liked' => in_array($comment->id, $userCommentLikes), // 🆕
                        'replies' => $pub->comments
                            ->where('parent_id', $comment->id)
                            ->sortBy('created_at')
                            ->values()
                            ->map(fn ($reply) => [
                                'id' => $reply->id,
                                'body' => $reply->body,
                                'created_at_human' => $reply->created_at->diffForHumans(),
                                'user' => $this->formatUser($reply->user),
                                'is_owner' => Auth::check() && Auth::id() === $reply->id_user,
                                'likes_count' => $commentLikesCounts[$reply->id] ?? 0, // 🆕
                                'has_liked' => in_array($reply->id, $userCommentLikes), // 🆕
                            ]),
                    ];
                }),
        ];
    }

    private function formatUser($user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'photo' => $user->photo ? Storage::url($user->photo) : null,
        ];
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image' => $product->img_1 ? Storage::url($product->img_1) : null,
            'category' => $product->categories ? [
                'id' => $product->categories->id,
                'name' => $product->categories->name,
            ] : null,
        ];
    }

    private function getPointsDeVente(): array
    {
        return [
            ['id' => 1, 'name' => 'PARADISIA Akwa', 'address' => 'Rue Joss, Akwa, Douala', 'phone' => '+237 6XX XXX XXX', 'lat' => 4.0511, 'lng' => 9.7679, 'hours' => '8h - 20h'],
            ['id' => 2, 'name' => 'PARADISIA Bonamoussadi', 'address' => 'Carrefour Maetur, Bonamoussadi, Douala', 'phone' => '+237 6XX XXX XXX', 'lat' => 4.0833, 'lng' => 9.7333, 'hours' => '8h - 21h'],
            ['id' => 3, 'name' => 'PARADISIA Bonapriso', 'address' => 'Avenue De Gaulle, Bonapriso, Douala', 'phone' => '+237 6XX XXX XXX', 'lat' => 4.0167, 'lng' => 9.7000, 'hours' => '9h - 19h'],
            ['id' => 4, 'name' => 'PARADISIA Yaoundé Centre', 'address' => 'Avenue Kennedy, Centre-ville, Yaoundé', 'phone' => '+237 6XX XXX XXX', 'lat' => 3.8480, 'lng' => 11.5021, 'hours' => '8h - 20h'],
            ['id' => 5, 'name' => 'PARADISIA Bastos', 'address' => 'Quartier Bastos, Yaoundé', 'phone' => '+237 6XX XXX XXX', 'lat' => 3.8833, 'lng' => 11.5167, 'hours' => '9h - 20h'],
        ];
    }
}
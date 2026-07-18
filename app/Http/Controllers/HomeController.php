<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Like;
use App\Models\PointDeVente;
use App\Models\Product;
use App\Models\Publication;
use App\Models\Share;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $highlightId = $request->query('highlight');

        $publications = Publication::with('user')
            ->withCount([
                'likes',
                'shares',
                'comments as comments_success_count' => fn ($q) => $q->where('status', 'Success'),
            ])
            ->where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $highlightedPublication = null;
        if ($highlightId) {
            $highlightedPublication = Publication::with('user')
                ->withCount([
                    'likes',
                    'shares',
                    'comments as comments_success_count' => fn ($q) => $q->where('status', 'Success'),
                ])
                ->where('id', $highlightId)
                ->where('status', 'Success')
                ->first();
        }

        // Charge uniquement les commentaires récents (payload borné : une
        // publication très commentée n'envoie plus des milliers de lignes).
        $this->attachRecentComments(
            $highlightedPublication ? $publications->concat([$highlightedPublication]) : $publications
        );

        // Likes de l'utilisateur sur les publications affichées : une seule requête
        $publicationIds = $publications->pluck('id')
            ->when($highlightedPublication, fn ($ids) => $ids->push($highlightedPublication->id));

        $likedPublicationIds = Auth::check()
            ? Like::where('id_user', Auth::id())
                ->whereIn('id_publication', $publicationIds)
                ->pluck('id_publication')
                ->all()
            : [];

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
            fn ($pub) => $this->formatPublication($pub, $userCommentLikes, $commentLikesCounts, $likedPublicationIds)
        );

        $formattedHighlight = $highlightedPublication
            ? $this->formatPublication($highlightedPublication, $userCommentLikes, $commentLikesCounts, $likedPublicationIds)
            : null;

        // Closures : évaluées uniquement quand la prop est demandée
        // (les rechargements partiels Inertia sautent le travail inutile).
        return Inertia::render('dashboard/home/index', [
            'publications' => $formattedPublications,
            'highlightedPublication' => $formattedHighlight,
            'featuredProducts' => fn () => Product::with(['user', 'categories'])
                ->where('status', 'Success')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()
                ->map(fn ($p) => $this->formatProduct($p)),
            'otherProducts' => fn () => Product::with(['user', 'categories'])
                ->where('status', 'Success')
                ->orderBy('created_at', 'desc')
                ->skip(3)
                ->take(4)
                ->get()
                ->map(fn ($p) => $this->formatProduct($p)),
            'pointsDeVente' => fn () => $this->getPointsDeVente(),
            'cart' => session()->get('cart', []),
        ])->withViewData([
            // Aperçu de partage : la publication mise en avant si le lien en
            // désigne une (…/?highlight=12), sinon la page d'accueil.
            'meta' => $formattedHighlight
                ? PageMeta::forPublication($formattedHighlight, url()->full())
                : PageMeta::make(
                    title: PageMeta::SITE_NAME.' — Jus naturels d\'ananas du Cameroun',
                ),
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
            'image' => 'nullable|image|max:10240',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:10240',
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

        // Jusqu'à 5 images, stockées dans img_1..img_5 (schéma existant).
        // 'image' (singulier) reste accepté pour compatibilité.
        $files = collect($request->file('images') ?? [])
            ->prepend($request->file('image'))
            ->filter()
            ->take(5)
            ->values();

        foreach ($files as $i => $file) {
            $data['img_'.($i + 1)] = $this->uploadPublicFile($file, 'uploads/publications/images');
        }

        if ($request->hasFile('video')) {
            $data['video'] = $this->uploadPublicFile($request->file('video'), 'uploads/publications/videos');
        }

        Publication::create($data);

        return back();
    }

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

    public function deletePost(int $publicationId): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $publication = Publication::find($publicationId);

        if (! $publication || $publication->id_user !== Auth::id()) {
            return back()->withErrors(['error' => 'Action non autorisée']);
        }

        foreach (['img_1', 'img_2', 'img_3', 'img_4', 'img_5', 'video'] as $media) {
            $this->deletePublicFile($publication->{$media});
        }

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
     * Attache aux publications leurs commentaires récents seulement (racines
     * + réponses), au lieu de la totalité : une publication très commentée
     * n'alourdit plus chaque affichage de l'accueil.
     */
    private function attachRecentComments($publications, int $limit = 10): void
    {
        foreach ($publications as $pub) {
            $topLevel = Comment::with('user')
                ->where('id_publication', $pub->id)
                ->whereNull('parent_id')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            $replies = $topLevel->isEmpty()
                ? collect()
                : Comment::with('user')
                    ->where('id_publication', $pub->id)
                    ->whereIn('parent_id', $topLevel->pluck('id'))
                    ->get();

            $pub->setRelation('comments', $topLevel->concat($replies));
        }
    }

    private function formatPublication(Publication $pub, array $userCommentLikes, array $commentLikesCounts, array $likedPublicationIds = []): array
    {
        $images = collect([
            $pub->img_1, $pub->img_2, $pub->img_3, $pub->img_4, $pub->img_5,
        ])->filter()->map(fn ($img) => $this->mediaUrl($img))->values();

        return [
            'id' => $pub->id,
            'text' => $pub->text,
            'images' => $images,
            'video' => $this->mediaUrl($pub->video),
            'audio' => $this->mediaUrl($pub->audio),
            'created_at' => $pub->created_at,
            'created_at_human' => $pub->created_at->diffForHumans(),
            'user' => $this->formatUser($pub->user),
            'is_owner' => Auth::check() && Auth::id() === $pub->id_user,
            'likes_count' => $pub->likes_count ?? 0,
            'comments_count' => $pub->comments_success_count ?? 0,
            'shares_count' => $pub->shares_count ?? 0,
            'has_liked' => in_array($pub->id, $likedPublicationIds, true),
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
                        'likes_count' => $commentLikesCounts[$comment->id] ?? 0,
                        'has_liked' => in_array($comment->id, $userCommentLikes),
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
                                'likes_count' => $commentLikesCounts[$reply->id] ?? 0,
                                'has_liked' => in_array($reply->id, $userCommentLikes),
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
            'photo' => $this->mediaUrl($user->photo),
        ];
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image' => $this->mediaUrl($product->img_1),
            'category' => $product->categories ? [
                'id' => $product->categories->id,
                'name' => $product->categories->name,
            ] : null,
        ];
    }

    /**
     * 🆕 Récupère les 5 premiers points de vente actifs depuis la BDD
     */
    private function getPointsDeVente(): array
    {
        return PointDeVente::where('status', 'Success')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'address' => $p->address,
                'phone' => $p->phone,
                'hours' => $p->hours,
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'image' => $this->mediaUrl($p->image),
            ])
            ->toArray();
    }
}
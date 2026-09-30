<?php

namespace App\Http\Controllers;

use App\Mail\NouveauCommentaireMail;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Event;
use App\Models\Like;
use App\Models\Notification;
use App\Models\PointDeVente;
use App\Models\Product;
use App\Models\Publication;
use App\Models\Share;
use App\Models\User;
use App\Models\View;
use App\Services\PublicationStats;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request, ?int $publication = null): Response
    {
        // /p/{id} (lien de partage) ou /?highlight={id} (ancien format)
        $highlightId = $publication ?? $request->query('highlight');

        // Graine de mélange stable pendant la session : l'ordre aléatoire ne
        // « saute » pas quand un like ou un commentaire recharge le fil, mais
        // change d'une visite à l'autre.
        $seed = $request->session()->get('feed_seed');
        if (! $seed) {
            $seed = random_int(1, 999999);
            $request->session()->put('feed_seed', $seed);
        }

        $publications = Publication::with('user')
            ->withCount([
                'likes',
                'shares',
                'comments as comments_success_count' => fn ($q) => $q->where('status', 'Success'),
            ])
            ->where('status', 'Success')
            ->inRandomOrder($seed)
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
        // unique() : la publication mise en avant figure aussi dans le fil,
        // sans quoi elle serait comptée deux fois en vues.
        $publicationIds = $publications->pluck('id')
            ->when($highlightedPublication, fn ($ids) => $ids->push($highlightedPublication->id))
            ->unique()
            ->values();

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

        $this->recordViews($request, $publicationIds->all());

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
            // Prochain événement publié, pour la carte d'accueil
            'prochainEvent' => fn () => $this->prochainEvent(),
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

    /**
     * Compte une vue par publication et par session : revoir le fil ne
     * regonfle pas les compteurs. Les écritures partent après l'envoi de la
     * réponse (terminating) — l'affichage du fil n'attend jamais dessus.
     */
    private function recordViews(Request $request, array $publicationIds): void
    {
        if ($publicationIds === []) {
            return;
        }

        $seen = $request->session()->get('viewed_publications', []);
        $fresh = array_values(array_diff($publicationIds, $seen));

        if ($fresh === []) {
            return;
        }

        // Marqué tout de suite : la session est écrite avec la réponse.
        $request->session()->put(
            'viewed_publications',
            array_slice(array_merge($seen, $fresh), -200)
        );

        $userId = Auth::id();
        $ip = $request->ip();
        $now = now();

        app()->terminating(function () use ($fresh, $userId, $ip, $now) {
            try {
                View::insert(array_map(fn ($id) => [
                    'id_publication' => $id,
                    'id_user' => $userId,
                    'ip_address' => $ip,
                    'status' => 'Success',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $fresh));

                // Compteur dénormalisé : l'affichage n'a jamais à compter.
                Publication::whereIn('id', $fresh)->increment('nbr_vews');
            } catch (\Throwable $e) {
                Log::warning('Enregistrement des vues échoué : '.$e->getMessage());
            }
        });
    }

    /**
     * Statistiques détaillées d'une publication, réservées à son auteur.
     */
    public function publicationStats(int $publicationId): JsonResponse
    {
        $publication = Publication::find($publicationId);

        if (! $publication) {
            abort(404);
        }

        if (! Auth::check() || $publication->id_user !== Auth::id()) {
            abort(403, 'Statistiques réservées à l\'auteur de la publication.');
        }

        return response()->json(PublicationStats::for($publication));
    }

    public function toggleLike(Request $request, int $publicationId): RedirectResponse|JsonResponse
    {
        if (! Auth::check()) {
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'auth' => false], 401)
                : redirect()->route('login');
        }

        $existingLike = Like::where('id_user', Auth::id())
            ->where('id_publication', $publicationId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            Like::create([
                'id_user' => Auth::id(),
                'id_publication' => $publicationId,
                'ip_address' => $request->ip(),
                'status' => 'Success',
            ]);
            $liked = true;
        }

        // Appel en arrière-plan (fetch) : réponse JSON légère, pas de
        // rechargement de tout le fil. L'UI a déjà été mise à jour côté client.
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'liked' => $liked,
                'likes_count' => Like::where('id_publication', $publicationId)->count(),
            ]);
        }

        return back();
    }

    public function toggleCommentLike(Request $request, int $commentId): RedirectResponse|JsonResponse
    {
        if (! Auth::check()) {
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'auth' => false], 401)
                : redirect()->route('login');
        }

        $existingLike = CommentLike::where('id_user', Auth::id())
            ->where('id_comment', $commentId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            CommentLike::create([
                'id_user' => Auth::id(),
                'id_comment' => $commentId,
                'ip_address' => $request->ip(),
                'status' => 'Success',
            ]);
            $liked = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'liked' => $liked,
                'likes_count' => CommentLike::where('id_comment', $commentId)->count(),
            ]);
        }

        return back();
    }

    public function addComment(Request $request, int $publicationId): RedirectResponse|JsonResponse
    {
        if (! Auth::check()) {
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'auth' => false], 401)
                : redirect()->route('login');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $comment = Comment::create([
            'id_user' => Auth::id(),
            'id_publication' => $publicationId,
            'body' => trim($validated['body']),
            'parent_id' => $validated['parent_id'] ?? null,
            'status' => 'Success',
        ]);

        // Prévenir l'auteur de la publication (notification + e-mail), sauf
        // s'il commente sa propre publication. Différé après la réponse pour
        // ne pas ralentir l'envoi du commentaire.
        $this->notifierAuteurPublication($publicationId, $comment);

        // En arrière-plan : on renvoie le commentaire créé pour que le client
        // remplace sa version optimiste, sans recharger tout le fil.
        if ($request->wantsJson()) {
            $user = Auth::user();

            return response()->json([
                'ok' => true,
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'parent_id' => $comment->parent_id,
                    'created_at_human' => $comment->created_at->diffForHumans(),
                    'likes_count' => 0,
                    'has_liked' => false,
                    'is_owner' => true,
                    'replies' => [],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'photo' => $this->mediaUrl($user->photo),
                    ],
                ],
            ]);
        }

        return back();
    }

    /**
     * Notifie l'auteur d'une publication qu'elle a été commentée : une entrée
     * en base (cloche) + un e-mail. Envoi différé après la réponse HTTP ; un
     * échec n'affecte jamais l'enregistrement du commentaire.
     */
    private function notifierAuteurPublication(int $publicationId, Comment $comment): void
    {
        $publication = Publication::find($publicationId);

        if (! $publication || ! $publication->id_user || $publication->id_user === Auth::id()) {
            return; // publication introuvable ou auteur qui se commente lui-même
        }

        $auteurId = (int) $publication->id_user;
        $acteur = Auth::user();
        $extrait = \Illuminate\Support\Str::limit($comment->body, 120);

        // Notification cloche : immédiate (légère).
        try {
            Notification::create([
                'type' => 'publication',
                'body' => "{$acteur->name} a commenté votre publication : «{$extrait}»",
                'status' => 'Success',
                'id_publication' => $publicationId,
                'id_user' => $auteurId,
                'id_actor' => $acteur->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Notification commentaire non créée : '.$e->getMessage());
        }

        // E-mail : après la réponse HTTP, pour ne pas ralentir le commentaire.
        app()->terminating(function () use ($auteurId, $acteur, $publication, $comment) {
            try {
                $destinataire = User::find($auteurId);
                if ($destinataire?->email) {
                    Mail::to($destinataire->email)->send(
                        new NouveauCommentaireMail($destinataire, $acteur, $publication, $comment)
                    );
                }
            } catch (\Throwable $e) {
                Log::error('E-mail commentaire non envoyé : '.$e->getMessage());
            }
        });
    }

    public function updateComment(Request $request, int $commentId): RedirectResponse|JsonResponse
    {
        if (! Auth::check()) {
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'auth' => false], 401)
                : redirect()->route('login');
        }

        $comment = Comment::find($commentId);

        if (! $comment || $comment->id_user !== Auth::id()) {
            return $request->wantsJson()
                ? response()->json(['ok' => false], 403)
                : back()->withErrors(['error' => 'Action non autorisée']);
        }

        $validated = $request->validate(['body' => 'required|string|max:1000']);

        $comment->update(['body' => trim($validated['body'])]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'body' => $comment->body]);
        }

        return back();
    }

    public function deleteComment(Request $request, int $commentId): RedirectResponse|JsonResponse
    {
        if (! Auth::check()) {
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'auth' => false], 401)
                : redirect()->route('login');
        }

        $comment = Comment::find($commentId);

        if (! $comment || $comment->id_user !== Auth::id()) {
            return $request->wantsJson()
                ? response()->json(['ok' => false], 403)
                : back()->withErrors(['error' => 'Action non autorisée']);
        }

        Comment::where('parent_id', $commentId)->delete();
        $comment->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

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
     * Pagination du fil : renvoie le lot suivant de publications (les plus
     * récentes d'abord). Permet d'afficher toutes les publications via
     * « Voir plus », sans tout charger d'un coup.
     */
    public function feedMore(Request $request): JsonResponse
    {
        $offset = max(0, (int) $request->query('offset', 0));

        // Même graine que le fil initial : l'ordre aléatoire reste stable,
        // donc « Voir plus » enchaîne sans doublon ni saut.
        $seed = $request->session()->get('feed_seed') ?: 1;

        $publications = Publication::with('user')
            ->withCount([
                'likes',
                'shares',
                'comments as comments_success_count' => fn ($q) => $q->where('status', 'Success'),
            ])
            ->where('status', 'Success')
            ->inRandomOrder($seed)
            ->offset($offset)
            ->limit(10)
            ->get();

        return response()->json([
            'publications' => $this->preparerFil($publications),
            'encore' => $publications->count() === 10,
        ]);
    }

    /** Prépare une collection de publications au format du fil (likes, commentaires). */
    private function preparerFil($publications): array
    {
        $this->attachRecentComments($publications);

        $likedPublicationIds = Auth::check()
            ? Like::where('id_user', Auth::id())->whereIn('id_publication', $publications->pluck('id'))->pluck('id_publication')->all()
            : [];

        $allCommentIds = $publications->flatMap(fn ($p) => $p->comments->pluck('id'))->toArray();

        $userCommentLikes = Auth::check()
            ? CommentLike::where('id_user', Auth::id())->whereIn('id_comment', $allCommentIds)->pluck('id_comment')->toArray()
            : [];

        $commentLikesCounts = CommentLike::whereIn('id_comment', $allCommentIds)
            ->selectRaw('id_comment, count(*) as count')
            ->groupBy('id_comment')
            ->pluck('count', 'id_comment')
            ->toArray();

        return $publications->map(
            fn ($pub) => $this->formatPublication($pub, $userCommentLikes, $commentLikesCounts, $likedPublicationIds)
        )->all();
    }

    /**
     * Tous les commentaires d'une publication (racines + réponses), au format
     * du fil. Appelé par « Voir tous les commentaires » puisque l'accueil n'en
     * charge que les plus récents.
     */
    public function commentsForPublication(int $id): JsonResponse
    {
        $pub = Publication::findOrFail($id);

        // Borne de sécurité : on ne renvoie pas des dizaines de milliers de
        // commentaires (publications inondées). 200 racines récentes suffisent.
        $topLevel = Comment::with('user')
            ->where('id_publication', $id)
            ->where('status', 'Success')
            ->whereNull('parent_id')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $replies = $topLevel->isEmpty()
            ? collect()
            : Comment::with('user')
                ->where('id_publication', $id)
                ->where('status', 'Success')
                ->whereIn('parent_id', $topLevel->pluck('id'))
                ->orderBy('created_at')
                ->limit(500)
                ->get();

        $pub->setRelation('comments', $topLevel->concat($replies));

        $ids = $pub->comments->pluck('id')->toArray();
        $userCommentLikes = Auth::check()
            ? CommentLike::where('id_user', Auth::id())->whereIn('id_comment', $ids)->pluck('id_comment')->toArray()
            : [];
        $commentLikesCounts = CommentLike::whereIn('id_comment', $ids)
            ->selectRaw('id_comment, count(*) as count')
            ->groupBy('id_comment')
            ->pluck('count', 'id_comment')
            ->toArray();

        $formatted = $this->formatPublication($pub, $userCommentLikes, $commentLikesCounts);

        return response()->json(['comments' => $formatted['comments']]);
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
            // Compteur dénormalisé : aucun COUNT à l'affichage du fil
            'views_count' => (int) ($pub->nbr_vews ?? 0),
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
    /** Événement publié à venir le plus proche, pour la carte d'accueil. */
    private function prochainEvent(): ?array
    {
        $e = Event::where('statut', 'publie')
            ->where('date_debut', '>=', now()->startOfDay())
            ->orderBy('date_debut')
            ->first();

        if (! $e) {
            return null;
        }

        return [
            'id' => $e->id,
            'titre' => $e->titre,
            'type' => $e->type,
            'mode_label' => $e->modeLabel(),
            'date_label' => $e->date_debut->isoFormat('dddd D MMMM YYYY [à] HH:mm'),
            'date_courte' => $e->date_debut->isoFormat('D MMM'),
            'image' => $this->mediaUrl($e->image),
            'extrait' => $e->description ? \Illuminate\Support\Str::limit($e->description, 130) : null,
            'inscriptions_ouvertes' => $e->accepteInscriptions(),
        ];
    }

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
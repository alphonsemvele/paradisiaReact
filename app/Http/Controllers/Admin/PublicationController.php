<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Publication;
use App\Models\Share;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicationController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $userId = $request->get('user');
        $hasMedia = $request->get('media');
        $period = $request->get('period');

        $query = Publication::with(['user'])
            ->withCount(['comments', 'likes']);

        if ($search) {
            $query->where('text', 'like', "%{$search}%");
        }

        // 🆕 Filtre par statut précis
        if ($status && in_array($status, ['Success', 'pending', 'failed', 'waiting', 'deleted'])) {
            $query->where('status', $status);
        }

        if ($userId) {
            $query->where('id_user', $userId);
        }

        if ($hasMedia === 'image') {
            $query->whereNotNull('img_1');
        } elseif ($hasMedia === 'video') {
            $query->whereNotNull('video');
        } elseif ($hasMedia === 'text') {
            $query->whereNull('img_1')->whereNull('video');
        }

        if ($period === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', Carbon::now()->subWeek());
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', Carbon::now()->subMonth());
        }

        $publications = $query->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $publications->getCollection()->transform(fn ($p) => $this->formatPublicationCard($p));

        // 🆕 Stats par statut
        $stats = [
            'total' => Publication::count(),
            'today' => Publication::whereDate('created_at', Carbon::today())->count(),
            'success' => Publication::where('status', 'Success')->count(),
            'pending' => Publication::where('status', 'pending')->count(),
            'waiting' => Publication::where('status', 'waiting')->count(),
            'failed' => Publication::where('status', 'failed')->count(),
            'deleted' => Publication::where('status', 'deleted')->count(),
            'with_media' => Publication::where(fn ($q) => $q->whereNotNull('img_1')->orWhereNotNull('video'))->count(),
        ];

        return Inertia::render('admin/publications/index', [
            'publications' => $publications,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'user' => $userId,
                'media' => $hasMedia,
                'period' => $period,
            ],
            'topAuthors' => User::withCount('publications')
                ->orderByDesc('publications_count')
                ->limit(5)
                ->get(['id', 'name', 'photo'])
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'photo' => $this->mediaUrl($u->photo),
                    'count' => $u->publications_count,
                ]),
        ]);
    }

    public function show(Publication $publication): Response
    {
        $publication->load(['user', 'comments.user', 'comments.replies.user']);

        $likesCount = Like::where('id_publication', $publication->id)->count();
        $sharesCount = Share::where('id_publication', $publication->id)->count();

        return Inertia::render('admin/publications/show', [
            'publication' => $this->formatPublicationFull($publication, $likesCount, $sharesCount),
            'likers' => Like::where('id_publication', $publication->id)
                ->with('user:id,name,photo')
                ->limit(20)
                ->get()
                ->map(fn ($l) => [
                    'id' => $l->id,
                    'created_at_human' => $l->created_at->diffForHumans(),
                    'user' => $l->user ? [
                        'id' => $l->user->id,
                        'name' => $l->user->name,
                        'photo' => $this->mediaUrl($l->user->photo),
                    ] : null,
                ]),
        ]);
    }

    public function update(Request $request, Publication $publication): RedirectResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $publication->update(['text' => $validated['text']]);

        return back()->with('success', 'Publication mise à jour.');
    }

    /**
     * 🆕 Changer le statut (Success, pending, failed, waiting, deleted)
     */
    public function updateStatus(Request $request, Publication $publication): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:Success,pending,failed,waiting,deleted',
        ]);

        $publication->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Publication $publication): RedirectResponse
    {
        $this->deleteFile($publication->img_1);
        $this->deleteFile($publication->img_2);
        $this->deleteFile($publication->img_3);
        $this->deleteFile($publication->img_4);
        $this->deleteFile($publication->img_5);
        $this->deleteFile($publication->video);
        $this->deleteFile($publication->audio);

        Comment::where('id_publication', $publication->id)->delete();
        Like::where('id_publication', $publication->id)->delete();
        Share::where('id_publication', $publication->id)->delete();

        $publication->delete();

        return redirect()
            ->route('admin.publications.index')
            ->with('success', 'Publication supprimée.');
    }

    public function deleteComment(Publication $publication, Comment $comment): RedirectResponse
    {
        Comment::where('parent_id', $comment->id)->delete();
        $comment->delete();

        return back()->with('success', 'Commentaire supprimé.');
    }

    /* ============ Helpers ============ */

    private function formatPublicationCard(Publication $pub): array
    {
        return [
            'id' => $pub->id,
            'text' => $pub->text ? mb_substr($pub->text, 0, 150) : null,
            'text_full_length' => mb_strlen($pub->text ?? ''),
            'img_1' => $this->mediaUrl($pub->img_1),
            'has_video' => ! empty($pub->video),
            'has_image' => ! empty($pub->img_1),
            'status' => $pub->status ?? 'pending',
            'likes_count' => $pub->likes_count,
            'comments_count' => $pub->comments_count,
            'created_at_human' => $pub->created_at->diffForHumans(),
            'user' => $pub->user ? [
                'id' => $pub->user->id,
                'name' => $pub->user->name,
                'photo' => $this->mediaUrl($pub->user->photo),
            ] : null,
        ];
    }

    private function formatPublicationFull(Publication $pub, int $likesCount, int $sharesCount): array
    {
        $images = collect([
            $pub->img_1, $pub->img_2, $pub->img_3, $pub->img_4, $pub->img_5,
        ])->filter()->map(fn ($img) => $this->mediaUrl($img))->filter()->values();

        return [
            'id' => $pub->id,
            'ref' => $pub->ref,
            'text' => $pub->text,
            'images' => $images,
            'video' => $this->mediaUrl($pub->video),
            'audio' => $this->mediaUrl($pub->audio),
            'status' => $pub->status ?? 'pending',
            'type' => $pub->type,
            'created_at' => $pub->created_at->format('d/m/Y H:i'),
            'created_at_human' => $pub->created_at->diffForHumans(),
            'updated_at_human' => $pub->updated_at->diffForHumans(),
            'likes_count' => $likesCount,
            'shares_count' => $sharesCount,
            'comments_count' => $pub->comments->where('status', 'Success')->count(),
            'user' => $pub->user ? [
                'id' => $pub->user->id,
                'name' => $pub->user->name,
                'email' => $pub->user->email,
                'photo' => $this->mediaUrl($pub->user->photo),
            ] : null,
            'comments' => $pub->comments
                ->whereNull('parent_id')
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($c) use ($pub) {
                    return [
                        'id' => $c->id,
                        'body' => $c->body,
                        'created_at_human' => $c->created_at->diffForHumans(),
                        'user' => $c->user ? [
                            'id' => $c->user->id,
                            'name' => $c->user->name,
                            'photo' => $this->mediaUrl($c->user->photo),
                        ] : null,
                        'replies' => $pub->comments
                            ->where('parent_id', $c->id)
                            ->sortBy('created_at')
                            ->values()
                            ->map(fn ($r) => [
                                'id' => $r->id,
                                'body' => $r->body,
                                'created_at_human' => $r->created_at->diffForHumans(),
                                'user' => $r->user ? [
                                    'id' => $r->user->id,
                                    'name' => $r->user->name,
                                    'photo' => $this->mediaUrl($r->user->photo),
                                ] : null,
                            ]),
                    ];
                }),
        ];
    }

    private function deleteFile(?string $path): void
    {
        if (! $path) return;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH);
            $path = ltrim($path, '/');
        }

        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Notifications de l'utilisateur connecté (cloche du header).
 */
class NotificationController extends Controller
{
    /** Dernières notifications + nombre de non-lues. */
    public function index(): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['notifications' => [], 'non_lues' => 0]);
        }

        $notifications = Notification::where('id_user', Auth::id())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (Notification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'body' => $n->body,
                'lien' => $this->lienNotification($n),
                'lue' => $n->read_at !== null,
                'date' => $n->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'non_lues' => Notification::where('id_user', Auth::id())->whereNull('read_at')->count(),
        ]);
    }

    /** Lien vers l'élément concerné par la notification. */
    private function lienNotification(Notification $n): ?string
    {
        if ($n->type === 'message' && $n->id_actor) {
            return '/messages/u/'.$n->id_actor;
        }

        if ($n->id_publication) {
            return '/p/'.$n->id_publication;
        }

        return null;
    }

    /** Marque tout comme lu. */
    public function lireTout(): JsonResponse
    {
        if (Auth::check()) {
            Notification::where('id_user', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Messagerie 1-à-1 (style WhatsApp) : liste des conversations + fil de discussion.
 */
class MessageController extends Controller
{
    /** Page messagerie, éventuellement avec une conversation ouverte. */
    public function index(Request $request, ?Conversation $conversation = null): Response
    {
        $me = $request->user()->id;

        $active = null;
        if ($conversation && $conversation->exists) {
            abort_unless($conversation->participe($me), 403);
            $this->marquerLu($conversation, $me);
            $active = $this->fmtConversationOuverte($conversation, $me);
        }

        return Inertia::render('messages/index', [
            'conversations' => $this->listeConversations($me),
            'active' => $active,
        ]);
    }

    /** Démarre (ou retrouve) une conversation avec un utilisateur. */
    public function with(User $user): RedirectResponse
    {
        $me = auth()->id();

        if ($user->id === $me) {
            return redirect()->route('messages.index');
        }

        $conversation = Conversation::entre($me, $user->id);

        return redirect()->route('messages.show', $conversation);
    }

    /** Envoie un message dans une conversation. */
    public function store(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        $me = $request->user()->id;
        abort_unless($conversation->participe($me), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $me,
            'body' => $data['body'],
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        $this->notifier($conversation, $me, $data['body']);

        if ($request->wantsJson()) {
            return response()->json(['message' => $this->fmtMessage($message, $me)]);
        }

        return back();
    }

    /** Récupère les nouveaux messages depuis un id donné (rafraîchissement live). */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $me = $request->user()->id;
        abort_unless($conversation->participe($me), 403);

        $apres = (int) $request->query('after', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $apres)
            ->orderBy('id')
            ->get()
            ->map(fn (Message $m) => $this->fmtMessage($m, $me));

        // Les messages reçus qu'on vient de voir passent en « lu ».
        $this->marquerLu($conversation, $me);

        // Jusqu'où l'autre a lu MES messages (accusés de lecture ✓✓).
        $luJusqua = (int) $conversation->messages()
            ->where('sender_id', $me)
            ->whereNotNull('read_at')
            ->max('id');

        return response()->json([
            'messages' => $messages,
            'lu_jusqua' => $luJusqua,
        ]);
    }

    /**
     * Recherche de personnes à qui écrire. Sans terme, renvoie des membres
     * récemment actifs (pour toujours proposer des contacts).
     */
    public function rechercher(Request $request): JsonResponse
    {
        $me = $request->user()->id;
        $q = trim((string) $request->query('q', ''));

        $query = User::where('id', '<>', $me)
            ->where(fn ($x) => $x->where('is_blocked', 0)->orWhereNull('is_blocked'));

        if ($q !== '') {
            $query->where(fn ($x) => $x
                ->where('name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"));
            $query->orderBy('name');
        } else {
            $query->orderByDesc('last_active');
        }

        $users = $query->limit(20)->get(['id', 'name', 'photo', 'ville'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => trim($u->name.' '.($u->last_name ?? '')),
                'photo' => $u->photo ? $this->mediaUrl($u->photo) : null,
                'ville' => $u->ville,
            ]);

        return response()->json(['users' => $users]);
    }

    /** Nombre de messages non lus (pastille du bouton messagerie). */
    public function nonLus(Request $request): JsonResponse
    {
        $me = $request->user()?->id;

        if (! $me) {
            return response()->json(['non_lus' => 0]);
        }

        $count = Message::whereHas('conversation', fn ($q) => $q
            ->where('user_one_id', $me)->orWhere('user_two_id', $me))
            ->where('sender_id', '<>', $me)
            ->whereNull('read_at')
            ->count();

        return response()->json(['non_lus' => $count]);
    }

    /* ═══════════════════════ Interne ═══════════════════════ */

    private function listeConversations(int $me): array
    {
        return Conversation::where('user_one_id', $me)->orWhere('user_two_id', $me)
            ->whereNotNull('last_message_at')
            ->orderByDesc('last_message_at')
            ->with(['userOne', 'userTwo'])
            ->limit(100)
            ->get()
            ->map(function (Conversation $c) use ($me) {
                $autre = $c->autre($me);
                $dernier = $c->messages()->orderByDesc('id')->first();

                return [
                    'id' => $c->id,
                    'autre' => $this->fmtUser($autre),
                    'dernier' => $dernier ? \Illuminate\Support\Str::limit($dernier->body, 40) : '',
                    'de_moi' => $dernier ? (int) $dernier->sender_id === $me : false,
                    'date' => $c->last_message_at?->diffForHumans(),
                    'non_lus' => $c->messages()->where('sender_id', '<>', $me)->whereNull('read_at')->count(),
                ];
            })->all();
    }

    private function fmtConversationOuverte(Conversation $c, int $me): array
    {
        return [
            'id' => $c->id,
            'autre' => $this->fmtUser($c->autre($me)),
            'messages' => $c->messages()->orderBy('id')->limit(200)->get()
                ->map(fn (Message $m) => $this->fmtMessage($m, $me))->all(),
        ];
    }

    private function fmtMessage(Message $m, int $me): array
    {
        return [
            'id' => $m->id,
            'de_moi' => (int) $m->sender_id === $me,
            'body' => $m->body,
            'date' => $m->created_at?->isoFormat('HH:mm'),
            'jour' => $this->jourLabel($m->created_at),
            'lu' => $m->read_at !== null,
        ];
    }

    private function jourLabel(?\Illuminate\Support\Carbon $d): string
    {
        if (! $d) {
            return '';
        }
        if ($d->isToday()) {
            return "Aujourd'hui";
        }
        if ($d->isYesterday()) {
            return 'Hier';
        }

        return $d->isoFormat('D MMMM YYYY');
    }

    private function fmtUser(?User $u): array
    {
        return [
            'id' => $u?->id,
            'name' => $u?->name ?? 'Utilisateur',
            'photo' => $u && $u->photo ? $this->mediaUrl($u->photo) : null,
        ];
    }

    private function marquerLu(Conversation $c, int $me): void
    {
        $c->messages()->where('sender_id', '<>', $me)->whereNull('read_at')->update(['read_at' => now()]);

        // La notification « message » de cet expéditeur devient lue.
        $autre = $c->autre($me);
        if ($autre) {
            Notification::where('id_user', $me)
                ->where('id_actor', $autre->id)
                ->where('type', 'message')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    private function notifier(Conversation $c, int $expediteurId, string $apercu): void
    {
        $destinataire = $c->autre($expediteurId);
        if (! $destinataire) {
            return;
        }

        $expediteur = User::find($expediteurId);
        $body = ($expediteur?->name ?? 'Quelqu\'un').' vous a envoyé un message : '.\Illuminate\Support\Str::limit($apercu, 60);

        // Une seule bulle non lue par expéditeur (on met à jour si elle existe).
        $notif = Notification::where('id_user', $destinataire->id)
            ->where('id_actor', $expediteurId)
            ->where('type', 'message')
            ->whereNull('read_at')
            ->first();

        if ($notif) {
            $notif->update(['body' => $body, 'updated_at' => now()]);
        } else {
            Notification::create([
                'type' => 'message',
                'body' => $body,
                'status' => 'Success',
                'id_user' => $destinataire->id,
                'id_actor' => $expediteurId,
            ]);
        }
    }
}

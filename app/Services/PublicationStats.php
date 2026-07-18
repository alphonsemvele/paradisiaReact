<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Publication;
use App\Models\Share;
use App\Models\View;
use Illuminate\Support\Facades\DB;

/**
 * Statistiques d'une publication, partagées par l'espace auteur (fil public)
 * et l'administration — une seule source de vérité pour les chiffres.
 */
class PublicationStats
{
    /** Métriques agrégées + série des 7 derniers jours. */
    public static function for(Publication $publication): array
    {
        $id = $publication->id;

        $viewsTotal = View::where('id_publication', $id)->count();
        $viewsUnique = View::where('id_publication', $id)
            ->distinct()
            ->count(DB::raw('COALESCE(id_user, ip_address)'));

        $viewsIdentified = View::where('id_publication', $id)
            ->whereNotNull('id_user')
            ->distinct()
            ->count('id_user');

        $likes = Like::where('id_publication', $id)->count();
        $comments = Comment::where('id_publication', $id)->where('status', 'Success')->count();
        $shares = Share::where('id_publication', $id)->count();
        $interactions = $likes + $comments + $shares;

        return [
            'views_total' => $viewsTotal,
            'views_unique' => $viewsUnique,
            'views_identified' => $viewsIdentified,
            'views_anonymous' => max(0, $viewsUnique - $viewsIdentified),
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'interactions' => $interactions,
            'engagement_rate' => $viewsTotal > 0
                ? round($interactions / $viewsTotal * 100, 1)
                : 0.0,
            'serie' => self::dailySeries($id),
            'published_at' => $publication->created_at->isoFormat('D MMMM YYYY [à] HH:mm'),
            'published_human' => $publication->created_at->diffForHumans(),
        ];
    }

    /**
     * Personnes ayant vu la publication : membres identifiés d'abord, puis
     * les visiteurs non connectés regroupés par adresse IP.
     */
    public static function viewers(Publication $publication, int $limit = 50): array
    {
        $id = $publication->id;

        $membres = View::where('id_publication', $id)
            ->whereNotNull('id_user')
            ->with('idUser:id,name,photo,email')
            ->selectRaw('MIN(id) as id, id_user, COUNT(*) as vues, MAX(created_at) as derniere_vue')
            ->groupBy('id_user')
            ->orderByDesc('derniere_vue')
            ->limit($limit)
            ->get()
            ->map(fn ($v) => [
                'type' => 'membre',
                'id' => $v->id_user,
                'nom' => $v->idUser?->name ?? 'Compte supprimé',
                'email' => $v->idUser?->email,
                'photo' => $v->idUser?->photo,
                'vues' => (int) $v->vues,
                'derniere_vue' => \Carbon\Carbon::parse($v->derniere_vue)->diffForHumans(),
                'derniere_vue_date' => \Carbon\Carbon::parse($v->derniere_vue)->format('d/m/Y H:i'),
            ]);

        $visiteurs = View::where('id_publication', $id)
            ->whereNull('id_user')
            ->selectRaw('ip_address, COUNT(*) as vues, MAX(created_at) as derniere_vue')
            ->groupBy('ip_address')
            ->orderByDesc('derniere_vue')
            ->limit($limit)
            ->get()
            ->map(fn ($v) => [
                'type' => 'visiteur',
                'id' => null,
                'nom' => 'Visiteur non connecté',
                'email' => null,
                'photo' => null,
                'ip' => $v->ip_address,
                'vues' => (int) $v->vues,
                'derniere_vue' => \Carbon\Carbon::parse($v->derniere_vue)->diffForHumans(),
                'derniere_vue_date' => \Carbon\Carbon::parse($v->derniere_vue)->format('d/m/Y H:i'),
            ]);

        return $membres->concat($visiteurs)->values()->all();
    }

    /** Vues par jour sur 7 jours, jours vides inclus. */
    private static function dailySeries(int $publicationId): array
    {
        $perDay = View::where('id_publication', $publicationId)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as jour, COUNT(*) as total')
            ->groupBy('jour')
            ->pluck('total', 'jour');

        return collect(range(6, 0))->map(function ($offset) use ($perDay) {
            $date = now()->subDays($offset);

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->isoFormat('ddd'),
                'total' => (int) ($perDay[$date->format('Y-m-d')] ?? 0),
            ];
        })->values()->all();
    }
}

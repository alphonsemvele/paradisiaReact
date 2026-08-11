<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\User;
use App\Support\PageMeta;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Profil public d'un utilisateur (façon réseau social) : en-tête + ses
 * publications. Bouton « Envoyer un message » vers la messagerie.
 */
class PublicProfileController extends Controller
{
    public function show(User $user): Response
    {
        $me = auth()->id();

        $publications = Publication::where('id_user', $user->id)
            ->where('status', 'Success')
            ->withCount(['comments', 'likes'])
            ->orderByDesc('created_at')
            ->limit(60)
            ->get()
            ->map(fn (Publication $p) => [
                'id' => $p->id,
                'texte' => $p->text ? Str::limit($p->text, 280) : null,
                'image' => $this->mediaUrl($p->img_1),
                'video' => $this->mediaUrl($p->video),
                'lien' => '/p/'.$p->id,
                'date' => $p->created_at?->isoFormat('D MMM YYYY'),
                'likes' => $p->likes_count,
                'commentaires' => $p->comments_count,
            ]);

        $nom = trim($user->name.' '.($user->last_name ?? ''));

        return Inertia::render('profil/show', [
            'profil' => [
                'id' => $user->id,
                'nom' => $nom,
                'photo' => $this->mediaUrl($user->photo),
                'cover' => $this->mediaUrl($user->cover_img),
                'ville' => $user->ville,
                'pays' => $user->country,
                'description' => $user->description,
                'membre_depuis' => $user->created_at?->isoFormat('MMMM YYYY'),
                'is_me' => $me === $user->id,
                'peut_ecrire' => $me !== null && $me !== $user->id,
                'nb_publications' => Publication::where('id_user', $user->id)->where('status', 'Success')->count(),
            ],
            'publications' => $publications,
        ])->withViewData([
            'meta' => PageMeta::make(
                title: $nom.' — Paradisia',
                description: $user->description ? Str::limit($user->description, 150) : "Profil de {$nom} sur Paradisia.",
                image: $this->mediaUrl($user->photo),
                type: 'profile',
            ),
        ]);
    }
}

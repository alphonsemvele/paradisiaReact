<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * « Mon profil » : la personne connectée voit et modifie son propre profil
 * (photo, couverture, bio, ville) et retrouve ses publications.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

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

        return Inertia::render('profil/moi', [
            'profil' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'ville' => $user->ville,
                'description' => $user->description,
                'photo' => $this->mediaUrl($user->photo),
                'cover' => $this->mediaUrl($user->cover_img),
                'membre_depuis' => $user->created_at?->isoFormat('MMMM YYYY'),
                'nb_publications' => Publication::where('id_user', $user->id)->where('status', 'Success')->count(),
            ],
            'publications' => $publications,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'ville' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'cover' => ['nullable', 'image', 'max:10240'],
        ]);

        $data = [
            'name' => $validated['name'],
            'last_name' => $validated['last_name'] ?? null,
            'ville' => $validated['ville'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $this->deletePublicFile($user->photo);
            $data['photo'] = $this->uploadPublicFile($request->file('photo'), 'uploads/avatars', 'avatar');
        }

        if ($request->hasFile('cover')) {
            $this->deletePublicFile($user->cover_img);
            $data['cover_img'] = $this->uploadPublicFile($request->file('cover'), 'uploads/covers', 'cover');
        }

        $user->update($data);

        return back()->with('success', 'Profil mis à jour.');
    }
}

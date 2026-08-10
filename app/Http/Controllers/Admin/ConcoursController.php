<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Résultats du jeu concours (barème Paradisia-Africa.com) : par participant,
 * likes reçus + commentaires reçus sur ses publications, hors interactions de
 * l'auteur lui-même. Calculé en direct sur la base.
 */
class ConcoursController extends Controller
{
    /** Début par défaut de la Phase 2. */
    private const DEBUT_DEFAUT = '2026-08-06 00:00:00';

    public function index(Request $request): Response
    {
        [$debut, $fin] = $this->fenetre($request);

        return Inertia::render('admin/concours/index', [
            'classement' => $this->classement($debut, $fin),
            'debut' => $debut->toIso8601String(),
            'fin' => $fin->toIso8601String(),
            'debut_label' => $debut->isoFormat('D MMMM YYYY [à] HH:mm'),
            'fin_label' => $fin->isoFormat('D MMMM YYYY [à] HH:mm'),
        ]);
    }

    /** Export Word (.doc lisible par Microsoft Word). */
    public function export(Request $request)
    {
        [$debut, $fin] = $this->fenetre($request);
        $classement = $this->classement($debut, $fin);

        $html = view('admin.concours-word', [
            'classement' => $classement,
            'debut' => $debut,
            'fin' => $fin,
        ])->render();

        $nom = 'resultats-concours-paradisia-'.$fin->format('Y-m-d').'.doc';

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$nom.'"',
        ]);
    }

    /* ═══════════════════════ Interne ═══════════════════════ */

    private function fenetre(Request $request): array
    {
        $debut = $request->filled('debut')
            ? Carbon::parse($request->input('debut'))
            : Carbon::parse(self::DEBUT_DEFAUT);

        $fin = $request->filled('fin')
            ? Carbon::parse($request->input('fin'))
            : now();

        return [$debut, $fin];
    }

    /**
     * Classement des participants.
     *
     * Un participant = un utilisateur ayant publié pendant la fenêtre. Score =
     * likes reçus (hors ses propres likes) + commentaires reçus (hors ses
     * propres commentaires), datés dans la fenêtre, sur ses publications
     * publiées pendant la fenêtre. En cas d'égalité, celui qui a atteint son
     * score en premier (dernière interaction la plus ancienne) est devant.
     */
    private function classement(Carbon $debut, Carbon $fin): array
    {
        // Publications de la phase (une photo de fruit + texte), par auteur.
        $publications = DB::table('publications')
            ->where('status', 'Success')
            ->whereBetween('created_at', [$debut, $fin])
            ->get(['id', 'id_user', 'text', 'created_at']);

        if ($publications->isEmpty()) {
            return [];
        }

        $pubIds = $publications->pluck('id')->all();
        $auteurParPub = $publications->pluck('id_user', 'id'); // pub_id => author_id

        // Likes reçus (hors auto-like), dans la fenêtre.
        $likes = DB::table('likes')
            ->whereIn('id_publication', $pubIds)
            ->where('status', 'Success')
            ->whereBetween('created_at', [$debut, $fin])
            ->get(['id_publication', 'id_user', 'created_at']);

        // Commentaires reçus (hors commentaires de l'auteur), dans la fenêtre.
        $comments = DB::table('comments')
            ->whereIn('id_publication', $pubIds)
            ->where('status', 'Success')
            ->whereBetween('created_at', [$debut, $fin])
            ->get(['id_publication', 'id_user', 'created_at']);

        // Agrégation par participant (auteur).
        $parUser = [];
        $init = fn () => ['likes' => 0, 'comments' => 0, 'pubs' => 0, 'atteint' => null];

        foreach ($publications as $p) {
            $parUser[$p->id_user] ??= $init();
            $parUser[$p->id_user]['pubs']++;
        }

        // Une personne = un point maximum par publication, même si elle like
        // ou commente plusieurs fois. On déduplique donc sur (publication,
        // utilisateur) : la clé n'est comptée qu'une fois.
        $likesVus = [];
        foreach ($likes as $l) {
            $auteur = $auteurParPub[$l->id_publication] ?? null;
            if ($auteur === null || (int) $l->id_user === (int) $auteur) {
                continue; // auto-like non compté
            }
            $cle = $l->id_publication.':'.$l->id_user;
            if (isset($likesVus[$cle])) {
                continue; // déjà compté pour cette personne sur cette publication
            }
            $likesVus[$cle] = true;
            $parUser[$auteur]['likes']++;
            $parUser[$auteur]['atteint'] = max($parUser[$auteur]['atteint'], $l->created_at);
        }

        $commentsVus = [];
        foreach ($comments as $c) {
            $auteur = $auteurParPub[$c->id_publication] ?? null;
            if ($auteur === null || (int) $c->id_user === (int) $auteur) {
                continue; // propre commentaire non compté
            }
            $cle = $c->id_publication.':'.$c->id_user;
            if (isset($commentsVus[$cle])) {
                continue; // 1000 commentaires d'une même personne = 1 seul point
            }
            $commentsVus[$cle] = true;
            $parUser[$auteur]['comments']++;
            $parUser[$auteur]['atteint'] = max($parUser[$auteur]['atteint'], $c->created_at);
        }

        // Noms des participants.
        $users = DB::table('users')
            ->whereIn('id', array_keys($parUser))
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        $lignes = collect($parUser)->map(function ($d, $uid) use ($users) {
            $total = $d['likes'] + $d['comments'];

            return [
                'user_id' => (int) $uid,
                'nom' => $users[$uid]->name ?? 'Utilisateur #'.$uid,
                'email' => $users[$uid]->email ?? null,
                'publications' => $d['pubs'],
                'likes' => $d['likes'],
                'commentaires' => $d['comments'],
                'total' => $total,
                'atteint_at' => $d['atteint'],
            ];
        })->values();

        // Tri : score décroissant, puis « atteint en premier » (le plus tôt).
        $classe = $lignes->sort(function ($a, $b) {
            if ($b['total'] !== $a['total']) {
                return $b['total'] <=> $a['total'];
            }
            // égalité : la dernière interaction la plus ancienne passe devant
            return strcmp((string) ($a['atteint_at'] ?? '9999'), (string) ($b['atteint_at'] ?? '9999'));
        })->values();

        return $classe->map(function ($ligne, $i) {
            $ligne['rang'] = $i + 1;
            $ligne['qualifie'] = $i < 4;

            return $ligne;
        })->all();
    }
}

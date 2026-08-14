<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConcoursFinalScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Résultats de la DERNIÈRE PHASE du jeu concours.
 *
 * Barème : 5 pts par réponse juste (saisies à la main, corrigées via le détail
 * de la publication) + 1 pt par like reçu + 1 pt par commentaire unique reçu
 * (calculés automatiquement, une personne = 1 point). Total = réponses×5 +
 * likes + commentaires, puis classement.
 */
class ConcoursFinalController extends Controller
{
    private const DEBUT_DEFAUT = '2026-08-11 00:00:00';

    public function index(Request $request): Response
    {
        [$debut, $fin] = $this->fenetre($request);

        return Inertia::render('admin/concours-final/index', [
            'classement' => $this->classement($debut, $fin),
            'debut' => $debut->toIso8601String(),
            'fin' => $fin->toIso8601String(),
            'debut_label' => $debut->isoFormat('D MMMM YYYY [à] HH:mm'),
            'fin_label' => $fin->isoFormat('D MMMM YYYY [à] HH:mm'),
        ]);
    }

    /** Enregistre le nombre de réponses justes (0..10) d'un participant. */
    public function saveScore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'reponses_justes' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        ConcoursFinalScore::updateOrCreate(
            ['id_user' => $data['user_id']],
            ['reponses_justes' => $data['reponses_justes']],
        );

        return back()->with('success', 'Points réponses enregistrés.');
    }

    /** Détail d'un participant : ses publications de la phase (texte complet
     *  pour corriger les réponses) + qui a liké / commenté. */
    public function participant(Request $request, int $user): JsonResponse
    {
        [$debut, $fin] = $this->fenetre($request);

        $publications = DB::table('publications')
            ->where('id_user', $user)
            ->where('status', 'Success')
            ->whereBetween('created_at', [$debut, $fin])
            ->orderBy('created_at')
            ->get(['id', 'text', 'img_1', 'created_at']);

        $auteur = DB::table('users')->where('id', $user)->first(['id', 'name', 'email']);

        $detail = $publications->map(function ($p) use ($debut, $fin, $user) {
            $likers = DB::table('likes')
                ->join('users', 'users.id', '=', 'likes.id_user')
                ->where('likes.id_publication', $p->id)
                ->where('likes.status', 'Success')
                ->where('likes.id_user', '<>', $user)
                ->whereBetween('likes.created_at', [$debut, $fin])
                ->distinct()->pluck('users.name')->all();

            $commenters = DB::table('comments')
                ->join('users', 'users.id', '=', 'comments.id_user')
                ->where('comments.id_publication', $p->id)
                ->where('comments.status', 'Success')
                ->where('comments.id_user', '<>', $user)
                ->whereBetween('comments.created_at', [$debut, $fin])
                ->groupBy('users.id', 'users.name')
                ->selectRaw('users.name as nom, COUNT(*) as nb')
                ->get()->map(fn ($c) => ['nom' => $c->nom, 'nb' => (int) $c->nb])->all();

            return [
                'id' => $p->id,
                'texte' => $p->text ?: '(sans texte)',
                'image' => $this->mediaUrl($p->img_1),
                'lien' => '/p/'.$p->id,
                'date' => Carbon::parse($p->created_at)->isoFormat('D MMM YYYY [à] HH:mm'),
                'likers' => $likers,
                'commenters' => $commenters,
            ];
        });

        return response()->json([
            'nom' => $auteur->name ?? 'Utilisateur #'.$user,
            'email' => $auteur->email ?? null,
            'publications' => $detail,
        ]);
    }

    /** Export Word (.doc). */
    public function export(Request $request)
    {
        [$debut, $fin] = $this->fenetre($request);

        $html = view('admin.concours-final-word', [
            'classement' => $this->classement($debut, $fin),
            'debut' => $debut,
            'fin' => $fin,
        ])->render();

        $nom = 'resultats-derniere-phase-paradisia-'.$fin->format('Y-m-d').'.doc';

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

        $fin = $request->filled('fin') ? Carbon::parse($request->input('fin')) : now();

        return [$debut, $fin];
    }

    /**
     * Classement : likes + commentaires uniques (auto) + réponses×5 (manuel),
     * total et rang.
     */
    private function classement(Carbon $debut, Carbon $fin): array
    {
        $publications = DB::table('publications')
            ->where('status', 'Success')
            ->whereBetween('created_at', [$debut, $fin])
            ->get(['id', 'id_user', 'created_at']);

        if ($publications->isEmpty()) {
            return [];
        }

        $pubIds = $publications->pluck('id')->all();
        $auteurParPub = $publications->pluck('id_user', 'id');

        $likes = DB::table('likes')
            ->whereIn('id_publication', $pubIds)->where('status', 'Success')
            ->whereBetween('created_at', [$debut, $fin])
            ->get(['id_publication', 'id_user', 'created_at']);

        $comments = DB::table('comments')
            ->whereIn('id_publication', $pubIds)->where('status', 'Success')
            ->whereBetween('created_at', [$debut, $fin])
            ->get(['id_publication', 'id_user', 'created_at']);

        $parUser = [];
        $init = fn () => ['likes' => 0, 'comments' => 0, 'pubs' => 0, 'atteint' => null];

        foreach ($publications as $p) {
            $parUser[$p->id_user] ??= $init();
            $parUser[$p->id_user]['pubs']++;
        }

        $vus = [];
        foreach ($likes as $l) {
            $a = $auteurParPub[$l->id_publication] ?? null;
            if ($a === null || (int) $l->id_user === (int) $a) {
                continue;
            }
            $cle = 'l:'.$l->id_publication.':'.$l->id_user;
            if (isset($vus[$cle])) {
                continue;
            }
            $vus[$cle] = true;
            $parUser[$a]['likes']++;
            $parUser[$a]['atteint'] = max($parUser[$a]['atteint'], $l->created_at);
        }
        foreach ($comments as $c) {
            $a = $auteurParPub[$c->id_publication] ?? null;
            if ($a === null || (int) $c->id_user === (int) $a) {
                continue;
            }
            $cle = 'c:'.$c->id_publication.':'.$c->id_user;
            if (isset($vus[$cle])) {
                continue;
            }
            $vus[$cle] = true;
            $parUser[$a]['comments']++;
            $parUser[$a]['atteint'] = max($parUser[$a]['atteint'], $c->created_at);
        }

        $users = DB::table('users')->whereIn('id', array_keys($parUser))->get(['id', 'name', 'email'])->keyBy('id');
        $scores = ConcoursFinalScore::whereIn('id_user', array_keys($parUser))->pluck('reponses_justes', 'id_user');

        $lignes = collect($parUser)->map(function ($d, $uid) use ($users, $scores) {
            $reponses = (int) ($scores[$uid] ?? 0);
            $ptsReponses = $reponses * 5;
            $total = $ptsReponses + $d['likes'] + $d['comments'];

            return [
                'user_id' => (int) $uid,
                'nom' => $users[$uid]->name ?? 'Utilisateur #'.$uid,
                'email' => $users[$uid]->email ?? null,
                'publications' => $d['pubs'],
                'reponses_justes' => $reponses,
                'points_reponses' => $ptsReponses,
                'likes' => $d['likes'],
                'commentaires' => $d['comments'],
                'total' => $total,
                'atteint_at' => $d['atteint'],
            ];
        })->values();

        $classe = $lignes->sort(function ($a, $b) {
            if ($b['total'] !== $a['total']) {
                return $b['total'] <=> $a['total'];
            }

            return strcmp((string) ($a['atteint_at'] ?? '9999'), (string) ($b['atteint_at'] ?? '9999'));
        })->values();

        return $classe->map(function ($ligne, $i) {
            $ligne['rang'] = $i + 1;
            $ligne['qualifie'] = $i < 6; // 6 finalistes

            return $ligne;
        })->all();
    }
}

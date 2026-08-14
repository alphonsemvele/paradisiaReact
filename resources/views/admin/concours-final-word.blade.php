<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" lang="fr">
<head>
<meta charset="UTF-8">
<title>Résultats — Dernière phase PARADISIA</title>
<style>
  body{font-family:Calibri,Arial,sans-serif;color:#1a2b20;font-size:11pt}
  h1{color:#0d7a4f;font-size:20pt;margin:0}
  h2{color:#14532d;font-size:13pt;margin:18px 0 6px}
  .sub{color:#666;font-size:10pt;margin:2px 0 14px}
  table{border-collapse:collapse;width:100%;margin-top:8px}
  th{background:#0d7a4f;color:#fff;padding:7px 9px;text-align:left;font-size:10pt}
  th.r,td.r{text-align:right}
  td{border-bottom:1px solid #dfe9e2;padding:7px 9px;font-size:10.5pt}
  tr.qualifie td{background:#eafaf1;font-weight:bold}
  .medaille{font-weight:bold}
  .note{font-size:9pt;color:#777;margin-top:14px;line-height:1.5}
</style>
</head>
<body>
  <h1>🍍 Jeu Concours PARADISIA — DERNIÈRE PHASE</h1>
  <p class="sub">
    Barème : 5 points par réponse juste + 1 point par like reçu + 1 point par
    commentaire unique reçu (hors interactions de l'auteur).<br>
    Période : du {{ $debut->isoFormat('D MMMM YYYY [à] HH:mm') }}
    au {{ $fin->isoFormat('D MMMM YYYY [à] HH:mm') }}.
  </p>

  <h2>Classement ({{ count($classement) }} participant{{ count($classement) > 1 ? 's' : '' }})</h2>

  @if (count($classement) === 0)
    <p>Aucune publication n'a été trouvée sur la période.</p>
  @else
    <table>
      <thead>
        <tr>
          <th>Rang</th>
          <th>Participant</th>
          <th class="r">Réponses justes</th>
          <th class="r">Pts réponses</th>
          <th class="r">Likes</th>
          <th class="r">Commentaires</th>
          <th class="r">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($classement as $l)
          <tr class="{{ $l['qualifie'] ? 'qualifie' : '' }}">
            <td class="medaille">
              @if ($l['rang'] === 1) 🥇 1
              @elseif ($l['rang'] === 2) 🥈 2
              @elseif ($l['rang'] === 3) 🥉 3
              @else {{ $l['rang'] }}
              @endif
            </td>
            <td>{{ $l['nom'] }}@if($l['email']) <span style="color:#888;font-size:9pt">({{ $l['email'] }})</span>@endif</td>
            <td class="r">{{ $l['reponses_justes'] }}/10</td>
            <td class="r">{{ $l['points_reponses'] }}</td>
            <td class="r">{{ $l['likes'] }}</td>
            <td class="r">{{ $l['commentaires'] }}</td>
            <td class="r">{{ $l['total'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h2>🏆 Podium</h2>
    <ol>
      @foreach (array_slice($classement, 0, 6) as $l)
        <li><strong>{{ $l['nom'] }}</strong> — {{ $l['total'] }} points
          ({{ $l['points_reponses'] }} réponses + {{ $l['likes'] }} likes + {{ $l['commentaires'] }} commentaires)</li>
      @endforeach
    </ol>
  @endif

  <p class="note">
    Les points « réponses » sont saisis à la main après correction des publications.
    Les likes/commentaires sont dédupliqués (une personne = 1 point), auto-likes et
    commentaires de l'auteur exclus. En cas d'égalité, le participant ayant atteint son
    score en premier passe devant.<br>
    Document généré le {{ now()->isoFormat('D MMMM YYYY [à] HH:mm') }} — Paradisia-Africa.com
  </p>
</body>
</html>

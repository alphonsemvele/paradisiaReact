<?php
    $couleur = $ticket->team?->couleur ?: '#F5B301';
    $equipe = $ticket->team?->nom;
    $estFan = $ticket->type === 'fan';
    $titulaire = $ticket->user?->name ?: 'Festyvalier';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Votre ticket PARADISIA FESTY</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f7f5;color:#18211b;-webkit-font-smoothing:antialiased}
  .wrap{max-width:560px;margin:32px auto;padding:0 16px}
  .head{text-align:center;margin-bottom:22px}
  .brand{font-size:22px;font-weight:800;color:#14532d;letter-spacing:-.02em}
  .brand span{color:#E8792B}
  .lead{font-size:14px;color:#5b6b60;line-height:1.7;margin:14px 4px 22px}
  /* Le ticket */
  .ticket{background:#0b2e1a;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(11,46,26,.25)}
  .ticket-top{padding:26px 28px;background:linear-gradient(135deg,#0b2e1a,#14532d);position:relative}
  .kind{display:inline-block;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#0b2e1a;background:<?php echo $couleur; ?>;padding:5px 12px;border-radius:999px}
  .festy{margin-top:14px;font-size:26px;font-weight:800;color:#fff;letter-spacing:-.01em}
  .festy small{display:block;font-size:12px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.12em;text-transform:uppercase;margin-top:4px}
  /* Séparateur perforé */
  .perf{height:0;border-top:2px dashed rgba(255,255,255,.28);position:relative}
  .notch{position:absolute;top:-11px;width:22px;height:22px;border-radius:50%;background:#f4f7f5}
  .notch.l{left:-11px}.notch.r{right:-11px}
  .ticket-body{background:#fff;padding:24px 28px}
  .row{padding:11px 0;border-bottom:1px solid #f0f4f1}
  .row:last-child{border-bottom:none}
  .lbl{font-size:11px;color:#9fb5a8;text-transform:uppercase;letter-spacing:.06em}
  .val{font-size:15px;font-weight:700;color:#18211b;margin-top:2px}
  .code-box{margin-top:18px;text-align:center;background:#f2f9f5;border:1px dashed <?php echo $couleur; ?>;border-radius:14px;padding:16px}
  .code-box .lbl{margin-bottom:6px}
  .code{font-size:26px;font-weight:800;letter-spacing:.18em;color:#14532d;font-family:'Courier New',monospace}
  .cta{display:block;text-align:center;margin-top:18px;background:#25D366;color:#fff;font-weight:800;text-decoration:none;padding:13px;border-radius:12px;font-size:14px}
  .foot{text-align:center;margin-top:22px}
  .foot p{font-size:11px;color:#9fb5a8;line-height:1.7}
  .foot a{color:#059669;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <div class="brand">PARADISIA <span>FESTY</span></div>
  </div>

  <p class="lead">Bonjour {{ $titulaire }} 👋<br/>Ton paiement est confirmé — voici ton ticket officiel. Présente le code ci-dessous le jour J.</p>

  <div class="ticket">
    <div class="ticket-top">
      <span class="kind">Ticket {{ $estFan ? 'Fan' : 'Participant' }}</span>
      <div class="festy">PARADISIA FESTY<small>{{ $ticket->team ? 'Équipe '.$equipe : 'Le grand challenge' }}</small></div>
    </div>

    <div style="position:relative;background:linear-gradient(135deg,#0b2e1a,#14532d)">
      <div class="perf"><span class="notch l"></span><span class="notch r"></span></div>
    </div>

    <div class="ticket-body">
      <div class="row">
        <div class="lbl">Titulaire</div>
        <div class="val">{{ $titulaire }}</div>
      </div>
      @if($ticket->team)
      <div class="row">
        <div class="lbl">Équipe</div>
        <div class="val" style="color:{{ $couleur }}">{{ $equipe }}</div>
      </div>
      @endif
      <div class="row">
        <div class="lbl">Formule</div>
        <div class="val">{{ $estFan ? 'Fan' : 'Participant' }}</div>
      </div>
      <div class="row">
        <div class="lbl">Montant payé</div>
        <div class="val">{{ number_format((float) $ticket->montant, 0, ',', ' ') }} FCFA @if($ticket->promo)<span style="color:#E8792B;font-size:12px;font-weight:700"> · promo</span>@endif</div>
      </div>
      <div class="row">
        <div class="lbl">Date</div>
        <div class="val">{{ $ticket->paid_at?->format('d/m/Y à H:i') ?? $ticket->created_at?->format('d/m/Y à H:i') }}</div>
      </div>

      <div class="code-box">
        <div class="lbl">Code du ticket</div>
        <div class="code">{{ $ticket->code_ticket }}</div>
      </div>

      @if($ticket->team)
        <p style="margin-top:16px;font-size:13px;color:#5b6b60;text-align:center">Retrouve le groupe WhatsApp de l'équipe {{ $equipe }} depuis ton espace Paradisia.</p>
      @endif
    </div>
  </div>

  <div class="foot">
    <p>
      Une question ? Contacte-nous au <strong>+237 687 98 42 82</strong>.<br/>
      PARADISIA Africa
    </p>
  </div>
</div>
</body>
</html>

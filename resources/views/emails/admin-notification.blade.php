<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>{{ $sujet }}</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f7f5;color:#18211b}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(16,185,129,.1)}
  .head{background:linear-gradient(135deg,#0b2e1a,#14532d);padding:20px 28px}
  .head .k{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#8fd7b4}
  .head .t{font-size:18px;font-weight:800;color:#fff;margin-top:2px}
  .body{padding:24px 28px}
  .msg{font-size:14px;color:#2b3a30;line-height:1.7;white-space:pre-line}
  .btn{display:inline-block;margin-top:20px;background:#14532d;color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:10px}
  .foot{background:#f2f9f5;border-top:1px solid #e6f2ea;padding:16px 28px;text-align:center}
  .foot p{font-size:11px;color:#9fb5a8;line-height:1.6}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <div class="k">Notification administration</div>
    <div class="t">PARADISIA</div>
  </div>
  <div class="body">
    <div class="msg">{{ $contenu }}</div>
    @if(!empty($lien))
      <a class="btn" href="{{ $lien }}">Voir / traiter dans l'administration →</a>
    @endif
  </div>
  <div class="foot">
    <p>E-mail automatique envoyé aux administrateurs Paradisia.<br>Gère les destinataires dans Admin → Réglages → Notifications.</p>
  </div>
</div>
</body>
</html>

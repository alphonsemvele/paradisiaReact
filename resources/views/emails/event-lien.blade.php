<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/><title>Lien de connexion — PARADISIA</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f7f5;color:#18211b}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(16,185,129,.12)}
  .header{background:linear-gradient(135deg,#059669,#0d9488);padding:34px 40px;text-align:center}
  .logo{font-size:24px;font-weight:800;color:#fff}
  .body{padding:36px 40px}
  h1{font-size:21px;font-weight:800;margin-bottom:12px}
  p{font-size:14px;color:#5b6b60;line-height:1.75;margin-bottom:14px}
  .btn{display:inline-block;background:linear-gradient(135deg,#059669,#0d9488);color:#fff !important;text-decoration:none;font-weight:700;font-size:15px;padding:15px 30px;border-radius:12px}
  .btn-wrap{text-align:center;margin:24px 0}
  .info-row{display:flex;justify-content:space-between;padding:10px 14px;background:#f2f9f5;border-radius:10px;margin-bottom:8px}
  .info-label{font-size:12px;color:#8aa294}.info-value{font-size:12px;font-weight:700;text-align:right}
  .footer{background:#f2f9f5;border-top:1px solid #e6f2ea;padding:22px 40px;text-align:center}
  .footer p{font-size:11px;color:#9fb5a8}.footer a{color:#059669;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="header"><div class="logo">PARADISIA</div></div>
  <div class="body">
    <p>Bonjour{{ $inscription->nom ? ' '.$inscription->nom : '' }} 👋</p>
    <h1>C'est bientôt l'heure !</h1>
    <p>Voici votre lien pour rejoindre <strong>{{ $event->titre }}</strong>.</p>

    <div class="info-row"><span class="info-label">Date</span><span class="info-value">{{ $event->date_debut->isoFormat('dddd D MMMM [à] HH:mm') }}</span></div>
    <div class="info-row"><span class="info-label">Format</span><span class="info-value">{{ $event->modeLabel() }}</span></div>

    <div class="btn-wrap">
      <a href="{{ $event->lien_reunion }}" class="btn">Rejoindre la réunion</a>
    </div>
    <p style="font-size:12px;color:#9db8a4;word-break:break-all">Lien : {{ $event->lien_reunion }}</p>
  </div>
  <div class="footer"><p>PARADISIA Africa<br><a href="https://paradisia-africa.com">paradisia-africa.com</a></p></div>
</div>
</body>
</html>

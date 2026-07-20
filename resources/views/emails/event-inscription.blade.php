<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<title>Inscription confirmée — PARADISIA</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f7f5;color:#18211b}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(16,185,129,.12)}
  .header{background:linear-gradient(135deg,#059669,#0d9488);padding:34px 40px;text-align:center}
  .logo{font-size:24px;font-weight:800;color:#fff}
  .logo-sub{font-size:10px;color:rgba(255,255,255,.6);letter-spacing:.12em;text-transform:uppercase;margin-top:4px}
  .body{padding:36px 40px}
  h1{font-size:21px;font-weight:800;margin-bottom:12px}
  p{font-size:14px;color:#5b6b60;line-height:1.75;margin-bottom:14px}
  .info-row{display:flex;justify-content:space-between;padding:10px 14px;background:#f2f9f5;border-radius:10px;margin-bottom:8px}
  .info-label{font-size:12px;color:#8aa294}
  .info-value{font-size:12px;font-weight:700;color:#18211b;text-align:right}
  .note{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-top:18px}
  .note p{font-size:13px;color:#1e40af;margin:0}
  .footer{background:#f2f9f5;border-top:1px solid #e6f2ea;padding:22px 40px;text-align:center}
  .footer p{font-size:11px;color:#9fb5a8;line-height:1.7}
  .footer a{color:#059669;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">PARADISIA</div>
    <div class="logo-sub">Africa</div>
  </div>
  <div class="body">
    <p>Bonjour{{ $inscription->nom ? ' '.$inscription->nom : '' }} 👋</p>
    <h1>Votre inscription est confirmée</h1>
    <p>Merci de vous être inscrit à <strong>{{ $event->titre }}</strong>. Voici le récapitulatif :</p>

    <div class="info-row"><span class="info-label">Événement</span><span class="info-value">{{ $event->titre }}</span></div>
    <div class="info-row"><span class="info-label">Date</span><span class="info-value">{{ $event->date_debut->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}</span></div>
    <div class="info-row"><span class="info-label">Format</span><span class="info-value">{{ $event->modeLabel() }}</span></div>
    @if ($inscription->profil)
      <div class="info-row"><span class="info-label">Profil</span><span class="info-value">{{ $inscription->profilLabel() }}</span></div>
    @endif
    @if ($inscription->pays)
      <div class="info-row"><span class="info-label">Pays</span><span class="info-value">{{ $inscription->pays }}</span></div>
    @endif

    <div class="note">
      <p>
        @if ($event->message_confirmation)
          {{ $event->message_confirmation }}
        @else
          Le lien de la réunion en ligne vous sera envoyé par e-mail le moment venu. Gardez un œil sur votre boîte de réception.
        @endif
      </p>
    </div>

    <p style="margin-top:18px">Une question ? WhatsApp : <strong>+237 687 98 42 82</strong></p>
  </div>
  <div class="footer">
    <p>PARADISIA Africa<br><a href="https://paradisia-africa.com">paradisia-africa.com</a></p>
  </div>
</div>
</body>
</html>

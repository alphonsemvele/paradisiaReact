<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>{{ $campagne->sujet }}</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f7f5;color:#18211b}
  .wrap{max-width:600px;margin:32px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(16,185,129,.12)}
  .header{background:linear-gradient(135deg,#059669,#0d9488);padding:28px 40px;text-align:center}
  .logo{font-size:22px;font-weight:800;color:#fff}
  .body{padding:32px 40px;font-size:15px;color:#2b3a30;line-height:1.75}
  .footer{background:#f2f9f5;border-top:1px solid #e6f2ea;padding:20px 40px;text-align:center}
  .footer p{font-size:11px;color:#9fb5a8}.footer a{color:#059669;text-decoration:none}
</style></head>
<body><div class="wrap">
  <div class="header"><div class="logo">PARADISIA</div></div>
  <div class="body">{!! nl2br(e($campagne->contenu)) !!}</div>
  <div class="footer">
    <p>PARADISIA Africa — Jus naturels d'ananas<br>
    <a href="https://paradisia-africa.com">paradisia-africa.com</a> · WhatsApp +237 687 98 42 82</p>
  </div>
</div></body></html>

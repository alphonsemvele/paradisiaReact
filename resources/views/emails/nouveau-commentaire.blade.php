<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>Nouveau commentaire — PARADISIA</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f7f5;color:#18211b}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(16,185,129,.12)}
  .header{background:linear-gradient(135deg,#059669,#0d9488);padding:30px 40px;text-align:center}
  .logo{font-size:22px;font-weight:800;color:#fff}
  .body{padding:32px 40px}
  h1{font-size:19px;font-weight:800;margin-bottom:12px}
  p{font-size:14px;color:#5b6b60;line-height:1.7;margin-bottom:12px}
  .quote{background:#f2f9f5;border-left:3px solid #10b981;border-radius:8px;padding:12px 16px;margin:14px 0;font-size:14px;color:#374151;font-style:italic}
  .btn{display:inline-block;background:#059669;color:#fff !important;text-decoration:none;font-weight:700;font-size:14px;padding:12px 26px;border-radius:10px;margin-top:8px}
  .footer{background:#f2f9f5;border-top:1px solid #e6f2ea;padding:20px 40px;text-align:center}
  .footer p{font-size:11px;color:#9fb5a8}.footer a{color:#059669;text-decoration:none}
</style></head>
<body><div class="wrap">
  <div class="header"><div class="logo">PARADISIA</div></div>
  <div class="body">
    <p>Bonjour {{ $destinataire->name }},</p>
    <h1>💬 {{ $auteurCommentaire->name }} a commenté votre publication</h1>
    <div class="quote">{{ \Illuminate\Support\Str::limit($commentaire->body, 300) }}</div>
    <a href="https://paradisia-africa.com/p/{{ $publication->id }}" class="btn">Voir la publication</a>
  </div>
  <div class="footer"><p>PARADISIA Africa<br><a href="https://paradisia-africa.com">paradisia-africa.com</a></p></div>
</div></body></html>

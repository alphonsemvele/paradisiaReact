<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Diagnostic e-mail — {{ config('app.name') }}</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,'Helvetica Neue',Arial,sans-serif;background:#f4f7f5;color:#18211b;padding:24px}
  .wrap{max-width:640px;margin:0 auto;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 4px 20px rgba(16,185,129,.1)}
  .head{background:linear-gradient(135deg,#059669,#0d9488);padding:24px 28px;color:#fff}
  .head h1{font-size:19px;font-weight:800}
  .head p{font-size:12px;opacity:.85;margin-top:4px}
  .body{padding:24px 28px}
  .row{display:flex;justify-content:space-between;gap:12px;padding:10px 14px;background:#f2f9f5;border-radius:9px;margin-bottom:7px}
  .k{font-size:12px;color:#7d9488}
  .v{font-size:12px;font-weight:700;text-align:right;word-break:break-all}
  .bad{color:#dc2626}.good{color:#059669}
  form{margin-top:20px;display:flex;gap:8px;flex-wrap:wrap}
  input{flex:1;min-width:200px;padding:11px 13px;border:1px solid #d7e5dc;border-radius:9px;font-size:14px}
  button{padding:11px 20px;background:#059669;color:#fff;border:0;border-radius:9px;font-weight:700;font-size:14px;cursor:pointer}
  .res{margin-top:16px;padding:14px 16px;border-radius:10px;font-size:13px;line-height:1.6}
  .res.ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d}
  .res.ko{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c}
  .hint{margin-top:18px;padding:14px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;font-size:12px;color:#92400e;line-height:1.7}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <h1>Diagnostic d'envoi d'e-mail</h1>
    <p>Configuration réellement utilisée par le serveur</p>
  </div>
  <div class="body">
    @foreach ($config as $cle => $valeur)
      <div class="row">
        <span class="k">{{ str_replace('_', ' ', $cle) }}</span>
        <span class="v {{ str_contains((string) $valeur, 'NON') ? 'bad' : '' }}">{{ $valeur }}</span>
      </div>
    @endforeach

    <form method="POST">
      @csrf
      <input type="email" name="email" placeholder="Adresse de test" value="{{ $emailParDefaut }}" required>
      <button type="submit">Envoyer un test</button>
    </form>

    @if ($resultat)
      <div class="res {{ $resultat['ok'] ? 'ok' : 'ko' }}">{{ $resultat['message'] }}</div>
    @endif

    <div class="hint">
      <strong>Si « transport » n'affiche pas sendmail :</strong> le cache de configuration
      est obsolète. Le .env a bien été modifié, mais Laravel lit encore l'ancienne version.
      Un redéploiement régénère ce cache.<br><br>
      <strong>Si « sendmail present » indique NON :</strong> le binaire n'est pas à ce
      chemin sur l'hébergement. Demandez le bon chemin au support, ou utilisez le SMTP
      du serveur (mail.votre-domaine.com, port 465).
    </div>
  </div>
</div>
</body>
</html>

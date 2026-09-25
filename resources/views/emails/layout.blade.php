<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="color-scheme" content="light only"/>
<title>@yield('sujet', 'Paradisia')</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Helvetica Neue',Arial,sans-serif;background:#eef3f0;color:#18211b;-webkit-font-smoothing:antialiased}
  img{border:0;line-height:100%;outline:none;text-decoration:none}
  .wrap{max-width:580px;margin:26px auto;padding:0 14px}
  .card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(11,46,26,.12)}
  .head{background:linear-gradient(135deg,#0b2e1a,#14532d);padding:26px 24px 22px;text-align:center}
  .head img{height:48px;width:auto;display:inline-block}
  .head .brand{font-size:12px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#8fd7b4;margin-top:10px}
  .body{padding:30px 32px}
  .body h1{font-size:21px;font-weight:800;color:#14532d;margin-bottom:12px;line-height:1.3}
  .body p{font-size:14px;color:#4b5a51;line-height:1.75;margin-bottom:14px}
  .center{text-align:center}
  .btn{display:inline-block;background:linear-gradient(135deg,#059669,#0d9488);color:#fff!important;text-decoration:none;font-weight:800;font-size:15px;padding:14px 32px;border-radius:12px;margin:6px 0}
  .btn-o{background:#E8792B;background-image:none}
  .info-row{padding:11px 15px;background:#f2f9f5;border-radius:10px;margin-bottom:8px}
  .info-row .info-label{font-size:12px;color:#8aa294}
  .info-row .info-value{font-size:14px;font-weight:700;color:#18211b;margin-top:2px}
  .quote{background:#f2f9f5;border-left:3px solid #10b981;border-radius:8px;padding:12px 16px;margin:14px 0;font-size:14px;color:#374151;font-style:italic}
  .note{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-top:16px}
  .note p{font-size:13px;color:#1e40af;margin:0}
  .muted{font-size:12px;color:#9fb5a8;word-break:break-all}
  .foot{background:#0b2e1a;padding:22px 24px;text-align:center}
  .foot .l{color:#cfe8da;font-size:13px;line-height:2}
  .foot .l a{color:#8fd7b4;text-decoration:none}
  .foot .s{color:#6f9683;font-size:11px;margin-top:10px}
</style>
@stack('styles')
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="head">
      <img src="{{ rtrim(config('app.url'), '/') }}/logo.png" alt="Paradisia" />
      <div class="brand">Paradisia Africa</div>
    </div>
    <div class="body">
      @yield('contenu')
    </div>
    <div class="foot">
      <div class="l">
        <a href="https://www.paradisia-africa.com">www.paradisia-africa.com</a><br>
        +237 687 98 42 82<br>
        <a href="https://www.facebook.com/paradisia.juice">www.facebook.com/paradisia.juice</a>
      </div>
      <div class="s">© {{ date('Y') }} PARADISIA Africa — Jus naturels d'ananas</div>
    </div>
  </div>
</div>
</body>
</html>

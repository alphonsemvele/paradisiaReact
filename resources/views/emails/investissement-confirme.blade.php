<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<title>Investissement confirmé — PARADISIA</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Helvetica Neue',Arial,sans-serif;background:#f4f7f5;color:#18211b;-webkit-font-smoothing:antialiased}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(16,185,129,.12)}
  .header{background:linear-gradient(135deg,#059669,#0d9488);padding:36px 40px;text-align:center}
  .logo{font-size:24px;font-weight:800;color:#fff;letter-spacing:-.02em}
  .logo-sub{font-size:10px;color:rgba(255,255,255,.6);letter-spacing:.12em;text-transform:uppercase;margin-top:4px}
  .body{padding:36px 40px}
  h1{font-size:21px;font-weight:800;margin-bottom:10px}
  p{font-size:14px;color:#5b6b60;line-height:1.75;margin-bottom:14px}
  .info-row{display:flex;justify-content:space-between;padding:10px 14px;background:#f2f9f5;border-radius:10px;margin-bottom:8px}
  .info-label{font-size:12px;color:#8aa294}
  .info-value{font-size:12px;font-weight:700;color:#18211b}
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
    <p>Bonjour {{ $payment->customer_name ?? '' }} 👋</p>
    <h1>Votre investissement est confirmé</h1>
    <p>
      Nous avons bien reçu votre paiement via Malapay. Vos parts sont enregistrées
      et apparaissent désormais dans votre espace investisseur.
    </p>

    <div class="info-row"><span class="info-label">Parts acquises</span><span class="info-value">{{ $payment->share }}</span></div>
    <div class="info-row"><span class="info-label">Montant</span><span class="info-value">{{ number_format((float) $payment->total_amount, 0, ',', ' ') }} {{ $payment->currency }}</span></div>
    <div class="info-row"><span class="info-label">Moyen de paiement</span><span class="info-value">Portefeuille Malapay</span></div>
    <div class="info-row"><span class="info-label">Référence</span><span class="info-value">{{ $payment->ref }}</span></div>
    <div class="info-row"><span class="info-label">Date</span><span class="info-value">{{ $payment->created_at?->format('d/m/Y à H:i') }}</span></div>

    <p style="margin-top:20px">
      Merci de votre confiance. Une question ? Écrivez-nous sur WhatsApp au
      <strong>+237 687 98 42 82</strong>.
    </p>
  </div>
  <div class="footer">
    <p>PARADISIA Africa — Jus naturels d'ananas<br><a href="https://paradisia-africa.com">paradisia-africa.com</a></p>
  </div>
</div>
</body>
</html>

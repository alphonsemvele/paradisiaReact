<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; color:#1a2b20; font-size:12px; }
  .head { background:#0d9268; color:#fff; padding:24px 30px; }
  .brand { font-size:22px; font-weight:bold; letter-spacing:1px; }
  .brand small { display:block; font-size:9px; letter-spacing:3px; opacity:.8; margin-top:2px; }
  .facture { text-align:right; margin-top:-42px; }
  .facture .t { font-size:16px; font-weight:bold; }
  .facture .code { font-size:11px; opacity:.9; margin-top:3px; }
  .wrap { padding:26px 30px; }
  .row { width:100%; }
  .col { width:49%; display:inline-block; vertical-align:top; }
  .lbl { color:#8aa294; font-size:10px; text-transform:uppercase; letter-spacing:.5px; }
  .val { font-size:12px; font-weight:bold; margin-bottom:10px; }
  table { width:100%; border-collapse:collapse; margin-top:18px; }
  th { background:#f0f7f3; text-align:left; padding:9px 10px; font-size:10px; text-transform:uppercase; color:#5b7566; }
  th.r, td.r { text-align:right; }
  td { padding:9px 10px; border-bottom:1px solid #eef3f0; }
  .totaux { margin-top:16px; width:100%; }
  .totaux td { padding:6px 10px; border:0; }
  .totaux .grand { font-size:15px; font-weight:bold; color:#0d9268; border-top:2px solid #0d9268; }
  .badge { display:inline-block; background:#fff7ed; color:#c2410c; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:bold; }
  .foot { margin-top:30px; padding-top:14px; border-top:1px solid #eef3f0; color:#8aa294; font-size:10px; text-align:center; line-height:1.7; }
</style>
</head>
<body>
  <div class="head">
    <div class="brand">PARADISIA <small>AFRICA</small></div>
    <div class="facture">
      <div class="t">FACTURE</div>
      <div class="code">{{ $sale->ref }}</div>
    </div>
  </div>

  <div class="wrap">
    <div class="row">
      <div class="col">
        <div class="lbl">Facturé à</div>
        <div class="val">{{ $sale->customer_name }}</div>
        <div class="lbl">Téléphone</div>
        <div class="val">{{ $sale->customer_phone }}</div>
      </div>
      <div class="col">
        <div class="lbl">Date</div>
        <div class="val">{{ $sale->sale_date?->format('d/m/Y à H:i') }}</div>
        <div class="lbl">Lieu de livraison</div>
        <div class="val">{{ $sale->delivery_location }}</div>
        <div class="lbl">Statut</div>
        <div class="val"><span class="badge">En attente — un agent vous contacte</span></div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Produit</th>
          <th class="r">Qté</th>
          <th class="r">Prix unitaire</th>
          <th class="r">Sous-total</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sale->items as $item)
          <tr>
            <td>{{ $item->product_name }}</td>
            <td class="r">{{ $item->quantity }}</td>
            <td class="r">{{ number_format((float) $item->unit_price, 0, ',', ' ') }}</td>
            <td class="r">{{ number_format((float) $item->subtotal, 0, ',', ' ') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <table class="totaux">
      <tr>
        <td class="r" style="width:75%; color:#5b7566;">Sous-total produits</td>
        <td class="r">{{ number_format((float) $sale->subtotal, 0, ',', ' ') }} FCFA</td>
      </tr>
      <tr>
        <td class="r" style="color:#5b7566;">Frais de livraison</td>
        <td class="r">{{ number_format((float) $frais_livraison, 0, ',', ' ') }} FCFA</td>
      </tr>
      <tr>
        <td class="r grand">TOTAL</td>
        <td class="r grand">{{ number_format((float) $sale->total, 0, ',', ' ') }} FCFA</td>
      </tr>
    </table>

    @if ($sale->notes)
      <div style="margin-top:16px;"><span class="lbl">Note</span><div class="val">{{ $sale->notes }}</div></div>
    @endif

    <div class="foot">
      Merci pour votre commande chez PARADISIA Africa.<br>
      Code de facture : <strong>{{ $sale->ref }}</strong> — conservez-le pour tout suivi.<br>
      WhatsApp : +237 687 98 42 82 · paradisia-africa.com
    </div>
  </div>
</body>
</html>

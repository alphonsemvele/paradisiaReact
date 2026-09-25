@extends('emails.layout')
@section('sujet', 'Votre investissement est confirmé')
@section('contenu')
  <p>Bonjour {{ $payment->customer_name ?? '' }} 👋</p>
  <h1>Votre investissement est confirmé</h1>
  <p>Nous avons bien reçu votre paiement. Vos parts sont enregistrées et apparaissent désormais dans votre espace investisseur.</p>

  <div class="info-row"><div class="info-label">Parts acquises</div><div class="info-value">{{ $payment->share }}</div></div>
  <div class="info-row"><div class="info-label">Montant</div><div class="info-value">{{ number_format((float) $payment->total_amount, 0, ',', ' ') }} {{ $payment->currency }}</div></div>
  <div class="info-row"><div class="info-label">Référence</div><div class="info-value">{{ $payment->ref }}</div></div>
  <div class="info-row"><div class="info-label">Date</div><div class="info-value">{{ $payment->created_at?->format('d/m/Y à H:i') }}</div></div>

  <p style="margin-top:18px">Merci de votre confiance 💚</p>
@endsection

@extends('emails.layout')
@section('sujet', 'Votre ticket Paradisia Festy')
@push('styles')
<style>
  .ticket{border-radius:16px;overflow:hidden;margin-top:6px}
  .ticket-top{padding:22px 24px;background:linear-gradient(135deg,#0b2e1a,#14532d)}
  .kind{display:inline-block;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#0b2e1a;padding:5px 12px;border-radius:999px}
  .festy{margin-top:12px;font-size:22px;font-weight:800;color:#fff}
  .festy small{display:block;font-size:12px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.1em;text-transform:uppercase;margin-top:4px}
  .ticket-body{background:#fff;padding:20px 24px;border:1px solid #e6f2ea;border-top:none;border-radius:0 0 16px 16px}
  .trow{padding:9px 0;border-bottom:1px solid #f0f4f1}
  .trow:last-child{border-bottom:none}
  .tlbl{font-size:11px;color:#9fb5a8;text-transform:uppercase;letter-spacing:.05em}
  .tval{font-size:15px;font-weight:700;color:#18211b;margin-top:2px}
  .code-box{margin-top:16px;text-align:center;background:#f2f9f5;border:1px dashed #14532d;border-radius:14px;padding:16px}
  .code-box .tlbl{margin-bottom:6px}
  .code{font-size:26px;font-weight:800;letter-spacing:.18em;color:#14532d;font-family:'Courier New',monospace}
</style>
@endpush
@section('contenu')
<?php
    $couleur = $ticket->team?->couleur ?: '#F5B301';
    $equipe = $ticket->team?->nom;
    $estFan = $ticket->type === 'fan';
    $titulaire = $ticket->user?->name ?: 'Festyvalier';
?>
  <h1>Ton ticket est confirmé 🎟️</h1>
  <p>Bonjour {{ $titulaire }}, voici ton ticket officiel. Présente le code ci-dessous le jour J.</p>

  <div class="ticket">
    <div class="ticket-top">
      <span class="kind" style="background:{{ $couleur }}">Ticket {{ $estFan ? 'Fan' : 'Participant' }}</span>
      <div class="festy">PARADISIA FESTY<small>{{ $ticket->team ? 'Équipe '.$equipe : 'Le grand challenge' }}</small></div>
    </div>
    <div class="ticket-body">
      <div class="trow"><div class="tlbl">Titulaire</div><div class="tval">{{ $titulaire }}</div></div>
      @if($ticket->team)
        <div class="trow"><div class="tlbl">Équipe</div><div class="tval" style="color:{{ $couleur }}">{{ $equipe }}</div></div>
      @endif
      <div class="trow"><div class="tlbl">Formule</div><div class="tval">{{ $estFan ? 'Fan' : 'Participant' }}</div></div>
      <div class="trow"><div class="tlbl">Montant payé</div><div class="tval">{{ number_format((float) $ticket->montant, 0, ',', ' ') }} FCFA @if($ticket->promo)<span style="color:#E8792B;font-size:12px"> · promo</span>@endif</div></div>
      <div class="trow"><div class="tlbl">Date</div><div class="tval">{{ $ticket->paid_at?->format('d/m/Y à H:i') ?? $ticket->created_at?->format('d/m/Y à H:i') }}</div></div>

      <div class="code-box">
        <div class="tlbl">Code du ticket</div>
        <div class="code">{{ $ticket->code_ticket }}</div>
      </div>

      @if($ticket->team?->whatsapp_group)
        <p class="center" style="margin-top:16px"><a class="btn" href="{{ $ticket->team->whatsapp_group }}">Rejoindre le groupe de l'équipe {{ $equipe }}</a></p>
      @endif
    </div>
  </div>
@endsection

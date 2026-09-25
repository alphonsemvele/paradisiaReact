@extends('emails.layout')
@section('sujet', 'Inscription confirmée')
@section('contenu')
  <p>Bonjour{{ $inscription->nom ? ' '.$inscription->nom : '' }} 👋</p>
  <h1>Votre inscription est confirmée</h1>
  <p>Merci de vous être inscrit à <strong>{{ $event->titre }}</strong>. Voici le récapitulatif :</p>

  <div class="info-row"><div class="info-label">Événement</div><div class="info-value">{{ $event->titre }}</div></div>
  <div class="info-row"><div class="info-label">Date</div><div class="info-value">{{ $event->date_debut->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}</div></div>
  <div class="info-row"><div class="info-label">Format</div><div class="info-value">{{ $event->modeLabel() }}</div></div>
  @if ($inscription->profil)
    <div class="info-row"><div class="info-label">Profil</div><div class="info-value">{{ $inscription->profilLabel() }}</div></div>
  @endif
  @if ($inscription->pays)
    <div class="info-row"><div class="info-label">Pays</div><div class="info-value">{{ $inscription->pays }}</div></div>
  @endif

  <div class="note">
    <p>@if ($event->message_confirmation){{ $event->message_confirmation }}@else Le lien de la réunion en ligne vous sera envoyé par e-mail le moment venu. Gardez un œil sur votre boîte de réception.@endif</p>
  </div>
@endsection

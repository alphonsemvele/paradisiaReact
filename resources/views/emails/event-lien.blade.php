@extends('emails.layout')
@section('sujet', 'Lien de connexion')
@section('contenu')
  <p>Bonjour{{ $inscription->nom ? ' '.$inscription->nom : '' }} 👋</p>
  <h1>C'est bientôt l'heure !</h1>
  <p>Voici votre lien pour rejoindre <strong>{{ $event->titre }}</strong>.</p>

  <div class="info-row"><div class="info-label">Date</div><div class="info-value">{{ $event->date_debut->isoFormat('dddd D MMMM [à] HH:mm') }}</div></div>
  <div class="info-row"><div class="info-label">Format</div><div class="info-value">{{ $event->modeLabel() }}</div></div>

  <p class="center"><a href="{{ $event->lien_reunion }}" class="btn btn-o">Rejoindre la réunion</a></p>
  <p class="muted center">Si le bouton ne s'ouvre pas : {{ $event->lien_reunion }}</p>
@endsection

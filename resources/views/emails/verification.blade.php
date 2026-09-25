@extends('emails.layout')
@section('sujet', 'Confirme ton compte Paradisia')
@section('contenu')
  <h1>Bienvenue sur Paradisia ! 🍍</h1>
  <p>Bonjour{{ $user?->name ? ' '.$user->name : '' }}, merci pour ton inscription. Ton compte est <strong>déjà actif</strong> — confirme simplement ton adresse e-mail pour la sécuriser.</p>
  <p class="center"><a class="btn btn-o" href="{{ $url }}">Confirmer mon compte</a></p>
  <p>Ce lien est valable quelques jours. Si tu n'es pas à l'origine de cette inscription, ignore cet e-mail en toute sécurité.</p>
@endsection

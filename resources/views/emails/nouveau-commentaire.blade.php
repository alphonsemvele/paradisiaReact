@extends('emails.layout')
@section('sujet', 'Nouveau commentaire')
@section('contenu')
  <p>Bonjour {{ $destinataire->name }},</p>
  <h1>💬 {{ $auteurCommentaire->name }} a commenté votre publication</h1>
  <div class="quote">{{ \Illuminate\Support\Str::limit($commentaire->body, 300) }}</div>
  <p class="center"><a class="btn" href="{{ rtrim(config('app.url'), '/') }}/p/{{ $publication->id }}">Voir la publication</a></p>
@endsection

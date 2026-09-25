@extends('emails.layout')
@section('sujet', $sujet)
@section('contenu')
  <div style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#8aa294;margin-bottom:8px">Notification administration</div>
  <div style="font-size:14px;color:#2b3a30;line-height:1.75;white-space:pre-line">{{ $contenu }}</div>
  @if(!empty($lien))
    <p class="center" style="margin-top:18px"><a class="btn" href="{{ $lien }}">Voir / traiter dans l'administration →</a></p>
  @endif
@endsection

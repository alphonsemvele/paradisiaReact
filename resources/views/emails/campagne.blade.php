@extends('emails.layout')
@section('sujet', $campagne->sujet)
@section('contenu')
  <div style="font-size:15px;color:#2b3a30;line-height:1.75">{!! nl2br(e($campagne->contenu)) !!}</div>
@endsection

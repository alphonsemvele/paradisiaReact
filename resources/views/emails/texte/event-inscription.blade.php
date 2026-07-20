Bonjour{{ $inscription->nom ? ' '.$inscription->nom : '' }},

VOTRE INSCRIPTION EST CONFIRMÉE

Merci de vous être inscrit à « {{ $event->titre }} ».

Événement : {{ $event->titre }}
Date : {{ $event->date_debut->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
Format : {{ $event->modeLabel() }}
@if ($inscription->profil)Profil : {{ $inscription->profilLabel() }}
@endif
@if ($inscription->pays)Pays : {{ $inscription->pays }}
@endif

@if ($event->message_confirmation){{ $event->message_confirmation }}@else
Le lien de la réunion en ligne vous sera envoyé par e-mail le moment venu.
@endif

Une question ? WhatsApp : +237 687 98 42 82

--
PARADISIA Africa
https://paradisia-africa.com

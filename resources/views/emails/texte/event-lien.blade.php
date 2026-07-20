Bonjour{{ $inscription->nom ? ' '.$inscription->nom : '' }},

C'EST BIENTÔT L'HEURE !

Voici votre lien pour rejoindre « {{ $event->titre }} ».

Date : {{ $event->date_debut->isoFormat('dddd D MMMM [à] HH:mm') }}
Format : {{ $event->modeLabel() }}

Rejoindre la réunion :
{{ $event->lien_reunion }}

--
PARADISIA Africa
https://paradisia-africa.com

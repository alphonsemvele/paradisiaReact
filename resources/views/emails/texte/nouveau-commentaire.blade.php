Bonjour {{ $destinataire->name }},

{{ $auteurCommentaire->name }} a commenté votre publication :

« {{ \Illuminate\Support\Str::limit($commentaire->body, 300) }} »

Voir la publication : https://paradisia-africa.com/p/{{ $publication->id }}

--
PARADISIA Africa
https://paradisia-africa.com

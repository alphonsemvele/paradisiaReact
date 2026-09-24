PARADISIA FESTY — Votre ticket

Bonjour {{ $ticket->user?->name ?: 'Festyvalier' }},

Votre paiement est confirmé. Voici votre ticket officiel :

- Formule : {{ $ticket->type === 'fan' ? 'Fan' : 'Participant' }}
@if($ticket->team)
- Équipe : {{ $ticket->team->nom }}
@endif
- Montant payé : {{ number_format((float) $ticket->montant, 0, ',', ' ') }} FCFA
- Date : {{ $ticket->paid_at?->format('d/m/Y à H:i') ?? $ticket->created_at?->format('d/m/Y à H:i') }}

CODE DU TICKET : {{ $ticket->code_ticket }}

Présentez ce code le jour de l'événement.
@if($ticket->team)

Retrouvez le groupe WhatsApp de votre équipe depuis votre espace Paradisia.
@endif

Une question ? Contactez-nous au +237 687 98 42 82
PARADISIA Africa

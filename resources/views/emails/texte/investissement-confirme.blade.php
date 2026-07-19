Bonjour {{ $payment->customer_name ?? '' }},

Votre investissement est confirmé.

Nous avons bien reçu votre paiement via Malapay. Vos parts sont enregistrées
et apparaissent dans votre espace investisseur.

Parts acquises : {{ $payment->share }}
Montant : {{ number_format((float) $payment->total_amount, 0, ',', ' ') }} {{ $payment->currency }}
Moyen de paiement : portefeuille Malapay
Référence : {{ $payment->ref }}
Date : {{ $payment->created_at?->format('d/m/Y à H:i') }}

Merci de votre confiance.
Une question ? WhatsApp : +237 687 98 42 82

--
PARADISIA Africa — Jus naturels d'ananas
https://paradisia-africa.com

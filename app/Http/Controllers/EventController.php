<?php

namespace App\Http\Controllers;

use App\Mail\EventInscriptionMail;
use App\Models\Country;
use App\Models\Event;
use App\Support\PageMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Événements Paradisia côté public : liste, détail et inscription.
 */
class EventController extends Controller
{
    public function index(): Response
    {
        $events = Event::where('statut', 'publie')
            ->orderBy('date_debut')
            ->get()
            ->map(fn (Event $e) => $this->carte($e));

        return Inertia::render('events/index', [
            'events' => $events,
        ]);
    }

    public function show(Event $event): Response
    {
        abort_if($event->statut !== 'publie', 404);

        $event->load('images');
        $formatted = $this->detail($event);

        return Inertia::render('events/show', [
            'event' => $formatted,
            // Pays proposés seulement si l'événement les demande
            'pays' => $event->collecte_pays ? $this->pays() : [],
        ])->withViewData([
            'meta' => PageMeta::make(
                title: $event->titre,
                description: $event->description,
                image: $formatted['image'],
                type: 'article',
            ),
        ]);
    }

    public function register(Request $request, Event $event): RedirectResponse
    {
        abort_if($event->statut !== 'publie', 404);

        if (! $event->accepteInscriptions()) {
            return back()->withErrors(['email' => 'Les inscriptions à cet événement sont closes.']);
        }

        // Les règles s'adaptent à ce que l'événement demande.
        $regles = ['email' => ['required', 'email', 'max:180']];

        if ($event->collecte_nom) {
            $regles['nom'] = ['required', 'string', 'max:160'];
        }
        if ($event->collecte_pays) {
            $regles['pays'] = ['required', 'string', 'max:100'];
        }
        if ($event->collecte_telephone) {
            $regles['telephone'] = ['nullable', 'string', 'max:40'];
        }
        if ($event->collecte_profil) {
            $regles['profil'] = ['required', 'in:investisseur,participant'];
        }

        $validated = $request->validate($regles);

        // Une même adresse ne s'inscrit qu'une fois : on renvoie un message
        // clair plutôt qu'une erreur de contrainte unique.
        $existe = $event->registrations()->where('email', $validated['email'])->exists();

        if ($existe) {
            return back()->with('info', 'Vous êtes déjà inscrit à cet événement. Un e-mail de confirmation vous a été envoyé.');
        }

        $inscription = $event->registrations()->create([
            'email' => $validated['email'],
            'nom' => $validated['nom'] ?? null,
            'pays' => $validated['pays'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'profil' => $validated['profil'] ?? null,
            'ip' => $request->ip(),
        ]);

        // La confirmation ne doit jamais faire échouer l'inscription.
        try {
            Mail::to($inscription->email)->send(new EventInscriptionMail($event, $inscription));
        } catch (\Throwable $e) {
            Log::error("Confirmation d'inscription à l'événement {$event->id} non envoyée : ".$e->getMessage());
        }

        return back()->with('success', 'Inscription confirmée ! Un e-mail vient de vous être envoyé.');
    }

    /* ═══════════════════════ Interne ═══════════════════════ */

    /** Carte compacte (accueil, liste). */
    private function carte(Event $e): array
    {
        return [
            'id' => $e->id,
            'titre' => $e->titre,
            'type' => $e->type,
            'mode' => $e->mode,
            'mode_label' => $e->modeLabel(),
            'lieu' => $e->lieu,
            'date_debut' => $e->date_debut->toIso8601String(),
            'date_label' => $e->date_debut->isoFormat('D MMM YYYY [à] HH:mm'),
            'date_courte' => $e->date_debut->isoFormat('D MMM'),
            'passe' => $e->estPasse(),
            'image' => $this->mediaUrl($e->image),
            'extrait' => $e->description ? \Illuminate\Support\Str::limit($e->description, 120) : null,
            'inscriptions_ouvertes' => $e->accepteInscriptions(),
        ];
    }

    /** Détail complet + configuration du formulaire. */
    private function detail(Event $e): array
    {
        // Galerie : couverture d'abord, puis les images additionnelles
        $galerie = collect([$this->mediaUrl($e->image)])
            ->concat($e->images->map(fn ($img) => $this->mediaUrl($img->path)))
            ->filter()
            ->unique()
            ->values();

        return $this->carte($e) + [
            'description' => $e->description,
            'images_galerie' => $galerie,
            'image' => $galerie->first(),
            'date_fin_label' => $e->date_fin?->isoFormat('D MMMM YYYY [à] HH:mm'),
            'document' => $this->mediaUrl($e->document),
            'document_nom' => $e->document ? basename($e->document) : null,
            'collecte_pays' => $e->collecte_pays,
            'collecte_profil' => $e->collecte_profil,
            'collecte_telephone' => $e->collecte_telephone,
            'collecte_nom' => $e->collecte_nom,
            'places_max' => $e->places_max,
            'places_restantes' => $e->places_max !== null
                ? max(0, $e->places_max - $e->registrations()->count())
                : null,
        ];
    }

    /** Liste des pays pour le sélecteur d'inscription. */
    private function pays(): array
    {
        return Country::orderBy('name')
            ->get(['name', 'sortname'])
            ->map(fn ($p) => ['code' => $p->sortname, 'nom' => $p->name])
            ->all();
    }
}

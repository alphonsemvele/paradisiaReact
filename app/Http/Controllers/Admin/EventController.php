<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EventLienReunionMail;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion des événements et de leurs inscrits.
 */
class EventController extends Controller
{
    public function index(): Response
    {
        $events = Event::withCount('registrations')
            ->orderByDesc('date_debut')
            ->get()
            ->map(fn (Event $e) => [
                'id' => $e->id,
                'titre' => $e->titre,
                'type' => $e->type,
                'mode_label' => $e->modeLabel(),
                'date_label' => $e->date_debut->isoFormat('D MMM YYYY [à] HH:mm'),
                'statut' => $e->statut,
                'passe' => $e->estPasse(),
                'inscriptions_ouvertes' => $e->inscriptions_ouvertes,
                'inscrits' => $e->registrations_count,
                'image' => $this->mediaUrl($e->image),
            ]);

        return Inertia::render('admin/events/index', [
            'events' => $events,
            'stats' => [
                'total' => Event::count(),
                'publies' => Event::where('statut', 'publie')->count(),
                'a_venir' => Event::where('date_debut', '>=', now())->count(),
                'inscrits' => \App\Models\EventRegistration::count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/events/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->valider($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadPublicFile($request->file('image'), 'uploads/events', 'evt');
        }
        if ($request->hasFile('document')) {
            $data['document'] = $this->uploadPublicFile($request->file('document'), 'uploads/events', 'doc');
        }

        Event::create($data);

        return redirect()->route('admin.events.index')->with('success', 'Événement créé.');
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('admin/events/edit', [
            'event' => [
                'id' => $event->id,
                'titre' => $event->titre,
                'description' => $event->description,
                'type' => $event->type,
                'mode' => $event->mode,
                'lieu' => $event->lieu,
                'date_debut' => $event->date_debut->format('Y-m-d\TH:i'),
                'date_fin' => $event->date_fin?->format('Y-m-d\TH:i'),
                'collecte_pays' => $event->collecte_pays,
                'collecte_profil' => $event->collecte_profil,
                'collecte_telephone' => $event->collecte_telephone,
                'collecte_nom' => $event->collecte_nom,
                'message_confirmation' => $event->message_confirmation,
                'lien_reunion' => $event->lien_reunion,
                'statut' => $event->statut,
                'inscriptions_ouvertes' => $event->inscriptions_ouvertes,
                'places_max' => $event->places_max,
                'image' => $this->mediaUrl($event->image),
                'document' => $this->mediaUrl($event->document),
                'document_nom' => $event->document ? basename($event->document) : null,
            ],
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->valider($request) + [
            'remove_document' => $request->boolean('remove_document'),
        ];

        if ($request->hasFile('image')) {
            $this->deletePublicFile($event->image);
            $data['image'] = $this->uploadPublicFile($request->file('image'), 'uploads/events', 'evt');
        }

        if ($request->boolean('remove_document') && $event->document) {
            $this->deletePublicFile($event->document);
            $data['document'] = null;
        }
        if ($request->hasFile('document')) {
            $this->deletePublicFile($event->document);
            $data['document'] = $this->uploadPublicFile($request->file('document'), 'uploads/events', 'doc');
        }

        unset($data['remove_document']);
        $event->update($data);

        return redirect()->route('admin.events.index')->with('success', 'Événement mis à jour.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->deletePublicFile($event->image);
        $this->deletePublicFile($event->document);
        $event->delete();

        return back()->with('success', 'Événement supprimé.');
    }

    /** Liste des inscrits, filtrable par profil. */
    public function registrations(Request $request, Event $event): Response
    {
        $profil = $request->get('profil');

        $query = $event->registrations()->latest();

        if (in_array($profil, ['investisseur', 'participant'], true)) {
            $query->where('profil', $profil);
        }

        $inscrits = $query->get()->map(fn ($r) => [
            'id' => $r->id,
            'email' => $r->email,
            'nom' => $r->nom,
            'pays' => $r->pays,
            'telephone' => $r->telephone,
            'profil' => $r->profil,
            'profil_label' => $r->profilLabel(),
            'lien_envoye' => $r->lien_envoye,
            'date' => $r->created_at->isoFormat('D MMM YYYY [à] HH:mm'),
        ]);

        return Inertia::render('admin/events/registrations', [
            'event' => [
                'id' => $event->id,
                'titre' => $event->titre,
                'date_label' => $event->date_debut->isoFormat('D MMMM YYYY [à] HH:mm'),
                'lien_reunion' => $event->lien_reunion,
                'collecte_profil' => $event->collecte_profil,
            ],
            'inscrits' => $inscrits,
            'filtre' => $profil,
            'compteurs' => [
                'total' => $event->registrations()->count(),
                'investisseurs' => $event->registrations()->where('profil', 'investisseur')->count(),
                'participants' => $event->registrations()->where('profil', 'participant')->count(),
                'lien_envoye' => $event->registrations()->where('lien_envoye', true)->count(),
            ],
        ]);
    }

    /**
     * Envoie le lien de réunion aux inscrits qui ne l'ont pas encore reçu.
     * C'est l'action « le moment venu » annoncée dans l'e-mail de confirmation.
     */
    public function envoyerLien(Request $request, Event $event): RedirectResponse
    {
        if (! $event->lien_reunion) {
            return back()->withErrors(['lien' => 'Renseignez d\'abord le lien de réunion dans l\'événement.']);
        }

        $destinataires = $event->registrations()->where('lien_envoye', false)->get();
        $envoyes = 0;

        foreach ($destinataires as $inscription) {
            try {
                Mail::to($inscription->email)->send(new EventLienReunionMail($event, $inscription));
                $inscription->update(['lien_envoye' => true, 'lien_envoye_at' => now()]);
                $envoyes++;
            } catch (\Throwable $e) {
                Log::error("Lien de réunion non envoyé à {$inscription->email} : ".$e->getMessage());
            }
        }

        return back()->with('success', "Lien envoyé à {$envoyes} inscrit(s).");
    }

    /* ═══════════════════════ Interne ═══════════════════════ */

    private function valider(Request $request): array
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:8000'],
            'type' => ['required', 'string', 'max:40'],
            'mode' => ['required', 'in:en_ligne,presentiel,hybride'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'collecte_pays' => ['boolean'],
            'collecte_profil' => ['boolean'],
            'collecte_telephone' => ['boolean'],
            'collecte_nom' => ['boolean'],
            'message_confirmation' => ['nullable', 'string', 'max:2000'],
            'lien_reunion' => ['nullable', 'string', 'max:500'],
            'statut' => ['required', 'in:brouillon,publie,termine'],
            'inscriptions_ouvertes' => ['boolean'],
            'places_max' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:10240'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
        ]);

        // Champs de fichiers gérés à part
        unset($validated['image'], $validated['document']);

        return $validated;
    }
}

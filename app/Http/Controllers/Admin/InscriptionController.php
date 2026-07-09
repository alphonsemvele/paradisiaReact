<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $typeFilter = $request->get('type');

        $query = Inscription::with('formation');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if (in_array($typeFilter, ['acceleree', 'normale'], true)) {
            $query->where('type', $typeFilter);
        }

        $inscriptions = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $inscriptions->getCollection()->transform(function ($i) {
            return [
                'id'               => $i->id,
                'nom'              => $i->nom,
                'prenom'           => $i->prenom,
                'telephone'        => $i->telephone,
                'type'             => $i->type,
                'type_label'       => $i->type_label,
                'statut'           => $i->statut,
                'formation'        => $i->formation?->titre ?? '—',
                'created_at_date'  => $i->created_at->format('d/m/Y H:i'),
            ];
        });

        $stats = [
            'total'      => Inscription::count(),
            'acceleree'  => Inscription::where('type', 'acceleree')->count(),
            'normale'    => Inscription::where('type', 'normale')->count(),
            'en_attente' => Inscription::where('statut', 'en_attente')->count(),
        ];

        return Inertia::render('admin/inscriptions/index', [
            'inscriptions' => $inscriptions,
            'stats'        => $stats,
            'filters'      => ['search' => $search, 'type' => $typeFilter],
        ]);
    }

    public function updateStatus(Request $request, Inscription $inscription): RedirectResponse
    {
        $validated = $request->validate([
            'statut' => 'required|in:en_attente,confirme,annule',
        ]);

        $inscription->update($validated);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Inscription $inscription): RedirectResponse
    {
        $inscription->delete();

        return back()->with('success', 'Inscription supprimée.');
    }
}

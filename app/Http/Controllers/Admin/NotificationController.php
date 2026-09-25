<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Gère la liste des e-mails qui reçoivent les notifications admin. */
class NotificationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/reglages/notifications', [
            'destinataires' => NotificationRecipient::orderBy('email')->get(['id', 'email', 'actif'])
                ->map(fn (NotificationRecipient $r) => [
                    'id' => $r->id,
                    'email' => $r->email,
                    'actif' => $r->actif,
                ]),
            'defaut' => config('services.superadmin_email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ], [
            'email.email' => 'Adresse e-mail invalide.',
        ]);

        NotificationRecipient::firstOrCreate(
            ['email' => strtolower(trim($data['email']))],
            ['actif' => true],
        );

        return back()->with('success', 'Destinataire ajouté.');
    }

    public function toggle(NotificationRecipient $recipient): RedirectResponse
    {
        $recipient->update(['actif' => ! $recipient->actif]);

        return back()->with('success', $recipient->actif ? 'Destinataire activé.' : 'Destinataire désactivé.');
    }

    public function destroy(NotificationRecipient $recipient): RedirectResponse
    {
        $recipient->delete();

        return back()->with('success', 'Destinataire retiré.');
    }
}

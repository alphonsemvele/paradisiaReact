<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class RegisteredUserController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('auth/register', [
            'countries' => Country::orderBy('name')
                ->get(['id', 'name', 'sortname', 'phoneCode']),
            // Code de parrainage transmis par le lien (?ref=REF_xxxx).
            'parrain' => $request->query('ref'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sexe' => 'required|in:H,F',
            'id_country' => 'required|exists:countries,id',
            'phone' => 'required|string|max:20',
            'parrain' => ['nullable', 'string', 'max:40'],
        ]);

        // Parrainage : on relie le nouveau compte à son parrain via son code.
        $parrain = ! empty($validated['parrain'])
            ? User::where('ref', trim($validated['parrain']))->first()
            : null;

        // Récupérer le pays pour remplir country et country_code automatiquement
        $country = Country::findOrFail($validated['id_country']);

        // Validation serveur du téléphone selon la numérotation du pays, puis
        // normalisation au format international E.164 (ex. +237651234567).
        [$telephone, $nsn] = $this->telephoneValide($validated['phone'], $country->sortname, $country->name);

        // Un même numéro ne peut créer qu'un seul compte. On compare la forme
        // E.164 exacte ET le numéro national « en chiffres » (sans espaces ni
        // tirets), pour attraper aussi les anciens comptes non normalisés.
        $chiffres = "REPLACE(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'.',''),'+','')";
        $existe = User::where('phone', $telephone)
            ->orWhereRaw("$chiffres LIKE ?", ['%'.$nsn])
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'phone' => 'Un compte existe déjà avec ce numéro de téléphone.',
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'sexe' => $validated['sexe'],
            'phone' => $telephone,
            'id_country' => $country->id,
            'country' => $country->name,
            'country_code' => $country->phoneCode,
            'ref' => User::genererRef(),                 // code de parrainage du nouveau
            'id_father' => $parrain?->id,                // son parrain (le cas échéant)
            'referral_code' => $parrain?->ref,           // code du parrain utilisé
        ]);

        // E-mail de confirmation (le compte n'est pas connectable tant qu'il
        // n'est pas confirmé). Un échec d'envoi ne bloque pas l'inscription.
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            logger()->error('Mail de vérification non envoyé : '.$e->getMessage());
        }

        // Pas de connexion automatique : confirmation e-mail requise d'abord.
        return redirect()->route('login')->with('status', 'Compte créé ! Confirme ton adresse e-mail (pense à vérifier tes spams) pour pouvoir te connecter.');
    }

    /**
     * Vérifie que le numéro correspond bien à la numérotation du pays choisi.
     * Renvoie [E.164, numéro national (NSN)]. Lève une erreur sinon.
     */
    private function telephoneValide(string $phone, ?string $iso, string $paysNom): array
    {
        $iso = strtoupper((string) $iso);
        $util = PhoneNumberUtil::getInstance();

        try {
            $proto = $util->parse($phone, $iso);
        } catch (NumberParseException) {
            throw ValidationException::withMessages([
                'phone' => "Le numéro de téléphone n'est pas valide pour {$paysNom}.",
            ]);
        }

        if (! $util->isValidNumberForRegion($proto, $iso)) {
            throw ValidationException::withMessages([
                'phone' => "Ce numéro n'est pas un numéro {$paysNom} valide. Vérifiez le pays et le numéro.",
            ]);
        }

        return [
            $util->format($proto, PhoneNumberFormat::E164),
            $util->getNationalSignificantNumber($proto),
        ];
    }
}
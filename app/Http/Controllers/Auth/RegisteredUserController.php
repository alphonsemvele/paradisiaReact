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
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/register', [
            'countries' => Country::orderBy('name')
                ->get(['id', 'name', 'sortname', 'phoneCode']),
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
        ]);

        // Récupérer le pays pour remplir country et country_code automatiquement
        $country = Country::findOrFail($validated['id_country']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'sexe' => $validated['sexe'],
            'phone' => $validated['phone'],
            'id_country' => $country->id,
            'country' => $country->name,
            'country_code' => $country->phoneCode,
        ]);

        event(new Registered($user));
        Auth::login($user);

        // Honore la destination mémorisée (ex. /festy) pour « suivre » l'utilisateur.
        return redirect()->intended('/');
    }
}
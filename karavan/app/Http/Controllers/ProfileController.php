<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Profil du membre : informations, mot de passe, suppression du compte. */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.edit')->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        // Erreurs rangées dans un « sac » séparé pour s'afficher sous le bon formulaire
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => "Le nouveau mot de passe doit être différent de l'actuel.",
        ]);

        $request->user()->update(['password' => $data['password']]);

        return redirect()->route('profile.edit')->with('success', 'Mot de passe modifié.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('deletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->isAdmin() && User::activeAdmins()->count() <= 1) {
            return back()->with('error', "Vous êtes le dernier administrateur : nommez-en un autre avant de supprimer votre compte.");
        }

        Auth::guard('web')->logout();
        $user->delete(); // trajets, logements et réservations supprimés en cascade
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Votre compte a été supprimé. Bonne route !');
    }
}

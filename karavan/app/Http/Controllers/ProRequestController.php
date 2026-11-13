<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ProRequestReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/** Un membre demande un profil organisateur ; les admins sont prévenus par email. */
class ProRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->canOrganize() || $user->hasPendingProRequest()) {
            return back()->with('info', $user->canOrganize() ? 'Vous avez déjà un profil organisateur.' : 'Votre demande est déjà en cours d\'examen.');
        }

        $data = $request->validate([
            'organization' => ['required', 'string', 'min:2', 'max:120'],
            'organization_website' => ['nullable', 'url', 'max:200'],
            'pro_request_message' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'organization' => 'le nom de la structure',
            'organization_website' => 'le site web',
            'pro_request_message' => 'le message',
        ]);

        $user->fill($data);
        $user->pro_requested_at = now();
        $user->save();

        Notification::send(User::activeAdmins()->get(), new ProRequestReceived($user));

        return redirect()->route('profile.edit')
            ->with('success', "Demande envoyée ! L'équipe Karavan l'examine et vous répond par email.");
    }
}

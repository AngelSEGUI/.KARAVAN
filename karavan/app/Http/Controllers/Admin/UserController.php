<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ProRequestApproved;
use App\Notifications\ProRequestDeclined;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Gestion des membres : rôle admin, suspension, suppression. */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->toString();
        $filter = $request->string('filtre')->toString();

        $users = User::query()
            ->withCount(['rides', 'housings', 'rideBookings', 'housingBookings'])
            ->when($q !== '', fn (Builder $b) => $b->where(fn (Builder $w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")))
            ->withCount('festivals')
            ->when($filter === 'admin', fn (Builder $b) => $b->where('role', UserRole::Admin))
            ->when($filter === 'organisateurs', fn (Builder $b) => $b->where('role', UserRole::Pro))
            ->when($filter === 'demandes-pro', fn (Builder $b) => $b->whereNotNull('pro_requested_at')->where('role', UserRole::Member))
            ->when($filter === 'suspendus', fn (Builder $b) => $b->whereNotNull('suspended_at'))
            ->latest()->orderByDesc('id')
            ->paginate(25)->withQueryString();

        $proRequests = User::whereNotNull('pro_requested_at')->where('role', UserRole::Member)->count();

        return view('admin.users.index', compact('users', 'q', 'filter', 'proRequests'));
    }

    /** Changer le rôle : membre, organisateur ou admin. */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        if ($response = $this->preventSelfAction($request, $user)) {
            return $response;
        }

        $role = UserRole::from($request->validate(['role' => ['required', Rule::enum(UserRole::class)]])['role']);

        $user->role = $role;
        if ($role !== UserRole::Member) {
            $user->suspended_at = null;
            $user->pro_requested_at = null;
        }
        $user->save();

        return back()->with('success', "{$user->name} est maintenant : " . mb_strtolower($role->label()) . '.');
    }

    /** Valider ou refuser une demande de profil organisateur (email envoyé au demandeur). */
    public function answerProRequest(Request $request, User $user, string $decision): RedirectResponse
    {
        if (! $user->hasPendingProRequest()) {
            return back()->with('error', "{$user->name} n'a pas de demande en attente.");
        }

        $user->pro_requested_at = null;
        if ($decision === 'valider') {
            $user->role = UserRole::Pro;
            $user->save();
            $user->notify(new ProRequestApproved($user));

            return back()->with('success', "{$user->name} est maintenant organisateur. Un email l'en a informé(e).");
        }

        $user->save();
        $user->notify(new ProRequestDeclined($user));

        return back()->with('info', "Demande de {$user->name} refusée. Un email l'en a informé(e).");
    }

    public function toggleSuspension(Request $request, User $user): RedirectResponse
    {
        if ($response = $this->preventSelfAction($request, $user)) {
            return $response;
        }

        $user->suspended_at = $user->isSuspended() ? null : now();
        $user->save();

        return back()->with('success', $user->isSuspended()
            ? "Le compte de {$user->name} est suspendu : il ne peut plus se connecter."
            : "Le compte de {$user->name} est réactivé.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($response = $this->preventSelfAction($request, $user)) {
            return $response;
        }

        $user->delete(); // trajets, logements et réservations supprimés en cascade

        return redirect()->route('admin.users.index')
            ->with('success', "Le compte de {$user->name} et toutes ses offres ont été supprimés.");
    }

    /** Un admin ne modifie pas son propre compte depuis cette page (pas de risque de se retirer ses droits). */
    private function preventSelfAction(Request $request, User $user): ?RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre compte depuis cette page.');
        }

        return null;
    }
}

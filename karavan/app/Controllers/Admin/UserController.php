<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;

/** Gestion des membres : rôle admin, suspension, suppression. */
final class UserController extends AdminController
{
    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $filter = (string) ($_GET['filtre'] ?? '');

        $this->render('users/index', [
            'title'  => 'Membres — Admin Karavan',
            'users'  => User::adminList($q, $filter),
            'q'      => $q,
            'filter' => $filter,
            'me'     => (int) $this->admin['id'],
        ]);
    }

    public function toggleRole(string $id): void
    {
        $this->guard();
        $user = $this->target($id);

        if ($user['role'] === 'admin') {
            User::update((int) $user['id'], ['role' => 'member']);
            flash('success', "{$user['name']} n'est plus administrateur.");
        } else {
            User::update((int) $user['id'], ['role' => 'admin', 'suspended' => 0]);
            flash('success', "{$user['name']} est maintenant administrateur.");
        }
        $this->back();
    }

    public function toggleSuspend(string $id): void
    {
        $this->guard();
        $user = $this->target($id);

        if ((int) $user['suspended'] === 1) {
            User::update((int) $user['id'], ['suspended' => 0]);
            flash('success', "Le compte de {$user['name']} est réactivé.");
        } else {
            User::update((int) $user['id'], ['suspended' => 1]);
            flash('success', "Le compte de {$user['name']} est suspendu : il ne peut plus se connecter.");
        }
        $this->back();
    }

    public function destroy(string $id): void
    {
        $this->guard();
        $user = $this->target($id);
        User::delete((int) $user['id']);
        flash('success', "Le compte de {$user['name']} et toutes ses offres ont été supprimés.");
        $this->redirect('/admin/membres');
    }

    /** Charge le membre visé et interdit les actions sur son propre compte. */
    private function target(string $id): array
    {
        $user = User::find((int) $id);
        if ($user === null) {
            $this->notFound();
            exit;
        }
        if ((int) $user['id'] === (int) $this->admin['id']) {
            flash('error', 'Vous ne pouvez pas modifier votre propre compte depuis cette page.');
            $this->back();
        }
        return $user;
    }
}

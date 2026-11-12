<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\User;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::user()) {
            $this->redirect('/mon-espace');
        }
        $this->render('auth/login', ['title' => 'Connexion — Karavan']);
        clear_input();
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $user = User::findByEmail((string) ($_POST['email'] ?? ''));

        if ($user === null || !password_verify((string) ($_POST['password'] ?? ''), $user['password'])) {
            with_input($_POST, ['email' => 'Email ou mot de passe incorrect.']);
            $this->redirect('/connexion');
        }

        if ((int) $user['suspended'] === 1) {
            with_input($_POST, ['email' => 'Ce compte est suspendu. Contactez l\'équipe Karavan.']);
            $this->redirect('/connexion');
        }

        Auth::login($user);
        clear_input();
        flash('success', 'Content de vous revoir, ' . explode(' ', $user['name'])[0] . ' !');

        $intended = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);
        if ($intended) {
            header('Location: ' . $intended);
            exit;
        }
        $this->redirect('/mon-espace');
    }

    public function registerForm(): void
    {
        if (Auth::user()) {
            $this->redirect('/mon-espace');
        }
        $this->render('auth/register', ['title' => 'Rejoindre la communauté — Karavan']);
        clear_input();
    }

    public function register(): void
    {
        $this->verifyCsrf();

        $v = new Validator($_POST, [
            'name'     => 'required|min:2|max:80',
            'email'    => 'required|email|max:160',
            'city'     => 'max:80',
            'password' => 'required|min:8',
        ], [
            'name'     => 'Le nom',
            'email'    => "L'email",
            'city'     => 'La ville',
            'password' => 'Le mot de passe',
        ]);

        $errors = $v->passes() ? [] : $v->errors();

        if (!isset($errors['email']) && User::findByEmail((string) $_POST['email'])) {
            $errors['email'] = 'Un compte existe déjà avec cet email.';
        }
        if (!isset($errors['password']) && ($_POST['password'] ?? '') !== ($_POST['password_confirm'] ?? '')) {
            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors) {
            with_input($_POST, $errors);
            $this->redirect('/inscription');
        }

        $id = User::register($_POST['name'], $_POST['email'], $_POST['password'], $_POST['city'] ?? null);
        Auth::login(User::find($id));
        clear_input();
        flash('success', 'Bienvenue dans la Karavan ! 🚐');
        $this->redirect('/mon-espace');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        Auth::logout();
        flash('info', 'Vous êtes déconnecté. À bientôt sur la route !');
        $this->redirect('/');
    }
}

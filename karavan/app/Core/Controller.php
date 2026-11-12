<?php
declare(strict_types=1);

namespace App\Core;

/** Contrôleur de base : rendu des vues, redirections, protection CSRF et authentification. */
class Controller
{
    /** Rend une vue dans le layout principal. */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $data['currentUser'] = Auth::user();
        echo View::render($view, $data, $layout);
    }

    /** Page d'erreur toujours rendue avec le layout public. */
    protected function renderError(array $data): void
    {
        $data['currentUser'] = Auth::user();
        echo View::render('errors/error', $data, 'main');
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function back(): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? url('/');
        header('Location: ' . $ref);
        exit;
    }

    /** Vérifie le jeton CSRF de tout formulaire POST. */
    protected function verifyCsrf(): void
    {
        $token = $_POST['_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['_token'] ?? '', $token)) {
            http_response_code(419);
            $this->renderError([
                'title'   => 'Session expirée',
                'message' => 'Votre session a expiré. Rechargez la page et réessayez.',
            ]);
            exit;
        }
    }

    /** Redirige vers la connexion si aucun membre n'est connecté. */
    protected function requireAuth(): array
    {
        $user = Auth::user();
        if ($user === null) {
            // Après connexion, on revient sur la page demandée (GET) ou sur la page précédente (POST)
            $_SESSION['intended'] = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
                ? ($_SERVER['REQUEST_URI'] ?? '/')
                : ($_SERVER['HTTP_REFERER'] ?? null);
            flash('info', 'Connectez-vous pour continuer.');
            $this->redirect('/connexion');
        }
        return $user;
    }

    /** Réservé aux administrateurs : 403 pour les membres connectés. */
    protected function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            $this->renderError([
                'title'   => 'Accès réservé',
                'message' => "Cette zone est réservée à l'équipe d'administration de Karavan.",
            ]);
            exit;
        }
        return $user;
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->renderError([
            'title'   => 'Page introuvable',
            'message' => "Cette route ne mène nulle part… Retournez sur la route des festivals.",
        ]);
    }
}

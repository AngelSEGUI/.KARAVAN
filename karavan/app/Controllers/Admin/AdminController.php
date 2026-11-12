<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Base des contrôleurs d'administration :
 * vérifie le rôle admin dès la construction et utilise le layout "admin".
 */
abstract class AdminController extends Controller
{
    protected array $admin;

    public function __construct()
    {
        $this->admin = $this->requireAdmin();
    }

    protected function render(string $view, array $data = [], string $layout = 'admin'): void
    {
        parent::render('admin/' . $view, $data, $layout);
    }

    /** Pour les actions POST : jeton CSRF obligatoire. */
    protected function guard(): void
    {
        $this->verifyCsrf();
    }
}

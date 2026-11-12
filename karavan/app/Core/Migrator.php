<?php
declare(strict_types=1);

namespace App\Core;

use PDOException;

/**
 * Mises à jour légères du schéma pour les bases créées avant une évolution.
 * Chaque étape vérifie si elle est nécessaire : on peut l'exécuter à chaque requête.
 */
final class Migrator
{
    public static function run(): void
    {
        $db = Database::connection();

        // v1.1 — rôles et suspension des membres
        try {
            $db->query('SELECT role, suspended FROM users LIMIT 1');
        } catch (PDOException) {
            $db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'member'");
            $db->exec('ALTER TABLE users ADD COLUMN suspended BOOLEAN NOT NULL DEFAULT 0');
            // Le premier compte devient administrateur s'il n'en existe aucun
            $db->exec("UPDATE users SET role = 'admin' WHERE id = (SELECT MIN(id) FROM users)");
        }
    }
}

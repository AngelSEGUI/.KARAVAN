<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Interception des erreurs : plutôt qu'une page à moitié affichée, on vide la sortie
 * en cours et on affiche une page d'erreur complète et lisible.
 * En mode debug, elle détaille l'erreur et propose une solution pour les cas connus.
 */
final class ErrorHandler
{
    private static bool $debug = true;

    public static function register(bool $debug): void
    {
        self::$debug = $debug;
        ini_set('display_errors', '0'); // c'est nous qui affichons, proprement
        error_reporting(E_ALL);

        // Avertissements et notices transformés en exceptions : on ne les rate plus
        set_error_handler(function (int $level, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $level)) {
                return false;
            }
            throw new \ErrorException($message, 0, $level, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);

        // Erreurs fatales non interceptables (mémoire épuisée, etc.)
        register_shutdown_function(function (): void {
            $e = error_get_last();
            if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::handle(new \ErrorException($e['message'], 0, $e['type'], $e['file'], $e['line']));
            }
        });
    }

    public static function handle(Throwable $e): void
    {
        // Jette tout ce qui avait déjà été produit (vues partiellement rendues)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        error_log('[Karavan] ' . $e::class . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

        echo self::page($e);
    }

    /** Conseils pour les problèmes d'installation les plus fréquents. */
    private static function hint(Throwable $e): ?string
    {
        $msg = $e->getMessage();
        return match (true) {
            str_contains($msg, 'could not find driver'), str_contains($msg, 'pdo_sqlite')
                => "L'extension PHP SQLite n'est pas installée. Ubuntu/Debian : <code>sudo apt install php-sqlite3</code> · Fedora : <code>sudo dnf install php-pdo</code> · Windows : décommentez <code>extension=pdo_sqlite</code> dans php.ini. Puis relancez le serveur.",
            str_contains($msg, 'unable to open database'), str_contains($msg, 'readonly database'), str_contains($msg, 'Permission denied')
                => "PHP ne peut pas écrire dans le dossier <code>storage/</code>. Donnez-lui les droits d'écriture : <code>chmod -R 775 storage</code>.",
            str_contains($msg, 'Call to undefined function')
                => "Une extension PHP manque. Vérifiez la liste avec <code>php -m</code> et installez-la (ex. <code>sudo apt install php-mbstring</code>), puis relancez le serveur.",
            default => null,
        };
    }

    private static function page(Throwable $e): string
    {
        $h = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $root = defined('ROOT_PATH') ? ROOT_PATH . DIRECTORY_SEPARATOR : '';
        $file = str_replace($root, '', $e->getFile());

        $details = '';
        if (self::$debug) {
            $hint = self::hint($e);
            $trace = str_replace($root, '', $e->getTraceAsString());
            $details = '<div class="box"><p class="type">' . $h($e::class) . '</p>'
                . '<p class="msg">' . $h($e->getMessage()) . '</p>'
                . '<p class="where">' . $h($file) . ' — ligne ' . $e->getLine() . '</p></div>'
                . ($hint ? '<div class="hint"><strong>Solution probable</strong><p>' . $hint . '</p></div>' : '')
                . '<details><summary>Pile d\'appels</summary><pre>' . $h($trace) . '</pre></details>'
                . '<p class="note">Ces détails s\'affichent car <code>debug</code> vaut <code>true</code> dans config/config.php. Passez-le à <code>false</code> en production.</p>';
        }

        return '<!doctype html><html lang="fr"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1"><title>Erreur — Karavan</title>'
            . '<style>body{margin:0;font:16px/1.55 system-ui,sans-serif;background:#f7f1e8;color:#17132b}'
            . 'main{max-width:760px;margin:0 auto;padding:48px 20px}h1{font-size:2rem;margin:0 0 8px}'
            . '.box,.hint{border-radius:14px;padding:16px 20px;margin:20px 0}.box{background:#fff;border:1px solid #e7dfd2}'
            . '.type{margin:0;color:#d6336c;font-weight:700;font-size:.85rem}.msg{margin:6px 0;font-weight:600;word-break:break-word}'
            . '.where{margin:0;color:#6d6884;font-size:.9rem}.hint{background:#fff5d6;color:#5c4500}.hint p{margin:6px 0 0}'
            . 'code{background:rgba(0,0,0,.06);padding:1px 6px;border-radius:5px;font-size:.9em}'
            . 'pre{background:#17132b;color:#f7f1e8;padding:16px;border-radius:12px;overflow:auto;font-size:.8rem}'
            . 'summary{cursor:pointer;font-weight:600}.note{color:#6d6884;font-size:.85rem}</style></head><body><main>'
            . '<h1>Oups, une erreur est survenue</h1>'
            . '<p>La page n\'a pas pu être affichée.' . (self::$debug ? '' : ' Réessayez dans un instant.') . '</p>'
            . $details
            . '</main></body></html>';
    }
}

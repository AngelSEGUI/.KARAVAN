<?php
declare(strict_types=1);

/**
 * Fonctions utilitaires disponibles dans toutes les vues et contrôleurs.
 */

/** Échappe une valeur pour l'affichage HTML. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Préfixe d'URL si l'app tourne dans un sous-dossier. */
function base_path(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    return ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');
}

function url(string $path = '/', array $query = []): string
{
    $url = base_path() . '/' . ltrim($path, '/');
    $query = array_filter($query, fn ($v) => $v !== null && $v !== '');
    return $query ? $url . '?' . http_build_query($query) : $url;
}

function asset(string $path): string
{
    $file = ROOT_PATH . '/public/assets/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 0;
    return url('assets/' . ltrim($path, '/')) . '?v=' . $v;
}

/** Page active dans la navigation. */
function is_active(string $prefix): bool
{
    $path = substr(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', strlen(base_path()));
    return $prefix === '/' ? $path === '/' || $path === '' : str_starts_with($path, $prefix);
}

function csrf_token(): string
{
    if (empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** Messages flash (affichés une fois). */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flashes(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

/** Ancienne saisie et erreurs de formulaire, conservées après redirection. */
function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function error(string $key): ?string
{
    return $_SESSION['_errors'][$key] ?? null;
}

function with_input(array $input, array $errors): void
{
    unset($input['_token'], $input['password'], $input['password_confirm']);
    $_SESSION['_old'] = $input;
    $_SESSION['_errors'] = $errors;
}

function clear_input(): void
{
    unset($_SESSION['_old'], $_SESSION['_errors']);
}

/** Dates en français. */
function date_fr(string $date, string $format = 'long'): string
{
    $ts = strtotime($date);
    $days = ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'];
    $months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $d = (int) date('j', $ts);
    $m = $months[(int) date('n', $ts) - 1];

    return match ($format) {
        'short' => "$d $m",
        'day'   => $days[(int) date('w', $ts)] . " $d $m",
        'time'  => $days[(int) date('w', $ts)] . " $d $m · " . date('H\hi', $ts),
        default => "$d $m " . date('Y', $ts),
    };
}

function month_short(string $date): string
{
    return ['janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'][(int) date('n', strtotime($date)) - 1];
}

function month_name(int $m): string
{
    return ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'][$m - 1];
}

function date_range(string $start, string $end): string
{
    if (date('Y-m', strtotime($start)) === date('Y-m', strtotime($end))) {
        return date('j', strtotime($start)) . '–' . date_fr($end);
    }
    return date_fr($start, 'short') . ' – ' . date_fr($end);
}

function price(float|int $amount): string
{
    return fmod((float) $amount, 1.0) === 0.0
        ? number_format((float) $amount, 0, ',', ' ') . ' €'
        : number_format((float) $amount, 2, ',', ' ') . ' €';
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    return mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
}

function plural(int $n, string $singular, ?string $plural = null): string
{
    return $n . ' ' . ($n > 1 ? ($plural ?? $singular . 's') : $singular);
}

function partial(string $name, array $data = []): string
{
    return App\Core\View::partial($name, $data);
}

<?php
declare(strict_types=1);

/**
 * Remplacements des fonctions de l'extension mbstring, utilisés seulement si elle
 * n'est pas installée (fréquent sous Linux : paquet php-mbstring absent).
 * Ils gèrent correctement l'UTF-8 (accents) grâce aux expressions régulières /u.
 */

if (!function_exists('mb_strlen')) {
    function mb_strlen(string $s, ?string $encoding = null): int
    {
        return (int) preg_match_all('/./us', $s);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr(string $s, int $start, ?int $length = null, ?string $encoding = null): string
    {
        $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return implode('', array_slice($chars, $start, $length));
    }
}

if (!function_exists('mb_strtolower') || !function_exists('mb_strtoupper')) {
    /** Correspondance minuscules/majuscules pour les lettres accentuées courantes. */
    function karavan_case_map(): array
    {
        static $map = null;
        if ($map === null) {
            $lower = preg_split('//u', 'àâäáãåæçéèêëíìîïñóòôöõøœúùûüýÿ', -1, PREG_SPLIT_NO_EMPTY);
            $upper = preg_split('//u', 'ÀÂÄÁÃÅÆÇÉÈÊËÍÌÎÏÑÓÒÔÖÕØŒÚÙÛÜÝŸ', -1, PREG_SPLIT_NO_EMPTY);
            $map = ['up' => array_combine($lower, $upper), 'down' => array_combine($upper, $lower)];
        }
        return $map;
    }
}

if (!function_exists('mb_strtolower')) {
    function mb_strtolower(string $s, ?string $encoding = null): string
    {
        return strtr(strtolower($s), karavan_case_map()['down']);
    }
}

if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper(string $s, ?string $encoding = null): string
    {
        return strtr(strtoupper($s), karavan_case_map()['up']);
    }
}

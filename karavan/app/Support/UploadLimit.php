<?php

namespace App\Support;

/**
 * Taille maximale réelle d'une image envoyée : la plus petite entre la limite Karavan
 * (config/festival_design.php) et celles de PHP (upload_max_filesize, post_max_size).
 */
class UploadLimit
{
    public static function kilobytes(): int
    {
        return (int) min(
            config('festival_design.max_image_kb'),
            self::iniToKb(ini_get('upload_max_filesize')),
            self::iniToKb(ini_get('post_max_size')),
        );
    }

    /** "5 Mo", "800 Ko" */
    public static function label(): string
    {
        $kb = self::kilobytes();

        return $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1, ',', ''), '0'), ',') . ' Mo' : "{$kb} Ko";
    }

    private static function iniToKb(string|false $value): int
    {
        if ($value === false || $value === '' || $value === '0' || $value === '-1') {
            return PHP_INT_MAX;
        }
        $number = (float) $value;

        return (int) match (strtoupper(substr(trim($value), -1))) {
            'G' => $number * 1024 * 1024,
            'M' => $number * 1024,
            'K' => $number,
            default => $number / 1024,
        };
    }
}

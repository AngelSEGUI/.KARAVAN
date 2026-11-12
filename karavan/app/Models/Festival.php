<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Festival extends Model
{
    protected static string $table = 'festivals';

    /** Sous-requêtes comptant les offres de la communauté pour chaque festival. */
    private const COUNTS = '
        (SELECT COUNT(*) FROM rides r WHERE r.festival_id = f.id)    AS rides_count,
        (SELECT COUNT(*) FROM housings h WHERE h.festival_id = f.id) AS housings_count';

    public static function findBySlug(string $slug): ?array
    {
        return static::first('SELECT f.*, ' . self::COUNTS . ' FROM festivals f WHERE slug = ?', [$slug]);
    }

    public static function featured(int $limit = 5): array
    {
        return static::select(
            'SELECT f.*, ' . self::COUNTS . ' FROM festivals f WHERE featured = 1 ORDER BY start_date LIMIT ' . $limit
        );
    }

    /** Recherche multi-critères pour la page découverte. */
    public static function search(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(f.name LIKE ? OR f.city LIKE ? OR f.lineup LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['genre'])) {
            // Requête portable SQLite / MySQL sur une liste "a,b,c"
            $g = $filters['genre'];
            $where[] = '(f.genres = ? OR f.genres LIKE ? OR f.genres LIKE ? OR f.genres LIKE ?)';
            array_push($params, $g, $g . ',%', '%,' . $g, '%,' . $g . ',%');
        }
        if (!empty($filters['region'])) {
            $where[] = 'f.region = ?';
            $params[] = $filters['region'];
        }
        if (!empty($filters['month'])) {
            $where[] = 'SUBSTR(f.start_date, 6, 2) = ?';
            $params[] = str_pad((string) (int) $filters['month'], 2, '0', STR_PAD_LEFT);
        }

        $order = match ($filters['sort'] ?? 'date') {
            'name'     => 'f.name',
            'popular'  => 'f.attendance DESC',
            'offers'   => '(rides_count + housings_count) DESC',
            default    => 'f.start_date',
        };

        $sql = 'SELECT f.*, ' . self::COUNTS . ' FROM festivals f'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY ' . $order;

        return static::select($sql, $params);
    }

    public static function genres(): array
    {
        $genres = [];
        foreach (static::select('SELECT genres FROM festivals') as $row) {
            foreach (explode(',', $row['genres']) as $g) {
                $genres[trim($g)] = true;
            }
        }
        $list = array_keys($genres);
        sort($list);
        return $list;
    }

    public static function regions(): array
    {
        return array_column(static::select('SELECT DISTINCT region FROM festivals ORDER BY region'), 'region');
    }

    /** Liste légère pour les menus déroulants des formulaires. */
    public static function options(): array
    {
        return static::select('SELECT id, name, city, start_date FROM festivals ORDER BY start_date');
    }

    /** "Les Vieilles Charrues" -> "les-vieilles-charrues" */
    public static function slugify(string $text): string
    {
        // Accents retirés sans dépendre de l'extension iconv
        $text = strtr($text, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o', 'ø' => 'o', 'œ' => 'oe',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y',
            'À' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Á' => 'a', 'Ç' => 'c', 'É' => 'e', 'È' => 'e', 'Ê' => 'e',
            'Ë' => 'e', 'Î' => 'i', 'Ï' => 'i', 'Ô' => 'o', 'Ö' => 'o', 'Œ' => 'oe', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
        ]);
        $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text));
        return trim($text, '-') ?: 'festival';
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return static::first('SELECT id FROM festivals WHERE slug = ? AND id <> ?', [$slug, $exceptId ?? 0]) !== null;
    }

    /** Festivals à venir sans aucune offre : à signaler à l'équipe. */
    public static function withoutOffers(): array
    {
        return static::select(
            'SELECT * FROM (SELECT f.*, ' . self::COUNTS . ' FROM festivals f) AS f
             WHERE rides_count = 0 OR housings_count = 0 ORDER BY start_date'
        );
    }

    public static function stats(): array
    {
        return static::first('SELECT
            (SELECT COUNT(*) FROM festivals) AS festivals,
            (SELECT COUNT(*) FROM rides)     AS rides,
            (SELECT COUNT(*) FROM housings)  AS housings,
            (SELECT COUNT(*) FROM users)     AS members') ?? [];
    }
}

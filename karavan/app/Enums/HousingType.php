<?php

namespace App\Enums;

/** Types de logement proposés par la communauté. Stocké en texte dans housings.type. */
enum HousingType: string
{
    case Tent = 'tente';
    case Van = 'van';
    case Room = 'chambre';
    case Apartment = 'appartement';
    case House = 'maison';

    public function label(): string
    {
        return match ($this) {
            self::Tent => 'Emplacement tente',
            self::Van => 'Van / camping-car',
            self::Room => "Chambre chez l'habitant",
            self::Apartment => 'Appartement',
            self::House => 'Maison entière',
        };
    }

    /** Nom de l'icône (voir resources/views/components/icon.blade.php). */
    public function icon(): string
    {
        return match ($this) {
            self::Tent => 'tent',
            self::Van => 'van',
            default => 'home',
        };
    }

    /** @return array<string, string> valeur => libellé, pour les listes déroulantes */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}

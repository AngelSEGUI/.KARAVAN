<?php

namespace Database\Seeders;

use App\Models\Festival;
use App\Models\Housing;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Démo de l'espace organisateur : un compte pro et sa page de festival entièrement personnalisée
 * (palette, polices, paragraphes, bannière, icône, carrousel, affiche, programmation).
 *
 * « Festival Marées Hautes » est un festival FICTIF : artistes et visuels inventés
 * (images générées, dans database/seeders/media/marees-hautes/).
 * Un second membre a une demande de statut organisateur en attente, à valider depuis l'admin.
 */
class OrganizerDemoSeeder extends Seeder
{
    private const MEDIA = __DIR__ . '/media/marees-hautes';

    public function run(): void
    {
        $password = Hash::make(DatabaseSeeder::DEMO_PASSWORD);

        $pro = new User([
            'name' => 'Camille Le Goff',
            'email' => 'pro@karavan.test',
            'city' => 'Saint-Malo',
            'bio' => 'Programmatrice du Festival Marées Hautes.',
            'organization' => 'Association Marées Hautes',
            'organization_website' => 'https://marees-hautes.example',
        ]);
        $pro->password = $password;
        $pro->role = 'pro';
        $pro->email_verified_at = now();
        $pro->save();

        // Demande de statut organisateur en attente (à traiter dans Administration › Membres)
        $applicant = new User([
            'name' => 'Yann Kerbrat',
            'email' => 'yann@karavan.test',
            'city' => 'Brest',
            'bio' => 'Bénévole et bidouilleur de sons.',
            'organization' => 'Collectif Fest-Noz Électro',
            'organization_website' => 'https://festnoz-electro.example',
            'pro_request_message' => 'Nous organisons une soirée électro-trad sur le port de Brest chaque automne et aimerions y associer le covoiturage Karavan.',
        ]);
        $applicant->password = $password;
        $applicant->role = 'member';
        $applicant->email_verified_at = now();
        $applicant->pro_requested_at = now()->subDay();
        $applicant->save();

        $festival = new Festival([
            'slug' => 'marees-hautes',
            'name' => 'Festival Marées Hautes',
            'city' => 'Saint-Malo',
            'region' => 'Bretagne',
            'venue' => 'Plage du Sillon',
            'starts_on' => '2027-07-22',
            'ends_on' => '2027-07-24',
            'genres' => ['Électro', 'Pop', 'Indé'],
            'lineup' => ['Nova Brume', 'Atlas Vega', 'Kéraudren Sound System', 'Les Goélands Électriques'],
            'tagline' => 'Trois nuits de musique les pieds dans le sable, face aux remparts.',
            'description' => "Né d'une bande d'amis sur la plage du Sillon, Marées Hautes réunit chaque été la scène électro et pop indé, entre coucher de soleil et grande marée.",
            'attendance' => 18000,
            'price_from' => 39,
            'website' => 'https://marees-hautes.example',
            'ticket_url' => 'https://marees-hautes.example/billetterie',
        ]);
        $festival->hue = 15;
        $festival->is_published = true;
        $festival->is_featured = true;
        $festival->user_id = $pro->id;
        $festival->design = [
            'palette' => 'custom', 'primary' => '#ff6b4a', 'secondary' => '#3ad6c5', 'mode' => 'dark',
            'heading_font' => 'Unbounded', 'body_font' => 'Work Sans',
        ];
        $festival->save();

        $this->copyMedia($festival);

        foreach ([
            ['Un festival les pieds dans l\'eau', "Trois scènes installées entre la digue et la plage : la Grande Scène face au large, la Scène du Phare pour les découvertes et le Chapiteau pour finir la nuit.\nLe site est entièrement accessible aux personnes à mobilité réduite."],
            ['Venir au festival', "Gare de Saint-Malo à 15 minutes à pied. Le parking est très limité : privilégiez le covoiturage Karavan, le train ou le vélo (parking vélo gardé gratuit)."],
            ['Engagements', "Gobelets consignés, toilettes sèches, food-trucks locaux et une navette de nuit vers le centre et les campings jusqu'à 4h."],
        ] as $i => [$title, $body]) {
            $festival->sections()->create(['title' => $title, 'body' => $body, 'position' => $i]);
        }

        foreach ($this->programme() as $day => $slots) {
            foreach ($slots as [$start, $end, $artist, $stage]) {
                $startsAt = Carbon::parse("{$day} {$start}");
                if ($startsAt->hour < 6) {
                    $startsAt->addDay();
                }
                $endsAt = $startsAt->copy()->setTimeFromTimeString($end);
                if ($endsAt->lessThanOrEqualTo($startsAt)) {
                    $endsAt->addDay();
                }
                $festival->performances()->create([
                    'artist' => $artist, 'stage' => $stage, 'starts_at' => $startsAt, 'ends_at' => $endsAt,
                    // Capacité renseignée pour deux scènes seulement : celle du Chapiteau reste masquée
                    'capacity' => ['Grande Scène' => 12000, 'Scène du Phare' => 3500][$stage] ?? null,
                ]);
            }
        }

        // Un peu de vie communautaire autour du festival
        $ride = new Ride([
            'departure_city' => 'Rennes', 'meeting_point' => 'Gare de Rennes, sortie Nord',
            'departs_at' => '2027-07-22 15:00', 'returns_at' => '2027-07-25 12:00',
            'seats' => 3, 'price' => 9, 'vehicle' => 'Renault Clio',
            'notes' => 'Retour le dimanche midi, après le petit-déj face à la mer.',
        ]);
        $ride->festival()->associate($festival);
        $ride->driver()->associate(User::where('email', 'lea@karavan.test')->first() ?? $applicant);
        $ride->save();

        $housing = new Housing([
            'title' => 'Chambre avec vue sur les remparts', 'type' => 'chambre', 'city' => 'Saint-Malo',
            'distance_km' => 1.2, 'capacity' => 2, 'price_per_night' => 48,
            'description' => 'Chambre double dans une maison de pêcheur, à dix minutes à pied de la plage du Sillon.',
            'amenities' => ['Petit-déjeuner', 'Douche', 'Wi-Fi'],
        ]);
        $housing->festival()->associate($festival);
        $housing->host()->associate(User::where('email', 'chloe@karavan.test')->first() ?? $applicant);
        $housing->save();
    }

    /** Copie les visuels de démo dans public/uploads/festivals/{id}/ */
    private function copyMedia(Festival $festival): void
    {
        $disk = Storage::disk('uploads');
        $dir = $festival->mediaDirectory();
        $disk->deleteDirectory($dir);

        $put = fn (string $file, string $target) => $disk->put($target, File::get(self::MEDIA . '/' . $file)) ? $target : null;

        $festival->banner_path = $put('banniere.jpg', "{$dir}/banniere.jpg");
        $festival->icon_path = $put('icone.png', "{$dir}/icone.png");
        $festival->poster_path = $put('affiche.jpg', "{$dir}/affiche.jpg");
        $festival->save();

        $captions = [
            1 => 'La Grande Scène, samedi soir',
            2 => 'Coucher de soleil sur le Sillon',
            3 => 'Scène du Phare',
            4 => 'Le phare veille sur la dernière nuit',
            5 => 'Le Chapiteau, jusqu\'au bout de la nuit',
        ];
        foreach ($captions as $n => $caption) {
            $festival->photos()->create([
                'path' => $put("photo-{$n}.jpg", "{$dir}/carrousel/photo-{$n}.jpg"),
                'caption' => $caption,
                'position' => $n,
            ]);
        }
    }

    /** Programmation fictive : [début, fin, artiste, scène]. Après minuit = nuit de la même journée. */
    private function programme(): array
    {
        return [
            '2027-07-22' => [
                ['18:00', '19:00', 'Iodé', 'Scène du Phare'],
                ['19:00', '20:00', 'Lumière Basse', 'Grande Scène'],
                ['19:30', '20:30', 'Brise Lame', 'Chapiteau'],
                ['20:15', '21:15', 'Capitaine Sono', 'Scène du Phare'],
                ['20:30', '21:45', 'Les Goélands Électriques', 'Grande Scène'],
                ['21:00', '22:30', 'Kalimer', 'Chapiteau'],
                ['22:00', '23:30', 'Nova Brume', 'Grande Scène'],
                ['22:45', '23:45', 'Écume', 'Scène du Phare'],
                ['23:00', '01:00', 'Phare Ouest', 'Chapiteau'],
                ['01:00', '03:00', 'Les Albatros (DJ set)', 'Chapiteau'],
            ],
            '2027-07-23' => [
                ['17:30', '18:30', 'Petite Houle', 'Scène du Phare'],
                ['18:30', '19:30', 'Sœurs Tempête', 'Grande Scène'],
                ['19:00', '20:00', 'Ressac', 'Chapiteau'],
                ['19:45', '20:45', 'Grain de Sel', 'Scène du Phare'],
                ['20:00', '21:15', 'Brise Lame', 'Grande Scène'],
                ['21:30', '23:00', 'Ondine & les Rafales', 'Grande Scène'],
                ['21:15', '22:15', 'Vent d\'Ouest', 'Scène du Phare'],
                ['21:00', '23:00', 'Marina Solstice', 'Chapiteau'],
                ['23:30', '01:00', 'Atlas Vega', 'Grande Scène'],
                ['23:30', '02:30', 'Kéraudren Sound System', 'Chapiteau'],
            ],
            '2027-07-24' => [
                ['16:00', '17:00', 'Chorale des Embruns', 'Scène du Phare'],
                ['17:30', '18:30', 'Les Mouettes Rieuses', 'Grande Scène'],
                ['18:00', '19:00', 'Kalimer', 'Scène du Phare'],
                ['19:00', '20:15', 'Capitaine Sono', 'Grande Scène'],
                ['19:30', '21:00', 'Écume', 'Chapiteau'],
                ['20:30', '21:30', 'Les Albatros', 'Scène du Phare'],
                ['21:00', '22:30', 'Marina Solstice', 'Grande Scène'],
                ['22:00', '23:00', 'Ressac', 'Scène du Phare'],
                ['23:00', '00:45', 'Kéraudren Sound System', 'Grande Scène'],
                ['23:30', '04:00', 'Nuit Blanche Collective', 'Chapiteau'],
            ],
        ];
    }
}

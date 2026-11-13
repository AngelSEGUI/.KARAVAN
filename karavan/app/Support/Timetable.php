<?php

namespace App\Support;

use App\Models\Performance;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Prépare la programmation d'un festival pour la vue calendrier :
 * une colonne par scène, une ligne de temps par journée, chaque créneau positionné en minutes.
 *
 * Une « journée » va de 6h00 à 6h00 le lendemain : un concert à 1h30 apparaît
 * dans la soirée de la veille, comme sur les vrais programmes de festival.
 */
class Timetable
{
    /** @var list<array{key: string, label: string, short: string, start: CarbonInterface, minutes: int, hours: list<array{label: string, offset: int}>, stages: array<string, list<array{performance: Performance, offset: int, length: int}>>}> */
    public array $days = [];

    /** @param Collection<int, Performance> $performances */
    public function __construct(Collection $performances)
    {
        // Ordre des scènes : la plus grande capacité d'abord (scènes sans capacité ensuite), puis ordre d'apparition
        $firstSeen = $performances->sortBy('starts_at')->pluck('stage')->unique()->values()->flip();
        $stageOrder = $performances->groupBy('stage')
            ->map(fn ($slots, $stage) => [-(int) $slots->max('capacity'), $firstSeen[$stage]])
            ->sort()->keys()->all();

        foreach ($performances->sortBy('starts_at')->groupBy(fn (Performance $p) => $p->festivalDay()) as $key => $slots) {
            $start = $slots->min('starts_at')->copy()->startOfHour();
            $end = $slots->max('ends_at')->copy();
            if ($end->minute > 0 || $end->second > 0) {
                $end = $end->startOfHour()->addHour();
            }
            $minutes = max(60, (int) $start->diffInMinutes($end));

            $hours = [];
            for ($offset = 0; $offset <= $minutes; $offset += 60) {
                $hours[] = ['label' => $start->copy()->addMinutes($offset)->format('H\h'), 'offset' => $offset];
            }

            $stages = array_fill_keys(array_values(array_intersect($stageOrder, $slots->pluck('stage')->unique()->all())), []);
            foreach ($slots as $p) {
                $stages[$p->stage][] = [
                    'performance' => $p,
                    'offset' => (int) $start->diffInMinutes($p->starts_at),
                    'length' => max(15, $p->durationInMinutes()),
                ];
            }

            $date = $slots->first()->starts_at->copy()->subHours(Performance::DAY_STARTS_AT_HOUR)->startOfDay();
            $this->days[] = [
                'key' => $key,
                'label' => ucfirst($date->translatedFormat('l j F')),
                'short' => ucfirst($date->translatedFormat('D j')),
                'start' => $start,
                'minutes' => $minutes,
                'hours' => $hours,
                'stages' => $stages,
            ];
        }
    }

    public function isEmpty(): bool
    {
        return $this->days === [];
    }
}

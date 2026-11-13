<?php

namespace App\Http\Requests;

use App\Models\Ride;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation de la création ET de la modification d'un trajet.
 * En modification, si des places sont déjà réservées :
 *   - le festival ne peut plus changer (les passagers ont réservé pour celui-ci) ;
 *   - le nombre de places ne peut pas descendre sous le nombre de places réservées.
 */
class RideRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ride = $this->ride();

        return $ride === null || $this->user()->can('update', $ride);
    }

    public function rules(): array
    {
        $ride = $this->ride();
        $booked = $ride?->bookedSeats() ?? 0;

        return [
            'festival_id' => array_filter([
                'required', 'integer', Rule::exists('festivals', 'id')->where('is_published', true),
                $booked > 0 ? Rule::in([$ride->festival_id]) : null,
            ]),
            'departure_city' => ['required', 'string', 'max:80'],
            'meeting_point' => ['nullable', 'string', 'max:160'],
            'departs_at' => array_filter(['required', 'date', $ride === null ? 'after:now' : null]),
            'returns_at' => ['nullable', 'date', 'after:departs_at'],
            'seats' => ['required', 'integer', 'min:' . max(1, $booked), 'max:' . config('karavan.max_ride_seats')],
            'price' => ['required', 'numeric', 'min:0', 'max:999'],
            'vehicle' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        $booked = $this->ride()?->bookedSeats() ?? 0;

        return [
            'festival_id.in' => 'Des passagers ont déjà réservé : le festival ne peut plus être changé.',
            'seats.min' => $booked > 0
                ? "Il y a déjà {$booked} place(s) réservée(s) : vous ne pouvez pas en proposer moins."
                : 'Proposez au moins une place.',
            'departs_at.after' => 'La date de départ doit être dans le futur.',
            'returns_at.after' => 'Le retour doit être après le départ.',
        ];
    }

    /** Trajet en cours de modification (null en création). */
    public function ride(): ?Ride
    {
        $ride = $this->route('ride');

        return $ride instanceof Ride ? $ride : null;
    }
}

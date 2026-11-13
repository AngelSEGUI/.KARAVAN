<?php

namespace App\Http\Requests;

use App\Enums\HousingType;
use App\Models\Housing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation de la création ET de la modification d'un logement.
 * En modification, si des demandes existent :
 *   - le festival ne peut plus changer ;
 *   - la capacité ne peut pas descendre sous le plus grand groupe déjà accepté.
 */
class HousingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $housing = $this->housing();

        return $housing === null || $this->user()->can('update', $housing);
    }

    public function rules(): array
    {
        $housing = $this->housing();
        $hasRequests = $housing?->bookings()->exists() ?? false;
        $largestGroup = $housing?->largestGroup() ?? 0;

        return [
            'festival_id' => array_filter([
                'required', 'integer', 'exists:festivals,id',
                $hasRequests ? Rule::in([$housing->festival_id]) : null,
            ]),
            'title' => ['required', 'string', 'min:5', 'max:140'],
            'type' => ['required', Rule::enum(HousingType::class)],
            'city' => ['required', 'string', 'max:80'],
            'distance_km' => ['required', 'numeric', 'min:0', 'max:200'],
            'capacity' => ['required', 'integer', 'min:' . max(1, $largestGroup), 'max:' . config('karavan.max_housing_capacity')],
            'price_per_night' => ['required', 'numeric', 'min:0', 'max:9999'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amenities' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        $largestGroup = $this->housing()?->largestGroup() ?? 0;

        return [
            'festival_id.in' => 'Des voyageurs ont déjà fait une demande : le festival ne peut plus être changé.',
            'capacity.min' => $largestGroup > 1
                ? "Une demande concerne {$largestGroup} personnes : la capacité ne peut pas être inférieure."
                : 'La capacité doit être d\'au moins 1 personne.',
        ];
    }

    /** Données prêtes à enregistrer : "Douche, Parking" devient ["Douche", "Parking"]. */
    public function housingData(): array
    {
        $data = $this->validated();
        $data['amenities'] = collect(explode(',', (string) ($data['amenities'] ?? '')))
            ->map(fn ($a) => trim($a))->filter()->unique()->values()->all() ?: null;

        return $data;
    }

    public function housing(): ?Housing
    {
        $housing = $this->route('housing');

        return $housing instanceof Housing ? $housing : null;
    }
}

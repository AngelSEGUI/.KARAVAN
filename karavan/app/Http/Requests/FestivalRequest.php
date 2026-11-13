<?php

namespace App\Http\Requests;

use App\Models\Festival;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Onglet « Informations » d'un festival (création et modification), par un organisateur ou un admin. */
class FestivalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $festival = $this->route('festival');

        return $festival instanceof Festival
            ? $this->user()->can('update', $festival)
            : $this->user()->can('create', Festival::class);
    }

    /** L'adresse (slug) est générée depuis le nom si elle est laissée vide. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name'))),
        ]);
    }

    public function rules(): array
    {
        $festival = $this->route('festival');

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('festivals', 'slug')->ignore($festival?->id)],
            'city' => ['required', 'string', 'max:80'],
            'region' => ['required', 'string', 'max:80'],
            'venue' => ['nullable', 'string', 'max:160'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'genres' => ['required', 'string', 'max:160'],
            'lineup' => ['nullable', 'string', 'max:2000'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'attendance' => ['nullable', 'integer', 'min:0'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'website' => ['nullable', 'url', 'max:200'],
            'ticket_url' => ['nullable', 'url', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => "L'adresse /festivals/:input est déjà utilisée par un autre festival.",
            'ends_on.after_or_equal' => 'La date de fin doit être identique ou après la date de début.',
        ];
    }

    /** "Rock, Pop" -> ["Rock", "Pop"] pour les champs liste. */
    public function festivalData(): array
    {
        $data = $this->validated();
        foreach (['genres', 'lineup'] as $field) {
            $list = collect(explode(',', (string) ($data[$field] ?? '')))->map(fn ($v) => trim($v))->filter()->values()->all();
            $data[$field] = $list ?: ($field === 'genres' ? [] : null);
        }

        return $data;
    }
}

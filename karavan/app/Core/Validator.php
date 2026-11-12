<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validation simple des formulaires.
 * Règles : required, email, min:N, max:N, int, numeric, date, after:champ, in:a,b,c
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $data, private array $rules, private array $labels = [])
    {
    }

    public function passes(): bool
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = trim((string) ($this->data[$field] ?? ''));
            $label = $this->labels[$field] ?? $field;

            foreach (explode('|', $ruleString) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

                if ($name !== 'required' && $value === '') {
                    continue;
                }

                $error = match ($name) {
                    'required' => $value === '' ? "$label est obligatoire." : null,
                    'email'    => !filter_var($value, FILTER_VALIDATE_EMAIL) ? "$label n'est pas une adresse valide." : null,
                    'min'      => mb_strlen($value) < (int) $arg ? "$label doit faire au moins $arg caractères." : null,
                    'max'      => mb_strlen($value) > (int) $arg ? "$label ne doit pas dépasser $arg caractères." : null,
                    'int'      => filter_var($value, FILTER_VALIDATE_INT) === false ? "$label doit être un nombre entier." : null,
                    'numeric'  => !is_numeric($value) ? "$label doit être un nombre." : null,
                    'gte'      => (float) $value < (float) $arg ? "$label doit être supérieur ou égal à $arg." : null,
                    'date'     => strtotime($value) === false ? "$label n'est pas une date valide." : null,
                    'after'    => strtotime($value) < strtotime((string) ($this->data[$arg] ?? '')) ? "$label doit être après " . mb_strtolower($this->labels[$arg] ?? $arg) . '.' : null,
                    'in'       => !in_array($value, explode(',', (string) $arg), true) ? "$label n'est pas valide." : null,
                    default    => null,
                };

                if ($error !== null) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
        }

        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}

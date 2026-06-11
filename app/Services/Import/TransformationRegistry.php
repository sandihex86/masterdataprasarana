<?php

namespace App\Services\Import;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransformationRegistry
{
    /**
     * @param  array<int, string>  $transformations
     */
    public function apply(mixed $value, array $transformations): mixed
    {
        foreach ($transformations as $transformation) {
            $value = $this->transform($transformation, $value);
        }

        return $value;
    }

    public function assertAllowed(string $transformation): void
    {
        if (! in_array($transformation, config('master-data.mapping.allowed_transformations', []), true)) {
            throw ValidationException::withMessages([
                'transformations' => ["Transformasi [{$transformation}] tidak diizinkan."],
            ]);
        }
    }

    public function transform(string $transformation, mixed $value): mixed
    {
        $this->assertAllowed($transformation);

        return match ($transformation) {
            'trim' => is_string($value) ? trim($value) : $value,
            'uppercase' => is_string($value) ? Str::upper($value) : $value,
            'lowercase' => is_string($value) ? Str::lower($value) : $value,
            'title_case' => is_string($value) ? Str::title($value) : $value,
            'nullable_string' => $this->nullableString($value),
            'integer' => $this->toInteger($value, false),
            'nullable_integer' => $this->toInteger($value, true),
            'float' => $this->toFloat($value, false),
            'nullable_float' => $this->toFloat($value, true),
            'boolean' => $this->toBoolean($value),
            'date' => $this->toDate($value, false),
            'datetime' => $this->toDate($value, true),
            'null_if_empty' => $this->nullIfEmpty($value),
            'normalize_whitespace' => is_string($value) ? preg_replace('/\s+/u', ' ', trim($value)) : $value,
            'remove_control_characters' => is_string($value) ? preg_replace('/[\x00-\x1F\x7F]/u', '', $value) : $value,
            'normalize_code' => is_string($value) ? Str::upper(Str::of($value)->replaceMatches('/[^A-Za-z0-9\-_]/', '')->trim()->toString()) : $value,
            'decimal_comma_to_dot' => is_string($value) ? str_replace(',', '.', $value) : $value,
            default => throw ValidationException::withMessages([
                'transformations' => ["Transformasi [{$transformation}] belum didukung."],
            ]),
        };
    }

    private function nullableString(mixed $value): ?string
    {
        $value = $this->nullIfEmpty($value);

        return $value === null ? null : (string) $value;
    }

    private function toInteger(mixed $value, bool $nullable): ?int
    {
        $value = $this->nullIfEmpty($value);

        if ($value === null && $nullable) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw ValidationException::withMessages([
                'transformations' => ['Nilai tidak dapat dikonversi menjadi integer.'],
            ]);
        }

        return (int) $value;
    }

    private function toFloat(mixed $value, bool $nullable): ?float
    {
        $value = $this->nullIfEmpty($value);

        if ($value === null && $nullable) {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }

        if (! is_numeric($value)) {
            throw ValidationException::withMessages([
                'transformations' => ['Nilai tidak dapat dikonversi menjadi float.'],
            ]);
        }

        return (float) $value;
    }

    private function toBoolean(mixed $value): bool
    {
        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($normalized === null) {
            throw ValidationException::withMessages([
                'transformations' => ['Nilai tidak dapat dikonversi menjadi boolean.'],
            ]);
        }

        return $normalized;
    }

    private function toDate(mixed $value, bool $includeTime): ?string
    {
        $value = $this->nullIfEmpty($value);

        if ($value === null) {
            return null;
        }

        $date = Carbon::parse((string) $value);

        return $includeTime ? $date->toDateTimeString() : $date->toDateString();
    }

    private function nullIfEmpty(mixed $value): mixed
    {
        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return $value;
    }
}

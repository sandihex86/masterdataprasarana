<?php

namespace App\Services\MasterData;

use App\Enums\MasterDataStatus;
use App\Models\MasterDataType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MasterDataValidationService
{
    public function validate(array $payload, ?MasterDataType $type = null): array
    {
        $rules = array_merge(
            $this->globalRules(),
            $this->buildTypeRules($type?->validation_rules ?? []),
        );

        return Validator::make($payload, $rules)->validate();
    }

    public function globalRules(): array
    {
        return [
            'entity_type' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:191'],
            'name' => ['nullable', 'string', 'max:191'],
            'parent_code' => ['nullable', 'string', 'max:191'],
            'source_system' => ['nullable', 'string', 'max:100'],
            'source_table' => ['nullable', 'string', 'max:100'],
            'source_id' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', MasterDataStatus::values())],
            'data' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
            'data.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'data.longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @param  array<string, array<int, string>>  $storedRules
     * @return array<string, array<int, string>>
     */
    private function buildTypeRules(array $storedRules): array
    {
        $allowedRules = config('master-data.validation.allowed_rules', []);
        $normalizedRules = [];

        foreach ($storedRules as $field => $fieldRules) {
            foreach (Arr::wrap($fieldRules) as $rule) {
                $baseRule = strtok((string) $rule, ':');

                if (! in_array($baseRule, $allowedRules, true)) {
                    throw ValidationException::withMessages([
                        $field => ["Rule [$baseRule] tidak diizinkan pada konfigurasi jenis master data."],
                    ]);
                }

                $normalizedRules[$field][] = $rule;
            }
        }

        return $normalizedRules;
    }
}

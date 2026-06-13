<?php

namespace App\Services\Import;

use App\Enums\MasterDataStatus;
use App\Models\ImportMapping;
use App\Models\User;
use App\Services\LegacyDatabaseService;
use App\Services\MasterData\MasterDataValidationService;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class MappingService
{
    public function __construct(
        private readonly TransformationRegistry $transformations,
        private readonly LegacyDatabaseService $legacyDatabase,
        private readonly MasterDataValidationService $validationService,
    ) {}

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<string, mixed>
     */
    public function validateConfiguration(array $configuration, string $connection = 'bridge'): array
    {
        $required = ['source_system', 'source_table', 'entity_type', 'identity'];

        foreach ($required as $field) {
            if (! array_key_exists($field, $configuration)) {
                throw ValidationException::withMessages([
                    $field => ["Field mapping [{$field}] wajib ada."],
                ]);
            }
        }

        $table = (string) $configuration['source_table'];
        $columns = $this->legacyDatabase->listColumns($table, $connection);
        $this->assertMappingColumnsExist($configuration, $columns);
        $this->assertTransformationsAllowed($configuration['transformations'] ?? []);

        return $configuration;
    }

    /**
     * @param  array<string, mixed>  $sourceRow
     * @param  array<string, mixed>  $configuration
     * @return array<string, mixed>
     */
    public function mapRow(array $sourceRow, array $configuration): array
    {
        $configuration = $this->validateConfiguration($configuration);
        $transformations = $configuration['transformations'] ?? [];

        $record = [
            'source_system' => $configuration['source_system'],
            'source_table' => $configuration['source_table'],
            'source_id' => $this->stringify(
                $this->resolveField($sourceRow, $configuration['identity']['source_id'] ?? null, $transformations['source_id'] ?? []),
            ),
            'entity_type' => $configuration['entity_type'],
            'code' => $this->stringify(
                $this->resolveField($sourceRow, $configuration['identity']['code'] ?? null, $transformations['code'] ?? []),
            ),
            'name' => $this->stringify(
                $this->resolveField($sourceRow, $configuration['columns']['name'] ?? null, $transformations['name'] ?? []),
            ),
            'parent_code' => $this->stringify(
                $this->resolveField($sourceRow, $configuration['columns']['parent_code'] ?? null, $transformations['parent_code'] ?? []),
            ),
            'description' => $this->stringify(
                $this->resolveField($sourceRow, $configuration['columns']['description'] ?? null, $transformations['description'] ?? []),
            ),
            'data' => [],
            'metadata' => [
                'raw_source' => $configuration['source_system'],
                'mapping_version' => 1,
            ],
            'status' => $configuration['status'] ?? MasterDataStatus::Active->value,
        ];

        foreach ($configuration['data'] ?? [] as $targetField => $specification) {
            $record['data'][$targetField] = $this->resolveField(
                $sourceRow,
                $specification,
                $transformations[$targetField] ?? [],
            );
        }

        return $this->validationService->validate($record);
    }

    /**
     * @param  array<string, mixed>  $sourceRow
     * @param  array<string, mixed>|string|null  $specification
     * @param  array<int, string>  $transformations
     */
    public function resolveField(array $sourceRow, array|string|null $specification, array $transformations = []): mixed
    {
        if ($specification === null) {
            return null;
        }

        if (is_string($specification)) {
            $value = $sourceRow[$specification] ?? null;

            return $this->transformations->apply($value, $transformations);
        }

        $source = $specification['source'] ?? null;
        $default = $specification['default'] ?? null;
        $nullable = $specification['nullable'] ?? true;
        $inlineTransformations = array_merge($transformations, $specification['transformations'] ?? []);

        $value = $source !== null ? ($sourceRow[$source] ?? null) : $default;
        $value = $value ?? $default;

        if ($value === null && ! $nullable) {
            throw ValidationException::withMessages([
                'mapping' => ['Field mapping wajib memiliki nilai dan tidak boleh null.'],
            ]);
        }

        return $this->transformations->apply($value, $inlineTransformations);
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<int, array<string, mixed>>
     */
    public function preview(array $configuration, int $limit = 10, string $connection = 'bridge'): array
    {
        $configuration = $this->validateConfiguration($configuration, $connection);
        $rows = $this->legacyDatabase->sampleRows(
            (string) $configuration['source_table'],
            min(max($limit, 1), config('master-data.import.preview_limit')),
            $connection,
        );

        return array_map(function (array $row) use ($configuration): array {
            try {
                return [
                    'source' => $row,
                    'normalized' => $this->mapRow($row, $configuration),
                    'errors' => [],
                ];
            } catch (ValidationException $exception) {
                return [
                    'source' => $row,
                    'normalized' => null,
                    'errors' => $exception->errors(),
                ];
            }
        }, $rows);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function persist(array $payload, ?ImportMapping $mapping = null): ImportMapping
    {
        $configuration = $this->normalizePayloadToConfiguration($payload);
        $this->validateConfiguration($configuration);

        $data = [
            'name' => $payload['name'],
            'source_system' => $configuration['source_system'],
            'source_table' => $configuration['source_table'],
            'entity_type' => $configuration['entity_type'],
            'version' => $payload['version'] ?? ($mapping?->version ?? 1),
            'mapping' => $configuration,
            'transformations' => $configuration['transformations'] ?? [],
            'validation_rules' => $payload['validation_rules'] ?? [],
            'is_active' => $payload['is_active'] ?? true,
            'created_by' => $mapping?->created_by ?? $this->actorId(),
            'updated_by' => $this->actorId(),
        ];

        if ($mapping === null) {
            return ImportMapping::query()->create($data);
        }

        $mapping->fill($data);
        $mapping->save();

        return $mapping->fresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalizePayloadToConfiguration(array $payload): array
    {
        if (isset($payload['mapping']) && is_array($payload['mapping'])) {
            return $payload['mapping'];
        }

        return Arr::only($payload, [
            'source_system',
            'source_table',
            'entity_type',
            'identity',
            'columns',
            'data',
            'transformations',
            'status',
        ]);
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @param  array<int, string>  $columns
     */
    private function assertMappingColumnsExist(array $configuration, array $columns): void
    {
        $references = [];

        $references[] = $configuration['identity']['source_id'] ?? null;
        $references[] = $configuration['identity']['code'] ?? null;

        foreach (($configuration['columns'] ?? []) as $specification) {
            $references[] = is_array($specification) ? ($specification['source'] ?? null) : $specification;
        }

        foreach (($configuration['data'] ?? []) as $specification) {
            $references[] = is_array($specification) ? ($specification['source'] ?? null) : $specification;
        }

        foreach (array_filter($references) as $reference) {
            if (! in_array($reference, $columns, true)) {
                throw ValidationException::withMessages([
                    'mapping' => ["Kolom source [{$reference}] tidak ditemukan pada tabel source."],
                ]);
            }
        }
    }

    /**
     * @param  array<string, array<int, string>>  $transformations
     */
    private function assertTransformationsAllowed(array $transformations): void
    {
        foreach ($transformations as $fieldTransforms) {
            foreach ($fieldTransforms as $transformation) {
                $this->transformations->assertAllowed($transformation);
            }
        }
    }

    private function actorId(): ?int
    {
        $actor = request()->user();

        return $actor instanceof User ? $actor->getKey() : null;
    }

    private function stringify(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}

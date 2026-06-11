<?php

namespace App\Services\MasterData;

use App\Enums\AuditAction;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Validation\ValidationException;

class MasterDataWriteService
{
    public function __construct(
        private readonly MasterDataValidationService $validationService,
        private readonly MasterDataChecksumService $checksumService,
        private readonly AuditService $auditService,
    ) {}

    public function create(array $payload): MasterData
    {
        $type = MasterDataType::query()->where('code', $payload['entity_type'])->first();
        $validated = $this->validationService->validate($payload, $type);

        $this->ensureNotDuplicate($validated);

        $validated['checksum'] = $this->checksumService->generate($validated);
        $validated['version'] = 1;
        $validated['created_by'] = $this->userId();
        $validated['updated_by'] = $this->userId();

        $record = MasterData::query()->create($validated);

        $this->auditService->record(AuditAction::Create, $record, [], $record->fresh()->toArray());

        return $record->fresh(['type']);
    }

    public function update(MasterData $record, array $payload): MasterData
    {
        if (isset($payload['uuid']) && $payload['uuid'] !== $record->uuid) {
            throw ValidationException::withMessages([
                'uuid' => ['UUID tidak dapat diubah.'],
            ]);
        }

        if (isset($payload['source_system'], $payload['source_table'], $payload['source_id'])) {
            $sourceChanged = $record->source_system !== $payload['source_system']
                || $record->source_table !== $payload['source_table']
                || $record->source_id !== $payload['source_id'];

            if ($sourceChanged && $record->source_system !== null && $record->source_table !== null && $record->source_id !== null) {
                throw ValidationException::withMessages([
                    'source_id' => ['Identifier sumber tidak boleh diubah sembarangan.'],
                ]);
            }
        }

        $typeCode = $payload['entity_type'] ?? $record->entity_type;
        $type = MasterDataType::query()->where('code', $typeCode)->first();
        $validated = $this->validationService->validate(array_merge($record->toArray(), $payload), $type);
        $this->ensureUpdateDuplicate($record, $validated);

        $before = $record->toArray();
        $normalizedForChecksum = $this->checksumService->generate($validated);
        $validated['checksum'] = $normalizedForChecksum;
        $validated['updated_by'] = $this->userId();

        if ($record->checksum !== $normalizedForChecksum) {
            $validated['version'] = $record->version + 1;
        }

        $record->fill($validated);
        $record->save();

        [$oldValues, $newValues] = $this->auditService->diff($before, $record->fresh()->toArray());
        $this->auditService->record(AuditAction::Update, $record, $oldValues, $newValues);

        return $record->fresh(['type']);
    }

    public function delete(MasterData $record): void
    {
        $before = $record->toArray();
        $record->delete();
        $this->auditService->record(AuditAction::Delete, $record, $before, []);
    }

    public function restore(MasterData $record): MasterData
    {
        $record->restore();
        $record->forceFill(['updated_by' => $this->userId()])->save();
        $this->auditService->record(AuditAction::Restore, $record, [], $record->fresh()->toArray());

        return $record->fresh(['type']);
    }

    private function ensureNotDuplicate(array $validated): void
    {
        $duplicate = MasterData::query()
            ->where('entity_type', $validated['entity_type'])
            ->where('code', $validated['code'])
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'code' => ['Kombinasi entity type dan code sudah digunakan.'],
            ]);
        }
    }

    private function ensureUpdateDuplicate(MasterData $record, array $validated): void
    {
        $duplicate = MasterData::query()
            ->where('entity_type', $validated['entity_type'])
            ->where('code', $validated['code'])
            ->whereKeyNot($record->getKey())
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'code' => ['Kombinasi entity type dan code sudah digunakan.'],
            ]);
        }
    }

    private function userId(): ?int
    {
        $actor = request()->user();

        return $actor instanceof User ? $actor->id : null;
    }
}

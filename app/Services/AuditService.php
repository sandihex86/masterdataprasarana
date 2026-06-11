<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\ApiClient;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditService
{
    public function record(AuditAction $action, Model $model, array $oldValues = [], array $newValues = []): void
    {
        if ($oldValues === $newValues && $action === AuditAction::Update) {
            return;
        }

        $actor = request()->user();

        AuditLog::query()->create([
            'user_id' => $actor instanceof User ? $actor->id : null,
            'api_client_id' => $actor instanceof ApiClient ? $actor->id : null,
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'request_id' => request()->attributes->get('request_id'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function diff(array $before, array $after): array
    {
        $beforeDot = Arr::dot($before);
        $afterDot = Arr::dot($after);
        $changedKeys = [];

        foreach (array_unique(array_merge(array_keys($beforeDot), array_keys($afterDot))) as $key) {
            if (($beforeDot[$key] ?? null) !== ($afterDot[$key] ?? null)) {
                $changedKeys[] = $key;
            }
        }

        $oldValues = [];
        $newValues = [];

        foreach ($changedKeys as $key) {
            Arr::set($oldValues, $key, $beforeDot[$key] ?? null);
            Arr::set($newValues, $key, $afterDot[$key] ?? null);
        }

        return [$oldValues, $newValues];
    }
}

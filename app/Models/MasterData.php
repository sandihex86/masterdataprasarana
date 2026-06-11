<?php

namespace App\Models;

use App\Enums\MasterDataStatus;
use App\Models\Concerns\HasPublicUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterData extends Model
{
    use HasFactory, HasPublicUuid, SoftDeletes;

    protected $table = 'master_data';

    protected $fillable = [
        'source_system',
        'source_table',
        'source_id',
        'entity_type',
        'code',
        'name',
        'parent_code',
        'description',
        'data',
        'metadata',
        'checksum',
        'version',
        'status',
        'synced_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'metadata' => 'array',
            'synced_at' => 'datetime',
            'status' => MasterDataStatus::class,
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(MasterDataType::class, 'entity_type', 'code');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeEntityType(Builder $query, ?string $entityType): Builder
    {
        return $query->when($entityType, fn (Builder $builder) => $builder->where('entity_type', $entityType));
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $builder) => $builder->where('status', $status));
    }
}

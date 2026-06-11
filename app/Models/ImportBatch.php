<?php

namespace App\Models;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportMode;
use App\Models\Concerns\HasPublicUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory, HasPublicUuid;

    protected $fillable = [
        'filename',
        'file_path',
        'source_connection',
        'source_system',
        'source_table',
        'entity_type',
        'mode',
        'status',
        'total_rows',
        'processed_rows',
        'success_rows',
        'updated_rows',
        'unchanged_rows',
        'duplicate_rows',
        'failed_rows',
        'progress_percentage',
        'options',
        'summary',
        'started_at',
        'finished_at',
        'cancelled_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'mode' => ImportMode::class,
            'status' => ImportBatchStatus::class,
            'options' => 'array',
            'summary' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportError::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

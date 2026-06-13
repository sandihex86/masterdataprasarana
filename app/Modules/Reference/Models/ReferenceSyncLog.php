<?php

namespace App\Modules\Reference\Models;

class ReferenceSyncLog extends ReferenceModel
{
    protected $table = 'reference_sync_logs';

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}

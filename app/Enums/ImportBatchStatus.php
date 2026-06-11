<?php

namespace App\Enums;

enum ImportBatchStatus: string
{
    case Pending = 'pending';
    case Profiling = 'profiling';
    case Validating = 'validating';
    case Queued = 'queued';
    case Processing = 'processing';
    case Completed = 'completed';
    case CompletedWithErrors = 'completed_with_errors';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}

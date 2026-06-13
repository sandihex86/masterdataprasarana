<?php

namespace App\Modules\Reference\Models;

class WorkArea extends ReferenceModel
{
    protected $table = 'ref_work_areas';

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];
}

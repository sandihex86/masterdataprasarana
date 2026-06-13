<?php

namespace App\Modules\Reference\Models;

class OperationalArea extends ReferenceModel
{
    protected $table = 'ref_operational_areas';

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];
}

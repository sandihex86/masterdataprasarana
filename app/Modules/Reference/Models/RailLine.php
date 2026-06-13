<?php

namespace App\Modules\Reference\Models;

class RailLine extends ReferenceModel
{
    protected $table = 'ref_rail_lines';

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];
}

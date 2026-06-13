<?php

namespace App\Modules\Reference\Models;

class Station extends ReferenceModel
{
    protected $table = 'ref_stations';

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}

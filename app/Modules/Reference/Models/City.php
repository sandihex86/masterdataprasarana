<?php

namespace App\Modules\Reference\Models;

class City extends ReferenceModel
{
    protected $table = 'ref_cities';

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];
}

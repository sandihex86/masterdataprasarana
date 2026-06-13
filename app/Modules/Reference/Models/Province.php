<?php

namespace App\Modules\Reference\Models;

class Province extends ReferenceModel
{
    protected $table = 'ref_provinces';

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];
}

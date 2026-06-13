<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class BridgeProtectionStructure extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan_detil_3';

    protected $guarded = [];

    public $timestamps = false;
}

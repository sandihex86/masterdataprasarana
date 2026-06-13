<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class BridgeSteelStructure extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan_baja';

    protected $guarded = [];

    public $timestamps = false;
}

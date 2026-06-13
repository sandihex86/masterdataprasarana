<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class BridgeConcreteStructure extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan_beton';

    protected $guarded = [];

    public $timestamps = false;
}

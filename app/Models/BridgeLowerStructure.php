<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class BridgeLowerStructure extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan_bawah';

    protected $guarded = [];

    public $timestamps = false;
}

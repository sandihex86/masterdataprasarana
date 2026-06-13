<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class BridgeMaintenance extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan_perawatan';

    protected $guarded = [];

    public $timestamps = false;
}

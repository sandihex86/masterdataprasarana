<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class BridgeSpan extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan_bentang';

    protected $guarded = [];

    public $timestamps = false;
}

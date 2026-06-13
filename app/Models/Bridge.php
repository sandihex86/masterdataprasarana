<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class Bridge extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan';

    protected $guarded = [];

    public $timestamps = false;
}

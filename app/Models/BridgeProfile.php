<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class BridgeProfile extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan_profil';

    protected $guarded = [];

    public $timestamps = false;
}

<?php

namespace App\Models;

use App\Models\Concerns\UsesBridgeSourceConnection;
use Illuminate\Database\Eloquent\Model;

class BridgeSurvey extends Model
{
    use UsesBridgeSourceConnection;

    protected $table = 'm_jembatan_survey';

    protected $guarded = [];

    public $timestamps = false;
}

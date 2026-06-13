<?php

namespace App\Modules\Track\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class TrackModel extends DomainModel
{
    protected $connection = 'track';
}

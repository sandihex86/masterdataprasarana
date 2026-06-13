<?php

namespace App\Modules\Tunnel\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class TunnelModel extends DomainModel
{
    protected $connection = 'tunnel';
}

<?php

namespace App\Modules\Bridge\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class BridgeModel extends DomainModel
{
    protected $connection = 'bridge';
}

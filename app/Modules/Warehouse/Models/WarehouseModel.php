<?php

namespace App\Modules\Warehouse\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class WarehouseModel extends DomainModel
{
    protected $connection = 'warehouse';
}

<?php

namespace App\Modules\OperationalFacility\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class OperationalFacilityModel extends DomainModel
{
    protected $connection = 'operational_facility';
}

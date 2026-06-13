<?php

namespace App\Modules\Reporting\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class ReportingModel extends DomainModel
{
    protected $connection = 'reporting';
}

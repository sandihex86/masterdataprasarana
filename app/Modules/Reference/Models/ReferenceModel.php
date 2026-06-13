<?php

namespace App\Modules\Reference\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class ReferenceModel extends DomainModel
{
    protected $connection = 'reference';
}

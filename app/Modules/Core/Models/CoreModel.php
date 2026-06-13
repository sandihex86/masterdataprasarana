<?php

namespace App\Modules\Core\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class CoreModel extends DomainModel
{
    protected $connection = 'core';
}

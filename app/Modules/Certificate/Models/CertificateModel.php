<?php

namespace App\Modules\Certificate\Models;

use App\Modules\Shared\Models\DomainModel;

abstract class CertificateModel extends DomainModel
{
    protected $connection = 'certificate';
}

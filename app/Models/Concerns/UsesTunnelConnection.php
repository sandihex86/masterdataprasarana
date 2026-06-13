<?php

namespace App\Models\Concerns;

trait UsesTunnelConnection
{
    public function getConnectionName()
    {
        return 'tunnel';
    }
}

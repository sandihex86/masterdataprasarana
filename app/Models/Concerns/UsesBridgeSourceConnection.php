<?php

namespace App\Models\Concerns;

use App\Support\BridgeSource\BridgeSourceSql;

trait UsesBridgeSourceConnection
{
    public function getConnectionName()
    {
        return app(BridgeSourceSql::class)->connectionName();
    }
}

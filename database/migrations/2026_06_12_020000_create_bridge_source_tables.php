<?php

use App\Support\BridgeSource\BridgeSourceSql;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $bridgeSourceSql = app(BridgeSourceSql::class);
        $connection = $bridgeSourceSql->connectionName();
        $schema = Schema::connection($connection);
        $database = DB::connection($connection);

        foreach ($bridgeSourceSql->createStatements() as $table => $statement) {
            if ($schema->hasTable($table)) {
                continue;
            }

            $database->unprepared($statement);
        }
    }

    public function down(): void
    {
        $bridgeSourceSql = app(BridgeSourceSql::class);
        $connection = $bridgeSourceSql->connectionName();
        $schema = Schema::connection($connection);

        foreach (array_reverse($bridgeSourceSql->sourceTables()) as $table) {
            if ($schema->hasTable($table)) {
                $schema->drop($table);
            }
        }
    }
};

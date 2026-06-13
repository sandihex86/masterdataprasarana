<?php

namespace Database\Seeders;

use App\Support\BridgeSource\BridgeSourceSql;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class BridgeSourceSqlSeeder extends Seeder
{
    public function run(BridgeSourceSql $bridgeSourceSql): void
    {
        $connection = $bridgeSourceSql->connectionName();
        $schema = Schema::connection($connection);
        $database = DB::connection($connection);
        $tables = $bridgeSourceSql->sourceTables();

        foreach ($tables as $table) {
            if (! $schema->hasTable($table)) {
                throw new RuntimeException("Bridge source table [{$table}] belum tersedia pada koneksi [{$connection}]. Jalankan migration source jembatan terlebih dahulu.");
            }
        }

        $database->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach (array_reverse($tables) as $table) {
                $database->table($table)->truncate();
            }

            $bridgeSourceSql->streamInsertStatements(function (string $table, string $statement) use ($database): void {
                $database->unprepared($statement);
            }, $tables);
        } finally {
            $database->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}

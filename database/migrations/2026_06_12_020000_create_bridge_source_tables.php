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

            $database->unprepared($database->getDriverName() === 'sqlite'
                ? $this->sqliteCreateStatement($statement)
                : $statement);
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

    private function sqliteCreateStatement(string $statement): string
    {
        if (! preg_match('/CREATE TABLE `([^`]+)` \((.*)\) ENGINE=/is', $statement, $matches)) {
            return $statement;
        }

        $table = $matches[1];
        $body = $matches[2];
        $lines = preg_split('/\R/', $body) ?: [];
        $columns = [];

        foreach ($lines as $line) {
            $line = trim($line);
            $line = rtrim($line, ',');

            if ($line === ''
                || str_starts_with($line, 'PRIMARY KEY')
                || str_starts_with($line, 'KEY ')
                || str_starts_with($line, 'UNIQUE KEY')) {
                continue;
            }

            if (preg_match('/^`id`\s+int\(\d+\)\s+NOT NULL AUTO_INCREMENT$/i', $line) === 1) {
                $columns[] = '`id` INTEGER PRIMARY KEY AUTOINCREMENT';

                continue;
            }

            $line = preg_replace('/\s+AUTO_INCREMENT/i', '', $line) ?? $line;
            $line = preg_replace('/\s+CHARACTER SET\s+\w+/i', '', $line) ?? $line;
            $line = preg_replace('/\s+COLLATE\s+\w+/i', '', $line) ?? $line;
            $line = preg_replace('/DEFAULT current_timestamp\(\)/i', 'DEFAULT CURRENT_TIMESTAMP', $line) ?? $line;

            $columns[] = $line;
        }

        return sprintf("CREATE TABLE `%s` (\n  %s\n);", $table, implode(",\n  ", $columns));
    }
};

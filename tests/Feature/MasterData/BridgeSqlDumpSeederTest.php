<?php

namespace Tests\Feature\MasterData;

use App\Models\MasterData;
use Database\Seeders\BridgeSqlDumpSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BridgeSqlDumpSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_bridge_sql_dump_is_imported_into_master_data(): void
    {
        $this->seed(BridgeSqlDumpSeeder::class);

        $this->assertGreaterThan(
            2500,
            MasterData::query()->where('entity_type', 'bridge')->count(),
        );

        $record = MasterData::query()
            ->where('entity_type', 'bridge')
            ->where('source_system', 'legacy_jembatan')
            ->where('source_id', '6468bf514455c')
            ->firstOrFail();

        $this->assertSame('6468bf514455c', $record->code);
        $this->assertSame('592', $record->data['bridge_number']);
        $this->assertSame('BTP Kelas I Bandung', $record->data['wil_ker_name']);
        $this->assertSame('108+471', $record->data['km_hm']);
        $this->assertIsArray($record->data['spans']);
        $this->assertIsArray($record->data['substructures']);
    }
}

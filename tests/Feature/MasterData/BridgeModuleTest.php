<?php

namespace Tests\Feature\MasterData;

use App\Models\ImportMapping;
use App\Models\MasterData;
use App\Models\MasterDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BridgeModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_bridge_master_data_module_is_seeded_from_legacy_jembatan_structure(): void
    {
        $this->seed();

        $type = MasterDataType::query()->where('code', 'bridge')->firstOrFail();

        $this->assertSame('Jembatan', $type->name);
        $this->assertSame('legacy_jembatan', $type->mapping_configuration['source_system']);
        $this->assertSame('m_jembatan', $type->mapping_configuration['source_table']);
        $this->assertSame('m_jembatan_profil', $type->mapping_configuration['detail_tables']['profile']);
        $this->assertSame(['nullable', 'array'], $type->validation_rules['data.profile']);
        $this->assertSame(['nullable', 'string', 'max:32'], $type->validation_rules['data.bridge_number']);

        $mapping = ImportMapping::query()
            ->where('entity_type', 'bridge')
            ->where('source_table', 'm_jembatan')
            ->firstOrFail();

        $this->assertSame('uniqid', $mapping->mapping['identity']['source_id']);
        $this->assertSame('uniqid', $mapping->mapping['identity']['code']);
        $this->assertSame('nama', $mapping->mapping['columns']['name']);
        $this->assertSame('no_bh', $mapping->mapping['data']['bridge_number']);
        $this->assertSame('lat', $mapping->mapping['data']['latitude']['source']);
        $this->assertSame(['decimal_comma_to_dot', 'nullable_float'], $mapping->mapping['data']['latitude']['transformations']);

        $record = MasterData::query()
            ->where('entity_type', 'bridge')
            ->where('code', 'BH-0001')
            ->firstOrFail();

        $this->assertSame('Jembatan Cikubang', $record->name);
        $this->assertSame('32', $record->parent_code);
        $this->assertSame('BH-0001', $record->data['bridge_number']);
        $this->assertSame('m_jembatan', $record->source_table);
        $this->assertSame(3, $record->data['profile']['span_count']);
        $this->assertSame(92.5, $record->data['assessment']['total_score']);
    }
}

<?php

namespace Tests\Unit;

use App\Services\MasterData\MasterDataChecksumService;
use PHPUnit\Framework\TestCase;

class MasterDataChecksumServiceTest extends TestCase
{
    public function test_checksum_is_deterministic_for_nested_json_payloads(): void
    {
        $service = new MasterDataChecksumService;

        $payloadA = [
            'entity_type' => 'station',
            'code' => 'GMR',
            'data' => [
                'longitude' => 106.8305,
                'latitude' => -6.1767,
            ],
            'metadata' => [
                'mapping_version' => 1,
                'raw_source' => 'legacy_database',
            ],
        ];

        $payloadB = [
            'code' => 'GMR',
            'metadata' => [
                'raw_source' => 'legacy_database',
                'mapping_version' => 1,
            ],
            'data' => [
                'latitude' => -6.1767,
                'longitude' => 106.8305,
            ],
            'entity_type' => 'station',
        ];

        $this->assertSame(
            $service->generate($payloadA),
            $service->generate($payloadB),
        );
    }
}

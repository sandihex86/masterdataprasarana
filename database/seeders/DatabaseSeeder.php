<?php

namespace Database\Seeders;

use App\Enums\MasterDataStatus;
use App\Enums\UserRole;
use App\Models\ApiClient;
use App\Models\ImportMapping;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superadmin = $this->seedUser(
            name: 'Superadmin Local',
            email: 'superadmin@example.com',
            role: UserRole::Superadmin,
        );

        $this->seedUser(
            name: 'Admin Local',
            email: 'admin@example.com',
            role: UserRole::Admin,
        );

        $this->seedUser(
            name: 'Operator Local',
            email: 'operator@example.com',
            role: UserRole::Operator,
        );

        $this->seedUser(
            name: 'Verifikator Local',
            email: 'verifikator@example.com',
            role: UserRole::Verifikator,
        );

        $this->seedUser(
            name: 'Viewer Local',
            email: 'viewer@example.com',
            role: UserRole::Viewer,
        );

        $stationType = $this->seedMasterDataType(
            code: 'station',
            name: 'Stasiun',
            actorId: $superadmin->id,
            attributes: [
                'validation_rules' => [
                    'code' => ['required', 'string', 'max:191'],
                    'name' => ['required', 'string', 'max:191'],
                    'data.latitude' => ['nullable', 'numeric', 'between:-90,90'],
                    'data.longitude' => ['nullable', 'numeric', 'between:-180,180'],
                ],
            ],
        );

        $this->seedMasterDataType(code: 'bridge', name: 'Jembatan', actorId: $superadmin->id);
        $this->seedMasterDataType(code: 'railway_track', name: 'Jalur Kereta', actorId: $superadmin->id);
        $this->seedMasterDataType(code: 'province', name: 'Provinsi', actorId: $superadmin->id);
        $this->seedMasterDataType(code: 'city', name: 'Kabupaten/Kota', actorId: $superadmin->id);
        $this->seedMasterDataType(code: 'operator', name: 'Operator', actorId: $superadmin->id);

        $this->seedMasterDataRecord($stationType, $superadmin->id);
        $this->seedApiClient($superadmin);
        $this->seedImportMapping($superadmin->id);
    }

    private function seedUser(string $name, string $email, UserRole $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'is_admin' => $role->grantsFullAccess(),
                'email_verified_at' => now(),
            ],
        );
    }

    private function seedMasterDataType(string $code, string $name, int $actorId, array $attributes = []): MasterDataType
    {
        $type = MasterDataType::query()->firstOrNew(['code' => $code]);

        if (! $type->exists) {
            $type->uuid = (string) Str::uuid();
            $type->created_by = $actorId;
        }

        $type->fill(array_merge([
            'name' => $name,
            'description' => $type->description ?? 'Data referensi untuk pengembangan lokal.',
            'validation_rules' => [
                'code' => ['required', 'string', 'max:191'],
                'name' => ['nullable', 'string', 'max:191'],
            ],
            'searchable_fields' => ['code', 'name'],
            'visible_fields' => ['code', 'name', 'status'],
            'mapping_configuration' => [
                'source_system' => 'legacy_example',
            ],
            'is_active' => true,
            'updated_by' => $actorId,
        ], $attributes));

        $type->save();

        return $type;
    }

    private function seedMasterDataRecord(MasterDataType $stationType, int $actorId): void
    {
        $record = MasterData::query()->firstOrNew([
            'source_system' => 'legacy_seed',
            'source_table' => 'mst_stasiun',
            'source_id' => '1',
        ]);

        if (! $record->exists) {
            $record->uuid = (string) Str::uuid();
            $record->created_by = $actorId;
        }

        $record->fill([
            'entity_type' => $stationType->code,
            'code' => 'GMR',
            'name' => 'Gambir',
            'parent_code' => '31',
            'description' => 'Contoh data stasiun untuk pengembangan lokal.',
            'data' => [
                'station_class' => 'Besar A',
                'province_code' => '31',
                'city_code' => '3171',
                'latitude' => -6.1767,
                'longitude' => 106.8305,
                'address' => 'Jakarta Pusat',
            ],
            'metadata' => [
                'raw_source' => 'seed',
                'mapping_version' => 1,
            ],
            'checksum' => hash('sha256', 'GMR'),
            'version' => 1,
            'status' => MasterDataStatus::Active,
            'updated_by' => $actorId,
        ]);

        $record->save();
    }

    private function seedApiClient(User $superadmin): void
    {
        $client = ApiClient::query()->firstOrNew(['code' => 'dummy_local_client']);

        if (! $client->exists) {
            $client->uuid = (string) Str::uuid();
            $client->created_by = $superadmin->id;
        }

        $client->forceFill([
            'name' => 'Client Dummy Lokal',
            'description' => $client->description ?? 'Client API bawaan untuk pengujian lokal.',
            'owner_name' => $superadmin->name,
            'owner_email' => $superadmin->email,
            'allowed_ips' => ['127.0.0.1'],
            'allowed_origins' => ['https://prasarana.labdata.id'],
            'rate_limit_per_minute' => 60,
            'rate_limit_per_day' => 10000,
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'updated_by' => $superadmin->id,
        ])->save();
    }

    private function seedImportMapping(int $actorId): void
    {
        $mapping = ImportMapping::query()->firstOrNew([
            'source_system' => 'legacy_djka',
            'source_table' => 'mst_stasiun',
            'entity_type' => 'station',
            'version' => 1,
        ]);

        if (! $mapping->exists) {
            $mapping->uuid = (string) Str::uuid();
            $mapping->created_by = $actorId;
        }

        $mapping->fill([
            'name' => 'Mapping Stasiun Legacy',
            'mapping' => [
                'source_system' => 'legacy_djka',
                'source_table' => 'mst_stasiun',
                'entity_type' => 'station',
                'identity' => [
                    'source_id' => 'id_stasiun',
                    'code' => 'kode_stasiun',
                ],
                'columns' => [
                    'name' => 'nama_stasiun',
                    'parent_code' => 'kode_provinsi',
                    'description' => 'keterangan',
                ],
                'data' => [
                    'station_class' => 'kelas_stasiun',
                    'province_code' => 'kode_provinsi',
                    'city_code' => 'kode_kabupaten',
                    'latitude' => 'latitude',
                    'longitude' => 'longitude',
                    'address' => 'alamat',
                ],
                'transformations' => [
                    'code' => ['trim', 'uppercase'],
                    'name' => ['trim', 'normalize_whitespace'],
                    'latitude' => ['nullable_float'],
                    'longitude' => ['nullable_float'],
                ],
            ],
            'transformations' => [
                'code' => ['trim', 'uppercase'],
                'name' => ['trim', 'normalize_whitespace'],
                'latitude' => ['nullable_float'],
                'longitude' => ['nullable_float'],
            ],
            'validation_rules' => [],
            'is_active' => true,
            'updated_by' => $actorId,
        ]);

        $mapping->save();
    }
}

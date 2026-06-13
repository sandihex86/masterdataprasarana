<?php

use App\Http\Controllers\Api\V1\BridgeController;
use App\Http\Controllers\Api\V1\BridgeBatchController;
use App\Http\Controllers\Api\V1\BridgeConditionController;
use App\Http\Controllers\Api\V1\BridgeIntegrationController;
use App\Http\Controllers\Api\V1\BridgeMaintenanceController;
use App\Http\Controllers\Api\V1\BridgeMapController;
use App\Http\Controllers\Api\V1\BridgeReferenceController;
use App\Http\Controllers\Api\V1\BridgeSourceTableController;
use App\Http\Controllers\Api\V1\BridgeTechnicalController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ImportMappingController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\MasterDataTypeController;
use App\Http\Controllers\Api\V1\TunnelController;
use Illuminate\Support\Facades\Route;

$tunnelRoutes = static function (): void {
    Route::get('/tunnels', [TunnelController::class, 'index'])
        ->middleware('abilities:master-data:read')
        ->name('api.tunnels.index');
    Route::post('/tunnels', [TunnelController::class, 'store'])
        ->middleware('abilities:master-data:write')
        ->name('api.tunnels.store');
    Route::get('/tunnels/{tunnel_id}/structure', [TunnelController::class, 'structure'])
        ->middleware('abilities:master-data:read')
        ->name('api.tunnels.structure.show');
    Route::match(['put', 'patch'], '/tunnels/{tunnel_id}/structure', [TunnelController::class, 'upsertStructure'])
        ->middleware('abilities:master-data:write')
        ->name('api.tunnels.structure.upsert');
    Route::get('/tunnels/{tunnel_id}/specs', [TunnelController::class, 'specs'])
        ->middleware('abilities:master-data:read')
        ->name('api.tunnels.specs.show');
    Route::match(['put', 'patch'], '/tunnels/{tunnel_id}/specs', [TunnelController::class, 'upsertSpecs'])
        ->middleware('abilities:master-data:write')
        ->name('api.tunnels.specs.upsert');
    Route::get('/tunnels/{tunnel_id}/docs', [TunnelController::class, 'docs'])
        ->middleware('abilities:master-data:read')
        ->name('api.tunnels.docs.show');
    Route::match(['put', 'patch'], '/tunnels/{tunnel_id}/docs', [TunnelController::class, 'upsertDocs'])
        ->middleware('abilities:master-data:write')
        ->name('api.tunnels.docs.upsert');
    Route::get('/tunnels/{tunnel_id}', [TunnelController::class, 'show'])
        ->middleware('abilities:master-data:read')
        ->name('api.tunnels.show');
    Route::match(['put', 'patch'], '/tunnels/{tunnel_id}', [TunnelController::class, 'update'])
        ->middleware('abilities:master-data:write')
        ->name('api.tunnels.update');
    Route::delete('/tunnels/{tunnel_id}', [TunnelController::class, 'destroy'])
        ->middleware('abilities:master-data:delete')
        ->name('api.tunnels.destroy');
};

Route::middleware(['auth:sanctum', 'api.actor'])->group($tunnelRoutes);

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'summary'])->name('api.v1.health.summary');
    Route::get('/health/live', [HealthController::class, 'live'])->name('api.v1.health.live');
    Route::get('/health/ready', [HealthController::class, 'ready'])->name('api.v1.health.ready');
    Route::get('/integration/health', [BridgeIntegrationController::class, 'health'])
        ->name('api.v1.integration.health');

    Route::middleware(['auth:sanctum', 'api.actor'])->group(function (): void {
        Route::get('/tunnels', [TunnelController::class, 'index'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.tunnels.index');
        Route::post('/tunnels', [TunnelController::class, 'store'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.tunnels.store');
        Route::get('/tunnels/{tunnel_id}/structure', [TunnelController::class, 'structure'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.tunnels.structure.show');
        Route::match(['put', 'patch'], '/tunnels/{tunnel_id}/structure', [TunnelController::class, 'upsertStructure'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.tunnels.structure.upsert');
        Route::get('/tunnels/{tunnel_id}/specs', [TunnelController::class, 'specs'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.tunnels.specs.show');
        Route::match(['put', 'patch'], '/tunnels/{tunnel_id}/specs', [TunnelController::class, 'upsertSpecs'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.tunnels.specs.upsert');
        Route::get('/tunnels/{tunnel_id}/docs', [TunnelController::class, 'docs'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.tunnels.docs.show');
        Route::match(['put', 'patch'], '/tunnels/{tunnel_id}/docs', [TunnelController::class, 'upsertDocs'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.tunnels.docs.upsert');
        Route::get('/tunnels/{tunnel_id}', [TunnelController::class, 'show'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.tunnels.show');
        Route::match(['put', 'patch'], '/tunnels/{tunnel_id}', [TunnelController::class, 'update'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.tunnels.update');
        Route::delete('/tunnels/{tunnel_id}', [TunnelController::class, 'destroy'])
            ->middleware('abilities:master-data:delete')
            ->name('api.v1.tunnels.destroy');

        Route::get('/master/bridges/batch', [BridgeBatchController::class, 'batch'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.batch');
        Route::get('/master/bridges/full-batch', [BridgeBatchController::class, 'fullBatch'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.full-batch');
        Route::get('/master/bridges/changed', [BridgeBatchController::class, 'changed'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.changed');
        Route::get('/master/bridges/search', [BridgeController::class, 'masterSearch'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.search');
        Route::get('/master/bridges/by-bh/{noBh}', [BridgeController::class, 'byBridgeNumber'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.by-bh');
        Route::get('/master/bridges/geojson', [BridgeMapController::class, 'geoJson'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.geojson');
        Route::get('/master/bridges/{kodeJembatan}/profile', [BridgeTechnicalController::class, 'profile'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.profile');
        Route::get('/master/bridges/{kodeJembatan}/spans', [BridgeTechnicalController::class, 'spans'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.spans');
        Route::get('/master/bridges/{kodeJembatan}', [BridgeController::class, 'masterShow'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.show');
        Route::get('/master/bridges', [BridgeController::class, 'masterIndex'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master.bridges.index');

        Route::get('/bridges/{kodeJembatan}/condition', [BridgeConditionController::class, 'condition'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridges.condition');
        Route::get('/bridges/{kodeJembatan}/maintenance', [BridgeMaintenanceController::class, 'index'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridges.maintenance.index');
        Route::post('/bridges/{kodeJembatan}/maintenance', [BridgeMaintenanceController::class, 'store'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.bridges.maintenance.store');

        Route::get('/references/provinces', [BridgeReferenceController::class, 'provinces'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.references.provinces');
        Route::get('/references/cities', [BridgeReferenceController::class, 'cities'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.references.cities');
        Route::get('/references/operation-areas', [BridgeReferenceController::class, 'operationAreas'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.references.operation-areas');
        Route::get('/references/work-areas', [BridgeReferenceController::class, 'workAreas'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.references.work-areas');
        Route::get('/references/routes', [BridgeReferenceController::class, 'routes'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.references.routes');
        Route::get('/references/stations', [BridgeReferenceController::class, 'stations'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.references.stations');
        Route::get('/references/segments', [BridgeReferenceController::class, 'segments'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.references.segments');

        Route::get('/bridges/metadata', [BridgeController::class, 'metadata'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridges.metadata');
        Route::get('/bridges', [BridgeController::class, 'index'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridges.index');
        Route::post('/bridges', [BridgeController::class, 'store'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.bridges.store');
        Route::get('/bridges/{bridgeUniqid}', [BridgeController::class, 'show'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridges.show');
        Route::match(['put', 'patch'], '/bridges/{bridgeUniqid}', [BridgeController::class, 'update'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.bridges.update');
        Route::delete('/bridges/{bridgeUniqid}', [BridgeController::class, 'destroy'])
            ->middleware('abilities:master-data:delete')
            ->name('api.v1.bridges.destroy');

        Route::get('/bridge-source/tables', [BridgeSourceTableController::class, 'catalog'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridge-source.tables.catalog');
        Route::get('/bridge-source/tables/{table}/schema', [BridgeSourceTableController::class, 'schema'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridge-source.tables.schema');
        Route::get('/bridge-source/tables/{table}/records', [BridgeSourceTableController::class, 'index'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridge-source.tables.records.index');
        Route::post('/bridge-source/tables/{table}/records', [BridgeSourceTableController::class, 'store'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.bridge-source.tables.records.store');
        Route::get('/bridge-source/tables/{table}/records/{rowKey}', [BridgeSourceTableController::class, 'show'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.bridge-source.tables.records.show');
        Route::match(['put', 'patch'], '/bridge-source/tables/{table}/records/{rowKey}', [BridgeSourceTableController::class, 'update'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.bridge-source.tables.records.update');
        Route::delete('/bridge-source/tables/{table}/records/{rowKey}', [BridgeSourceTableController::class, 'destroy'])
            ->middleware('abilities:master-data:delete')
            ->name('api.v1.bridge-source.tables.records.destroy');

        Route::get('/master-data', [MasterDataController::class, 'index'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master-data.index');
        Route::post('/master-data', [MasterDataController::class, 'store'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.master-data.store');
        Route::get('/master-data/{masterData:uuid}', [MasterDataController::class, 'show'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master-data.show');
        Route::match(['put', 'patch'], '/master-data/{masterData:uuid}', [MasterDataController::class, 'update'])
            ->middleware('abilities:master-data:write')
            ->name('api.v1.master-data.update');
        Route::delete('/master-data/{masterData:uuid}', [MasterDataController::class, 'destroy'])
            ->middleware('abilities:master-data:delete')
            ->name('api.v1.master-data.destroy');
        Route::post('/master-data/{masterDataUuid}/restore', [MasterDataController::class, 'restore'])
            ->middleware('abilities:master-data:delete')
            ->name('api.v1.master-data.restore');

        Route::get('/master-data-types', [MasterDataTypeController::class, 'index'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master-data-types.index');
        Route::get('/master-data-types/{masterDataType:code}', [MasterDataTypeController::class, 'show'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master-data-types.show');
        Route::get('/master-data-types/{masterDataType:code}/records', [MasterDataTypeController::class, 'records'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master-data-types.records.index');
        Route::get('/master-data-types/{masterDataType:code}/records/{recordCode}', [MasterDataTypeController::class, 'record'])
            ->middleware('abilities:master-data:read')
            ->name('api.v1.master-data-types.records.show');

        Route::get('/import-mappings', [ImportMappingController::class, 'index'])
            ->middleware('abilities:imports:read')
            ->name('api.v1.import-mappings.index');
        Route::post('/import-mappings', [ImportMappingController::class, 'store'])
            ->middleware('abilities:imports:create')
            ->name('api.v1.import-mappings.store');
        Route::get('/import-mappings/{importMapping:uuid}', [ImportMappingController::class, 'show'])
            ->middleware('abilities:imports:read')
            ->name('api.v1.import-mappings.show');
        Route::put('/import-mappings/{importMapping:uuid}', [ImportMappingController::class, 'update'])
            ->middleware('abilities:imports:create')
            ->name('api.v1.import-mappings.update');
        Route::post('/import-mappings/preview', [ImportMappingController::class, 'preview'])
            ->middleware('abilities:imports:create')
            ->name('api.v1.import-mappings.preview');
    });
});

<?php

use App\Http\Controllers\Api\V1\BridgeController;
use App\Http\Controllers\Api\V1\BridgeSourceTableController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ImportMappingController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\MasterDataTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'summary'])->name('api.v1.health.summary');
    Route::get('/health/live', [HealthController::class, 'live'])->name('api.v1.health.live');
    Route::get('/health/ready', [HealthController::class, 'ready'])->name('api.v1.health.ready');

    Route::middleware(['auth:sanctum', 'api.actor'])->group(function (): void {
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

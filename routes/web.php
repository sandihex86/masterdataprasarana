<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\ApiDocsController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\OpenApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(Auth::check() ? '/dashboard' : '/login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/api/documentation', [ApiDocsController::class, 'swagger'])->name('api.documentation');

    Route::prefix('dashboard')->name('dashboard.')->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/documentation', [DashboardController::class, 'documentation'])->name('documentation');
        Route::get('/metadata/jembatan/fields/{field}/values', [DashboardController::class, 'bridgeMetadataFieldValues'])->name('metadata.jembatan.fields.values');
        Route::get('/menu-penting', [DashboardController::class, 'quickMenu'])->name('quick-menu');
        Route::get('/status-modul', [DashboardController::class, 'moduleStatus'])->name('module-status');
        Route::get('/master-data', [DashboardController::class, 'masterData'])->name('master-data');
        Route::get('/master-data/jembatan/source-records', [DashboardController::class, 'bridgeSourceRecords'])->name('bridge-source.records.index');
        Route::post('/master-data/jembatan/source-records', [DashboardController::class, 'storeBridgeSourceRecord'])->name('bridge-source.records.store');
        Route::get('/master-data/jembatan/source-records/{bridgeUniqid}', [DashboardController::class, 'bridgeSourceRecord'])->name('bridge-source.records.show');
        Route::match(['put', 'patch'], '/master-data/jembatan/source-records/{bridgeUniqid}', [DashboardController::class, 'updateBridgeSourceRecord'])->name('bridge-source.records.update');
        Route::delete('/master-data/jembatan/source-records/{bridgeUniqid}', [DashboardController::class, 'destroyBridgeSourceRecord'])->name('bridge-source.records.destroy');
        Route::get('/master-data/jembatan/tables/{table}', [DashboardController::class, 'bridgeSourceTable'])->name('bridge-source.tables.show');
        Route::get('/master-data/jembatan/tables/{table}/rows', [DashboardController::class, 'bridgeSourceTableRows'])->name('bridge-source.tables.rows');
        Route::get('/master-data/terowongan/source-records', [DashboardController::class, 'tunnelSourceRecords'])->name('tunnel-source.records.index');
        Route::post('/master-data/terowongan/source-records', [DashboardController::class, 'storeTunnelSourceRecord'])->name('tunnel-source.records.store');
        Route::post('/master-data/terowongan/import', [DashboardController::class, 'importTunnelSourceRecords'])->name('tunnel-source.import');
        Route::get('/master-data/terowongan/export', [DashboardController::class, 'exportTunnelSourceRecords'])->name('tunnel-source.export');
        Route::get('/master-data/terowongan/template', [DashboardController::class, 'tunnelCsvTemplate'])->name('tunnel-source.template');
        Route::match(['put', 'patch'], '/master-data/terowongan/source-records/{tunnel_id}', [DashboardController::class, 'updateTunnelSourceRecord'])->name('tunnel-source.records.update');
        Route::get('/master-data/terowongan/source-records/{tunnelId}', [DashboardController::class, 'tunnelSourceRecord'])->name('tunnel-source.records.show');
        Route::get('/master-data/terowongan/tables/{table}', [DashboardController::class, 'tunnelSourceTable'])->name('tunnel-source.tables.show');
        Route::get('/master-data/terowongan/tables/{table}/template', [DashboardController::class, 'tunnelSourceTableCsvTemplate'])->name('tunnel-source.tables.template');
        Route::post('/master-data/terowongan/tables/{table}/import', [DashboardController::class, 'importTunnelSourceTableRows'])->name('tunnel-source.tables.import');
        Route::get('/master-data/terowongan/tables/{table}/export', [DashboardController::class, 'exportTunnelSourceTableRows'])->name('tunnel-source.tables.export');
        Route::get('/master-data/terowongan/tables/{table}/rows', [DashboardController::class, 'tunnelSourceTableRows'])->name('tunnel-source.tables.rows');
        Route::post('/master-data/terowongan/tables/{table}/rows', [DashboardController::class, 'storeTunnelSourceTableRow'])->name('tunnel-source.tables.rows.store');
        Route::match(['put', 'patch'], '/master-data/terowongan/tables/{table}/rows/{rowKey}', [DashboardController::class, 'updateTunnelSourceTableRow'])->name('tunnel-source.tables.rows.update');
        Route::get('/master-data/{entity}/records', [DashboardController::class, 'masterDataRecords'])->name('master-data.records');
        Route::post('/master-data/{entity}/records', [DashboardController::class, 'storeMasterDataRecord'])->name('master-data.records.store');
        Route::get('/master-data/{entity}/records/{masterData}', [DashboardController::class, 'masterDataRecord'])->name('master-data.record');
        Route::match(['put', 'patch'], '/master-data/{entity}/records/{masterData}', [DashboardController::class, 'updateMasterDataRecord'])->name('master-data.records.update');
        Route::get('/master-data/{entity}', [DashboardController::class, 'masterDataEntity'])->name('master-data.entity');
        Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');
        Route::get('/superadmin/users', [DashboardController::class, 'superadminUsers'])->middleware('role:superadmin')->name('superadmin.users');
        Route::get('/superadmin/users/records', [DashboardController::class, 'superadminUserRecords'])->middleware('role:superadmin')->name('superadmin.users.records.index');
        Route::post('/superadmin/users/records', [DashboardController::class, 'storeSuperadminUserRecord'])->middleware('role:superadmin')->name('superadmin.users.records.store');
        Route::get('/superadmin/users/records/{user}', [DashboardController::class, 'superadminUserRecord'])->middleware('role:superadmin')->name('superadmin.users.records.show');
        Route::match(['put', 'patch'], '/superadmin/users/records/{user}', [DashboardController::class, 'updateSuperadminUserRecord'])->middleware('role:superadmin')->name('superadmin.users.records.update');
        Route::delete('/superadmin/users/records/{user}', [DashboardController::class, 'destroySuperadminUserRecord'])->middleware('role:superadmin')->name('superadmin.users.records.destroy');
        Route::get('/superadmin/api-clients', [DashboardController::class, 'superadminApiClients'])->middleware('role:superadmin')->name('superadmin.api-clients');
        Route::get('/superadmin/api-clients/records', [DashboardController::class, 'superadminApiClientRecords'])->middleware('role:superadmin')->name('superadmin.api-clients.records.index');
        Route::post('/superadmin/api-clients/records', [DashboardController::class, 'storeSuperadminApiClientRecord'])->middleware('role:superadmin')->name('superadmin.api-clients.records.store');
        Route::get('/superadmin/api-clients/records/{apiClient}', [DashboardController::class, 'superadminApiClientRecord'])->middleware('role:superadmin')->name('superadmin.api-clients.records.show');
        Route::match(['put', 'patch'], '/superadmin/api-clients/records/{apiClient}', [DashboardController::class, 'updateSuperadminApiClientRecord'])->middleware('role:superadmin')->name('superadmin.api-clients.records.update');
        Route::delete('/superadmin/api-clients/records/{apiClient}', [DashboardController::class, 'destroySuperadminApiClientRecord'])->middleware('role:superadmin')->name('superadmin.api-clients.records.destroy');
        Route::post('/superadmin/api-clients/records/{apiClient}/tokens', [DashboardController::class, 'generateSuperadminApiClientToken'])->middleware('role:superadmin')->name('superadmin.api-clients.tokens.store');
        Route::get('/system', [DashboardController::class, 'system'])->middleware('role:superadmin')->name('system');
    });

    Route::get('/docs/openapi', [OpenApiController::class, 'spec'])->name('docs.openapi');
    Route::get('/docs/swagger', [ApiDocsController::class, 'swagger'])->name('docs.swagger');
});

Route::get('/docs/openapi/asset/{asset}', [OpenApiController::class, 'asset'])
    ->where('asset', '.*')
    ->name('docs.openapi.asset');

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMasterDataRequest;
use App\Http\Requests\Api\V1\StoreMasterDataRequest;
use App\Http\Requests\Api\V1\UpdateMasterDataRequest;
use App\Http\Resources\Api\V1\MasterDataResource;
use App\Models\MasterData;
use App\Services\MasterData\MasterDataQueryService;
use App\Services\MasterData\MasterDataWriteService;
use App\Support\ApiResponse;

class MasterDataController extends Controller
{
    public function __construct(
        private readonly MasterDataQueryService $queryService,
        private readonly MasterDataWriteService $writeService,
    ) {}

    public function index(ListMasterDataRequest $request)
    {
        $this->authorize('viewAny', MasterData::class);

        $records = $this->queryService->paginate($request);

        return ApiResponse::paginated(
            'Data berhasil diambil.',
            MasterDataResource::collection($records->getCollection())->resolve(),
            $records,
        );
    }

    public function show(MasterData $masterData)
    {
        $this->authorize('view', $masterData);
        $masterData->load('type');

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataResource::make($masterData)->resolve(),
        );
    }

    public function store(StoreMasterDataRequest $request)
    {
        $this->authorize('create', MasterData::class);

        $record = $this->writeService->create($request->validated());

        return ApiResponse::success(
            'Data berhasil dibuat.',
            MasterDataResource::make($record)->resolve(),
            status: 201,
        );
    }

    public function update(UpdateMasterDataRequest $request, MasterData $masterData)
    {
        $this->authorize('update', $masterData);

        $record = $this->writeService->update($masterData, $request->validated());

        return ApiResponse::success(
            'Data berhasil diperbarui.',
            MasterDataResource::make($record)->resolve(),
        );
    }

    public function destroy(MasterData $masterData)
    {
        $this->authorize('delete', $masterData);

        $this->writeService->delete($masterData);

        return ApiResponse::success('Data berhasil dihapus.');
    }

    public function restore(string $masterDataUuid)
    {
        $record = MasterData::withTrashed()->where('uuid', $masterDataUuid)->firstOrFail();
        $this->authorize('restore', $record);

        $record = $this->writeService->restore($record);

        return ApiResponse::success(
            'Data berhasil dipulihkan.',
            MasterDataResource::make($record)->resolve(),
        );
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMasterDataRequest;
use App\Http\Resources\Api\V1\MasterDataResource;
use App\Http\Resources\Api\V1\MasterDataTypeResource;
use App\Models\MasterData;
use App\Models\MasterDataType;
use App\Services\MasterData\MasterDataQueryService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MasterDataTypeController extends Controller
{
    public function __construct(
        private readonly MasterDataQueryService $queryService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', MasterDataType::class);

        $types = MasterDataType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataTypeResource::collection($types)->resolve(),
        );
    }

    public function show(MasterDataType $masterDataType)
    {
        $this->authorize('view', $masterDataType);

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataTypeResource::make($masterDataType)->resolve(),
        );
    }

    public function records(ListMasterDataRequest $request, MasterDataType $masterDataType)
    {
        $this->authorize('view', $masterDataType);

        $request->merge(['type' => $masterDataType->code]);
        $records = $this->queryService->paginate($request);

        return ApiResponse::paginated(
            'Data berhasil diambil.',
            MasterDataResource::collection($records->getCollection())->resolve(),
            $records,
        );
    }

    public function record(Request $request, MasterDataType $masterDataType, string $recordCode)
    {
        $this->authorize('view', $masterDataType);

        $record = MasterData::query()
            ->with('type')
            ->where('entity_type', $masterDataType->code)
            ->where('code', $recordCode)
            ->firstOrFail();

        return ApiResponse::success(
            'Data berhasil diambil.',
            MasterDataResource::make($record)->resolve(),
        );
    }
}

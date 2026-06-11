<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        return view('dashboard.index', [
            'overview' => $this->dashboardService->overview(),
        ]);
    }

    public function system(): JsonResponse
    {
        return response()->json($this->dashboardService->overview());
    }
}

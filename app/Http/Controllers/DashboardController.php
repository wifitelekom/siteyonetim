<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index()
    {
        $summary = $this->dashboardService->getSummary();

        return view('dashboard.index', $summary);
    }
}

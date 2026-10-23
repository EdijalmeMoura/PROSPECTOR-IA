<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardMetrics;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardMetrics $metrics): View
    {
        return view('dashboard.index', ['metrics' => $metrics->build()]);
    }
}

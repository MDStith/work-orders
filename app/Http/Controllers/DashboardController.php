<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $counts = WorkOrder::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $open = (int) ($counts->get('open') ?? 0);
        $inProgress = (int) ($counts->get('in_progress') ?? 0);
        $completed = (int) ($counts->get('completed') ?? 0);

        return Inertia::render('dashboard', [
            'stats' => [
                'total' => $open + $inProgress + $completed,
                'open' => $open,
                'in_progress' => $inProgress,
                'completed' => $completed,
            ],
        ]);
    }
}

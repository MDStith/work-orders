<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate(
            [
                'status' => ['nullable', 'in:open,in_progress,completed'],
            ]
        );

        $status = $validated['status'] ?? null;

        $query = WorkOrder::query();

        if ($status) {
            $query->where('status', $status);
        }

        return Inertia::render('work-orders/index', [
            'workOrders' => $query->orderByDesc('created_at')->get(),
            'filters' => [
                'status' => $status ?? '',
            ],
        ]);
    }
}

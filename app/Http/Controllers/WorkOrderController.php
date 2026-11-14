<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

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

    public function update(Request $request, WorkOrder $workOrder)
    {

        Gate::authorize('update', $workOrder);

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,completed'],
        ]);

        $workOrder->update([
            'status' => $validated['status'],
        ]);

        return to_route('work-orders.index');

    }
}

<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
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
            'workOrders' => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(10)
                ->appends($status ? ['status' => $status] : []),
            'filters' => [
                'status' => $status ?? '',
            ],
            'canUpdateWorkOrders' => $request->user()->isDispatcher(),
            'canCreateWorkOrders' => Gate::allows('create', WorkOrder::class),
        ]);
    }

    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('update', $workOrder);

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,completed'],
            'filter_status' => ['nullable', 'in:open,in_progress,completed'],
        ]);

        $workOrder->update([
            'status' => $validated['status'],
        ]);

        $filterStatus = $validated['filter_status'] ?? null;

        return to_route(
            'work-orders.index',
            $filterStatus ? ['status' => $filterStatus] : [],
        );
    }

    public function create(): Response
    {
        Gate::authorize('create', WorkOrder::class);

        return Inertia::render('work-orders/create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', WorkOrder::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
        ]);

        WorkOrder::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => 'open',
        ]);

        return to_route('work-orders.index');
    }
}

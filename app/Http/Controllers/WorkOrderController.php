<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:open,in_progress,completed'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $validated['status'] ?? null;
        $search = trim($validated['search'] ?? '');

        $query = WorkOrder::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $pattern = '%'.strtr($search, [
                '!' => '!!',
                '%' => '!%',
                '_' => '!_',
            ]).'%';

            $query->where(function (Builder $query) use ($pattern) {
                $query->whereRaw("title LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("description LIKE ? ESCAPE '!'", [$pattern]);
            });
        }

        $queryParameters = [];

        if ($status) {
            $queryParameters['status'] = $status;
        }

        if ($search !== '') {
            $queryParameters['search'] = $search;
        }

        return Inertia::render('work-orders/index', [
            'workOrders' => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(10)
                ->appends($queryParameters),
            'filters' => [
                'status' => $status ?? '',
                'search' => $search,
            ],
            'canUpdateWorkOrders' => $request->user()->isDispatcher(),
            'canCreateWorkOrders' => Gate::allows('create', WorkOrder::class),
            'canDeleteWorkOrders' => $request->user()->isDispatcher(),
        ]);
    }

    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('update', $workOrder);

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,completed'],
            'filter_status' => ['nullable', 'in:open,in_progress,completed'],
            'filter_search' => ['nullable', 'string', 'max:100'],
        ]);

        $workOrder->update([
            'status' => $validated['status'],
        ]);

        $filterStatus = $validated['filter_status'] ?? null;
        $filterSearch = trim($validated['filter_search'] ?? '');

        $queryParameters = [];

        if ($filterStatus) {
            $queryParameters['status'] = $filterStatus;
        }

        if ($filterSearch !== '') {
            $queryParameters['search'] = $filterSearch;
        }

        Inertia::flash('success', 'Work order status updated.');

        return to_route('work-orders.index', $queryParameters);
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

        Inertia::flash('success', 'Work order created.');

        return to_route('work-orders.index');
    }

    public function edit(WorkOrder $workOrder): Response
    {
        Gate::authorize('update', $workOrder);

        return Inertia::render('work-orders/edit', [
            'workOrder' => [
                'id' => $workOrder->id,
                'title' => $workOrder->title,
                'description' => $workOrder->description,
            ],
        ]);
    }

    public function updateDetails(
        Request $request,
        WorkOrder $workOrder,
    ): RedirectResponse {
        Gate::authorize('update', $workOrder);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
        ]);

        $workOrder->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        Inertia::flash('success', 'Work order details updated.');

        return to_route('work-orders.index');
    }

    public function destroy(
        Request $request,
        WorkOrder $workOrder,
    ): RedirectResponse {
        Gate::authorize('delete', $workOrder);

        $validated = $request->validate([
            'filter_status' => ['nullable', 'in:open,in_progress,completed'],
            'filter_search' => ['nullable', 'string', 'max:100'],
        ]);

        $filterStatus = $validated['filter_status'] ?? null;
        $filterSearch = trim($validated['filter_search'] ?? '');

        $queryParameters = [];

        if ($filterStatus) {
            $queryParameters['status'] = $filterStatus;
        }

        if ($filterSearch !== '') {
            $queryParameters['search'] = $filterSearch;
        }

        $workOrder->delete();

        Inertia::flash('success', 'Work order deleted.');

        return to_route('work-orders.index', $queryParameters);
    }
}

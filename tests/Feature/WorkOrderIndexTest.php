<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkOrderIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/work-orders');

        $response->assertRedirect('/login');
    }

    public function test_viewers_can_view_work_orders(): void
    {
        $user = User::factory()->create();

        $order = WorkOrder::create([
            'title' => 'Repair loading dock door',
            'description' => 'Door does not close completely.',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->get('/work-orders');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->has('workOrders', 1)
            ->where('workOrders.0.id', $order->id)
            ->where('filters.status', '')
            ->where('canUpdateWorkOrders', false)
        );
    }

    public function test_work_orders_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();

        $openOrder = WorkOrder::create([
            'title' => 'Repair loading dock door',
            'description' => 'Door does not close completely.',
            'status' => 'open',
        ]);

        WorkOrder::create([
            'title' => 'Replace office light',
            'description' => 'Light has been replaced.',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)
            ->get('/work-orders?status=open');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->has('workOrders', 1)
            ->where('workOrders.0.id', $openOrder->id)
            ->where('filters.status', 'open')
        );
    }

    public function test_invalid_status_filter_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/work-orders')
            ->get('/work-orders?status=invalid');

        $response->assertRedirect('/work-orders');
        $response->assertSessionHasErrors('status');
    }

    public function test_dispatchers_receive_update_permission(): void
    {
        $user = User::factory()->create();

        $user->role = 'dispatcher';
        $user->save();

        $response = $this->actingAs($user)->get('/work-orders');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->where('canUpdateWorkOrders', true)
        );
    }

    public function test_listing_can_be_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/work-orders');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->has('workOrders', 0)
            ->where('filters.status', '')
        );
    }

    public function test_unfiltered_listing_returns_all_statuses_newest_first(): void
    {
        $user = User::factory()->create();

        $oldestOrder = WorkOrder::create([
            'title' => 'Repair loading dock door',
            'description' => 'Door does not close completely.',
            'status' => 'open',
        ]);

        $middleOrder = WorkOrder::create([
            'title' => 'Inspect warehouse heater',
            'description' => 'Inspection is underway.',
            'status' => 'in_progress',
        ]);

        $newestOrder = WorkOrder::create([
            'title' => 'Replace office light',
            'description' => 'Light has been replaced.',
            'status' => 'completed',
        ]);

        $oldestOrder->created_at = now()->subDays(2);
        $oldestOrder->save();

        $middleOrder->created_at = now()->subDay();
        $middleOrder->save();

        $newestOrder->created_at = now();
        $newestOrder->save();

        $response = $this->actingAs($user)->get('/work-orders');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->has('workOrders', 3)
            ->where('workOrders.0.id', $newestOrder->id)
            ->where('workOrders.1.id', $middleOrder->id)
            ->where('workOrders.2.id', $oldestOrder->id)
            ->where('filters.status', '')
        );
    }

    public function test_remaining_valid_status_filters_return_matching_orders(): void
    {
        $user = User::factory()->create();

        WorkOrder::create([
            'title' => 'Repair loading dock door',
            'description' => 'Door does not close completely.',
            'status' => 'open',
        ]);

        $inProgressOrder = WorkOrder::create([
            'title' => 'Inspect warehouse heater',
            'description' => 'Inspection is underway.',
            'status' => 'in_progress',
        ]);

        $completedOrder = WorkOrder::create([
            'title' => 'Replace office light',
            'description' => 'Light has been replaced.',
            'status' => 'completed',
        ]);

        foreach ([$inProgressOrder, $completedOrder] as $order) {
            $response = $this->actingAs($user)
                ->get('/work-orders?status='.$order->status);

            $response->assertOk();

            $response->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/index')
                ->has('workOrders', 1)
                ->where('workOrders.0.id', $order->id)
                ->where('filters.status', $order->status)
            );
        }
    }

    public function test_status_filter_can_return_no_matching_orders(): void
    {
        $user = User::factory()->create();

        WorkOrder::create([
            'title' => 'Repair loading dock door',
            'description' => 'Door does not close completely.',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)
            ->get('/work-orders?status=completed');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->has('workOrders', 0)
            ->where('filters.status', 'completed')
        );
    }
}

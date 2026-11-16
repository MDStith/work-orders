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
            ->has('workOrders.data', 1)
            ->where('workOrders.data.0.id', $order->id)
            ->where('filters.status', '')
            ->where('canUpdateWorkOrders', false)
            ->where('canCreateWorkOrders', false)
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
            ->has('workOrders.data', 1)
            ->where('workOrders.data.0.id', $openOrder->id)
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

    public function test_dispatchers_receive_work_order_permissions(): void
    {
        $user = User::factory()->create();

        $user->role = 'dispatcher';
        $user->save();

        $response = $this->actingAs($user)->get('/work-orders');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->where('canUpdateWorkOrders', true)
            ->where('canCreateWorkOrders', true)
        );
    }

    public function test_listing_can_be_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/work-orders');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->has('workOrders.data', 0)
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
            ->has('workOrders.data', 3)
            ->where('workOrders.data.0.id', $newestOrder->id)
            ->where('workOrders.data.1.id', $middleOrder->id)
            ->where('workOrders.data.2.id', $oldestOrder->id)
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
                ->has('workOrders.data', 1)
                ->where('workOrders.data.0.id', $order->id)
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
            ->has('workOrders.data', 0)
            ->where('filters.status', 'completed')
        );
    }

    public function test_work_orders_are_paginated_with_consistent_ordering(): void
    {
        $user = User::factory()->create();
        $createdAt = now();
        $orders = [];

        for ($number = 1; $number <= 12; $number++) {
            $order = WorkOrder::create([
                'title' => 'Pagination order '.$number,
                'description' => 'Testing pagination.',
                'status' => 'open',
            ]);

            $order->created_at = $createdAt;
            $order->save();

            $orders[] = $order;
        }

        $this->actingAs($user)
            ->get('/work-orders')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/index')
                ->has('workOrders.data', 10)
                ->where('workOrders.total', 12)
                ->where('workOrders.per_page', 10)
                ->where('workOrders.current_page', 1)
                ->where('workOrders.last_page', 2)
                ->where('workOrders.prev_page_url', null)
                ->where('workOrders.data.0.id', $orders[11]->id)
                ->where('workOrders.data.9.id', $orders[2]->id)
            );

        $this->get('/work-orders?page=2')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/index')
                ->has('workOrders.data', 2)
                ->where('workOrders.current_page', 2)
                ->where('workOrders.next_page_url', null)
                ->where('workOrders.data.0.id', $orders[1]->id)
                ->where('workOrders.data.1.id', $orders[0]->id)
            );
    }

    public function test_pagination_links_preserve_status_filter(): void
    {
        $user = User::factory()->create();

        for ($number = 1; $number <= 11; $number++) {
            WorkOrder::create([
                'title' => 'Open order '.$number,
                'description' => 'Testing filtered pagination.',
                'status' => 'open',
            ]);
        }

        WorkOrder::create([
            'title' => 'Completed order',
            'description' => 'Excluded from the open listing.',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)
            ->get('/work-orders?status=open');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('work-orders/index')
            ->has('workOrders.data', 10)
            ->where('workOrders.total', 11)
            ->where('filters.status', 'open')
            ->where(
                'workOrders.next_page_url',
                fn ($url) => is_string($url)
                    && str_contains($url, 'status=open')
                    && str_contains($url, 'page=2'),
            )
        );

        $this->get('/work-orders?status=open&page=2')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/index')
                ->has('workOrders.data', 1)
                ->where('workOrders.data.0.status', 'open')
                ->where('workOrders.total', 11)
                ->where('workOrders.current_page', 2)
                ->where('filters.status', 'open')
                ->where(
                    'workOrders.prev_page_url',
                    fn ($url) => is_string($url)
                        && str_contains($url, 'status=open')
                        && str_contains($url, 'page=1'),
                )
            );
    }
}

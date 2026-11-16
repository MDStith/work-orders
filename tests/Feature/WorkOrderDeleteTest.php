<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkOrderDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function createDispatcher(): User
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        return $user;
    }

    private function createWorkOrder(): WorkOrder
    {
        return WorkOrder::create([
            'title' => 'Repair faucet',
            'description' => 'The faucet is leaking.',
            'status' => 'in_progress',
        ]);
    }

    public function test_guests_cannot_delete_orders(): void
    {
        $order = $this->createWorkOrder();

        $this->delete(route('work-orders.destroy', $order))
            ->assertRedirect('/login');

        $this->assertDatabaseHas('work_orders', [
            'id' => $order->id,
        ]);
    }

    public function test_viewers_cannot_delete_orders(): void
    {
        $user = User::factory()->create();
        $order = $this->createWorkOrder();

        $this->actingAs($user)
            ->delete(route('work-orders.destroy', $order))
            ->assertForbidden();

        $this->assertDatabaseHas('work_orders', [
            'id' => $order->id,
        ]);
    }

    public function test_dispatcher_can_delete_only_the_selected_order(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();
        $otherOrder = $this->createWorkOrder();

        $this->actingAs($user)
            ->delete(route('work-orders.destroy', $order))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('work-orders.index'));

        $this->assertDatabaseMissing('work_orders', [
            'id' => $order->id,
        ]);

        $this->assertDatabaseHas('work_orders', [
            'id' => $otherOrder->id,
        ]);
    }

    public function test_deletion_preserves_filters_and_returns_to_page_one(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();

        $this->actingAs($user)
            ->delete(route('work-orders.destroy', $order), [
                'filter_status' => 'in_progress',
                'filter_search' => '  faucet  ',
                'page' => 2,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('work-orders.index', [
                'status' => 'in_progress',
                'search' => 'faucet',
            ]));

        $this->assertDatabaseMissing('work_orders', [
            'id' => $order->id,
        ]);
    }

    public function test_zero_search_is_preserved(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();

        $this->actingAs($user)
            ->delete(route('work-orders.destroy', $order), [
                'filter_search' => '0',
            ])
            ->assertRedirect(route('work-orders.index', [
                'search' => '0',
            ]));
    }

    public function test_invalid_filters_prevent_deletion(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();

        $invalidInputs = [
            [
                'filter_status' => 'invalid',
                'filter_search' => str_repeat('a', 101),
            ],
            [
                'filter_status' => ['invalid'],
                'filter_search' => ['invalid'],
            ],
        ];

        foreach ($invalidInputs as $input) {
            $this->actingAs($user)
                ->from(route('work-orders.index'))
                ->delete(route('work-orders.destroy', $order), $input)
                ->assertRedirect(route('work-orders.index'))
                ->assertSessionHasErrors([
                    'filter_status',
                    'filter_search',
                ]);

            $this->assertDatabaseHas('work_orders', [
                'id' => $order->id,
            ]);
        }
    }

    public function test_missing_order_returns_not_found(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();
        $id = $order->id;
        $order->delete();

        $this->actingAs($user)
            ->delete(route('work-orders.destroy', $id))
            ->assertNotFound();
    }

    public function test_listing_exposes_delete_permission_for_each_role(): void
    {
        $viewer = User::factory()->create();
        $dispatcher = $this->createDispatcher();

        $this->actingAs($viewer)
            ->get(route('work-orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/index')
                ->where('canDeleteWorkOrders', false)
            );

        $this->actingAs($dispatcher)
            ->get(route('work-orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/index')
                ->where('canDeleteWorkOrders', true)
            );
    }
}

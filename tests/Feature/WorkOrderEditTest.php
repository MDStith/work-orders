<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkOrderEditTest extends TestCase
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

    public function test_guests_cannot_access_edit_or_save_changes(): void
    {
        $order = $this->createWorkOrder();

        $this->get(route('work-orders.edit', $order))
            ->assertRedirect('/login');

        $this->put(route('work-orders.update-details', $order), [
            'title' => 'Changed title',
            'description' => 'Changed description.',
        ])->assertRedirect('/login');

        $this->assertDatabaseHas('work_orders', [
            'id' => $order->id,
            'title' => 'Repair faucet',
            'description' => 'The faucet is leaking.',
        ]);
    }

    public function test_viewers_cannot_access_edit_or_save_changes(): void
    {
        $user = User::factory()->create();
        $order = $this->createWorkOrder();

        $this->actingAs($user)
            ->get(route('work-orders.edit', $order))
            ->assertForbidden();

        $this->put(route('work-orders.update-details', $order), [
            'title' => 'Changed title',
            'description' => 'Changed description.',
        ])->assertForbidden();

        $this->assertDatabaseHas('work_orders', [
            'id' => $order->id,
            'title' => 'Repair faucet',
            'description' => 'The faucet is leaking.',
        ]);
    }

    public function test_dispatcher_receives_existing_values(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();

        $this->actingAs($user)
            ->get(route('work-orders.edit', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/edit')
                ->where('workOrder.id', $order->id)
                ->where('workOrder.title', $order->title)
                ->where('workOrder.description', $order->description)
            );
    }

    public function test_dispatcher_can_edit_details_without_changing_status(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();

        $this->actingAs($user)
            ->put(route('work-orders.update-details', $order), [
                'title' => 'Replace kitchen faucet',
                'description' => 'Install a replacement faucet.',
                'status' => 'completed',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('work-orders.index'));

        $this->assertDatabaseHas('work_orders', [
            'id' => $order->id,
            'title' => 'Replace kitchen faucet',
            'description' => 'Install a replacement faucet.',
            'status' => 'in_progress',
        ]);
    }

    public function test_invalid_details_are_rejected_without_changing_order(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();

        $invalidInputs = [
            ['title' => '', 'description' => ''],
            [
                'title' => str_repeat('a', 256),
                'description' => str_repeat('a', 10001),
            ],
            ['title' => ['invalid'], 'description' => ['invalid']],
        ];

        foreach ($invalidInputs as $input) {
            $this->actingAs($user)
                ->from(route('work-orders.edit', $order))
                ->put(route('work-orders.update-details', $order), $input)
                ->assertRedirect(route('work-orders.edit', $order))
                ->assertSessionHasErrors(['title', 'description']);

            $this->assertDatabaseHas('work_orders', [
                'id' => $order->id,
                'title' => 'Repair faucet',
                'description' => 'The faucet is leaking.',
                'status' => 'in_progress',
            ]);
        }
    }

    public function test_valid_maximum_lengths_are_accepted(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();

        $title = str_repeat('a', 255);
        $description = str_repeat('a', 10000);

        $this->actingAs($user)
            ->put(route('work-orders.update-details', $order), [
                'title' => $title,
                'description' => $description,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('work-orders.index'));

        $this->assertDatabaseHas('work_orders', [
            'id' => $order->id,
            'title' => $title,
            'description' => $description,
        ]);
    }

    public function test_missing_orders_return_not_found(): void
    {
        $user = $this->createDispatcher();
        $order = $this->createWorkOrder();
        $id = $order->id;
        $order->delete();

        $this->actingAs($user)
            ->get(route('work-orders.edit', $id))
            ->assertNotFound();

        $this->put(route('work-orders.update-details', $id), [
            'title' => 'Changed title',
            'description' => 'Changed description.',
        ])->assertNotFound();
    }
}

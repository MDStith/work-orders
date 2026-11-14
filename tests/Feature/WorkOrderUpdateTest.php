<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatcher_can_update_work_order_status(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $workOrder = WorkOrder::create([
            'title' => 'Repair faucet',
            'description' => 'The faucet is leaking.',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->patch(
            route('work-orders.update', $workOrder),
            ['status' => 'in_progress'],
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('work-orders.index'));

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_viewer_cannot_update_work_order_status(): void
    {
        $user = User::factory()->create();

        $workOrder = WorkOrder::create([
            'title' => 'Repair faucet',
            'description' => 'The faucet is leaking.',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->patch(
            route('work-orders.update', $workOrder),
            ['status' => 'completed'],
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'status' => 'open',
        ]);
    }

    public function test_dispatcher_cannot_save_invalid_status(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $workOrder = WorkOrder::create([
            'title' => 'Repair faucet',
            'description' => 'The faucet is leaking.',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->patch(
            route('work-orders.update', $workOrder),
            ['status' => 'banana'],
        );

        $response->assertSessionHasErrors(['status']);

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'status' => 'open',
        ]);
    }
}

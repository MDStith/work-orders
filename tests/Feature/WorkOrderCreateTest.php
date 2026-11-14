<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkOrderCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_creation(): void
    {
        $this->get('/work-orders/create')
            ->assertRedirect('/login');

        $this->post('/work-orders', [
            'title' => 'Repair door',
            'description' => 'Door is stuck.',
        ])->assertRedirect('/login');

        $this->assertDatabaseCount('work_orders', 0);
    }

    public function test_viewers_cannot_access_creation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/work-orders/create')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/work-orders', [
                'title' => 'Repair door',
                'description' => 'Door is stuck.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('work_orders', 0);
    }

    public function test_dispatchers_can_view_creation_form(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $this->actingAs($user)
            ->get('/work-orders/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/create')
            );
    }

    public function test_dispatchers_can_create_open_work_orders(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $this->actingAs($user)
            ->post('/work-orders', [
                'title' => 'Repair door',
                'description' => 'Door is stuck.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/work-orders');

        $this->assertDatabaseCount('work_orders', 1);

        $this->assertDatabaseHas('work_orders', [
            'title' => 'Repair door',
            'description' => 'Door is stuck.',
            'status' => 'open',
        ]);
    }

    public function test_submitted_status_cannot_override_open_status(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $this->actingAs($user)
            ->post('/work-orders', [
                'title' => 'Repair door',
                'description' => 'Door is stuck.',
                'status' => 'completed',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/work-orders');

        $this->assertDatabaseCount('work_orders', 1);

        $this->assertDatabaseHas('work_orders', [
            'title' => 'Repair door',
            'status' => 'open',
        ]);
    }

    public function test_creation_requires_title_and_description(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $this->actingAs($user)
            ->from('/work-orders/create')
            ->post('/work-orders', [])
            ->assertRedirect('/work-orders/create')
            ->assertSessionHasErrors(['title', 'description']);

        $this->assertDatabaseCount('work_orders', 0);
    }

    public function test_creation_rejects_overlong_fields(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $this->actingAs($user)
            ->from('/work-orders/create')
            ->post('/work-orders', [
                'title' => str_repeat('a', 256),
                'description' => str_repeat('a', 10001),
            ])
            ->assertRedirect('/work-orders/create')
            ->assertSessionHasErrors(['title', 'description']);

        $this->assertDatabaseCount('work_orders', 0);
    }

    public function test_creation_rejects_non_string_fields(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $this->actingAs($user)
            ->post('/work-orders', [
                'title' => ['invalid'],
                'description' => ['invalid'],
            ])
            ->assertSessionHasErrors(['title', 'description']);

        $this->assertDatabaseCount('work_orders', 0);
    }
}

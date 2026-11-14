<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_shows_work_order_counts_to_both_roles(): void
    {
        foreach (['open', 'open', 'in_progress', 'completed'] as $index => $status) {
            WorkOrder::create([
                'title' => 'Work order '.$index,
                'description' => 'Dashboard count test.',
                'status' => $status,
            ]);
        }

        foreach (['viewer', 'dispatcher'] as $role) {
            $user = User::factory()->create();
            $user->role = $role;
            $user->save();

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('dashboard')
                    ->where('stats.total', 4)
                    ->where('stats.open', 2)
                    ->where('stats.in_progress', 1)
                    ->where('stats.completed', 1)
                );
        }
    }

    public function test_dashboard_shows_zero_counts_without_work_orders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('stats.total', 0)
                ->where('stats.open', 0)
                ->where('stats.in_progress', 0)
                ->where('stats.completed', 0)
            );
    }

    public function test_dashboard_shows_zero_for_statuses_without_orders(): void
    {
        $user = User::factory()->create();

        WorkOrder::create([
            'title' => 'Repair faucet',
            'description' => 'The faucet is leaking.',
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('stats.total', 1)
                ->where('stats.open', 1)
                ->where('stats.in_progress', 0)
                ->where('stats.completed', 0)
            );
    }
}

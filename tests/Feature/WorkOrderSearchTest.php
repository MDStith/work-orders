<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkOrderSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_title_or_description_with_status_filter(): void
    {
        $user = User::factory()->create();

        $titleMatch = WorkOrder::create([
            'title' => 'Repair faucet',
            'description' => 'Kitchen maintenance.',
            'status' => 'open',
        ]);

        $descriptionMatch = WorkOrder::create([
            'title' => 'Inspect kitchen',
            'description' => 'Check the faucet.',
            'status' => 'open',
        ]);

        WorkOrder::create([
            'title' => 'Completed repair',
            'description' => 'The faucet is fixed.',
            'status' => 'completed',
        ]);

        WorkOrder::create([
            'title' => 'Replace light',
            'description' => 'Office maintenance.',
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get(route('work-orders.index', [
                'status' => 'open',
                'search' => ' faucet ',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('work-orders/index')
                ->has('workOrders.data', 2)
                ->where('workOrders.total', 2)
                ->where(
                    'workOrders.data',
                    fn ($orders) => collect($orders)->pluck('id')->sort()->values()->all()
                        === collect([$titleMatch->id, $descriptionMatch->id])->sort()->values()->all(),
                )
                ->where('filters.status', 'open')
                ->where('filters.search', 'faucet')
            );
    }

    public function test_search_can_return_no_results(): void
    {
        $user = User::factory()->create();

        WorkOrder::create([
            'title' => 'Replace light',
            'description' => 'Office maintenance.',
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get('/work-orders?search=faucet')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('workOrders.data', 0)
                ->where('workOrders.total', 0)
                ->where('filters.search', 'faucet')
            );
    }

    public function test_search_validates_type_and_length(): void
    {
        $user = User::factory()->create();

        foreach ([['invalid'], str_repeat('a', 101)] as $search) {
            $this->actingAs($user)
                ->from('/work-orders')
                ->get(route('work-orders.index', ['search' => $search]))
                ->assertRedirect('/work-orders')
                ->assertSessionHasErrors('search');
        }
    }

    public function test_search_handles_literal_wildcards_and_zero(): void
    {
        $user = User::factory()->create();

        $literalOrder = WorkOrder::create([
            'title' => 'Check 50%_! valve',
            'description' => 'Inspect unit 0.',
            'status' => 'open',
        ]);

        WorkOrder::create([
            'title' => 'Check 500XX valve',
            'description' => 'Unrelated maintenance.',
            'status' => 'open',
        ]);

        foreach (['50%_!', '0'] as $search) {
            $this->actingAs($user)
                ->get(route('work-orders.index', ['search' => $search]))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->where('filters.search', $search)
                    ->where(
                        'workOrders.data',
                        fn ($orders) => collect($orders)->contains('id', $literalOrder->id),
                    )
                );
        }

        $this->get(route('work-orders.index', ['search' => '50%_!']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('workOrders.data', 1)
            );
    }

    public function test_search_and_status_are_preserved_in_pagination(): void
    {
        $user = User::factory()->create();

        for ($number = 1; $number <= 11; $number++) {
            WorkOrder::create([
                'title' => 'Repair faucet '.$number,
                'description' => 'Kitchen maintenance.',
                'status' => 'open',
            ]);
        }

        WorkOrder::create([
            'title' => 'Repair faucet completed',
            'description' => 'Finished.',
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get('/work-orders?status=open&search=faucet')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('workOrders.data', 10)
                ->where('workOrders.total', 11)
                ->where(
                    'workOrders.next_page_url',
                    fn ($url) => is_string($url)
                        && str_contains($url, 'status=open')
                        && str_contains($url, 'search=faucet')
                        && str_contains($url, 'page=2'),
                )
            );

        $this->get('/work-orders?status=open&search=faucet&page=2')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('workOrders.data', 1)
                ->where('workOrders.total', 11)
                ->where('filters.status', 'open')
                ->where('filters.search', 'faucet')
            );
    }

    public function test_status_update_preserves_search_and_status_filter(): void
    {
        $user = User::factory()->create();
        $user->role = 'dispatcher';
        $user->save();

        $order = WorkOrder::create([
            'title' => 'Repair faucet',
            'description' => 'Kitchen maintenance.',
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->patch(route('work-orders.update', $order), [
                'status' => 'in_progress',
                'filter_status' => 'open',
                'filter_search' => 'faucet',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('work-orders.index', [
                'status' => 'open',
                'search' => 'faucet',
            ]));

        $this->assertDatabaseHas('work_orders', [
            'id' => $order->id,
            'status' => 'in_progress',
        ]);
    }
}

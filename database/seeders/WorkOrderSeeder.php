<?php

namespace Database\Seeders;

use App\Models\WorkOrder;
use Illuminate\Database\Seeder;

class WorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = [
            [
                'title' => 'Repair leaking faucet',
                'description' => 'The break room faucet drips constantly.',
                'status' => 'open',
            ],
            [
                'title' => 'Replace hallway light',
                'description' => 'The second floor hallway light is dim.',
                'status' => 'in_progress',
            ],
            [
                'title' => 'Fix the conference room door',
                'description' => "The conference room door doesn't latch.",
                'status' => 'completed',
            ],
        ];

        foreach ($orders as $order) {
            WorkOrder::updateOrCreate(
                ['title' => $order['title']],
                [
                    'description' => $order['description'],
                    'status' => $order['status'],
                ]
            );
        }
    }
}

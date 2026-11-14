<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class WorkOrderPolicyTest extends TestCase
{
    public function test_dispatcher_can_update_work_order(): void
    {
        $user = new User;
        $user->role = 'dispatcher';

        $workOrder = new WorkOrder;

        $this->assertTrue(
            Gate::forUser($user)->allows('update', $workOrder)
        );
    }

    public function test_viewer_cannot_update_work_order(): void
    {
        $user = new User;
        $user->role = 'viewer';

        $workOrder = new WorkOrder;

        $this->assertFalse(
            Gate::forUser($user)->allows('update', $workOrder)
        );
    }
}

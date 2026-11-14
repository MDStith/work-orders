<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_dispatcher_is_recognized(): void
    {
        $user = new User;
        $user->role = 'dispatcher';

        $this->assertTrue($user->isDispatcher());
    }

    public function test_viewer_is_not_a_dispatcher(): void
    {
        $user = new User;
        $user->role = 'viewer';

        $this->assertFalse($user->isDispatcher());
    }
}

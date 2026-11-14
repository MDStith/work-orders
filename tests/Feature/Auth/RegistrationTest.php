<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'viewer',
        ]);
    }

    public function test_registration_cannot_assign_dispatcher_role(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Role Test User',
            'email' => 'role-test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'dispatcher',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'role-test@example.com',
            'role' => 'viewer',
        ]);
    }
}

<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_and_wait_for_activation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        // Tài khoản tự đăng ký chưa được kích hoạt và không tự đăng nhập
        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));

        $user = \App\Models\User::where('email', 'test@example.com')->first();
        $this->assertFalse($user->is_active);
        $this->assertTrue($user->hasRole('giao-vien-bo-mon'));
    }
}

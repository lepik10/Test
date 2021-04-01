<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function testApiUserLoginWithWrongAccess()
    {
        $this->seed();
        $credentials = [
            'email' => 'test@test.ru',
            'password' => 'test'
        ];

        $response = $this->post('/api/login', $credentials);
        $response->assertStatus(401)->assertSeeText('Wrong Access');
    }

    public function testApiUserLoginRightAccess()
    {
        $this->seed();
        $user = User::first();
        $credentials = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $response = $this->post('/api/login', $credentials);
        $response->assertStatus(200)->assertExactJson([
            'api_token' => $user->api_token,
        ]);
    }
}

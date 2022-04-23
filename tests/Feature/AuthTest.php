<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function role_points()
    {
        return [
            ["premium", 40],
            ["user", 20],
            ["owner", 0],
        ];
    }
    
    /**
     * @dataProvider role_points
     */
    public function test_user_register($role, $point)
    {
        $response = $this->post('/api/user/register');
        $response = $this->json('POST', '/api/user/register', [
            "name" => "User",
	        "email" => "user@mail.com",
	        "password" => "password",
	        "confirm_password" => "password",
	        "role" => $role,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            "name" => "User",
	        "email" => "user@mail.com",
	        "password" => "password",
        ]);

        if ($point === 0) {
            return;
        }

        $this->assertDatabaseHas('user_points', [
            "point" => $point
        ]);
        $this->assertDatabaseHas('user_points_transaction', [
            "point" => $point,
            "type" => 'add',
        ]);
    }
}

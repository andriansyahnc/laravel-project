<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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
        $user = [
            "name" => $role,
	        "email" => "{$role}@mail.com",
	        "password" => "{$role}_password",
	        "confirm_password" => "{$role}_password",
	        "role" => $role,
        ];
        $response = $this->json('POST', '/api/user/register', $user);
        $response->assertStatus(200);
        
        $content = $response->getContent();
        $content_json = json_decode($content);

        $user_id = $content_json->data->id;

        $this->assertDatabaseHas('users', [
            'id' => $user_id,
            "name" => $user["name"],
	        "email" => $user["email"],
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user_id,
            'tokenable_type' => 'App\Models\User'
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

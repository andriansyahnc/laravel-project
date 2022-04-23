<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\Groups;
use App\Models\User;
use Illuminate\Support\Facades\Http;

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

    public function user_error()
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


    public function test_login_user()
    {
        $user_data = [
            'email' => 'mail@email.com',
            'password' => bcrypt('password'),
        ];
        $user_id = User::factory(App\Model\User::class)->create($user_data);

        $response = $this->json('POST', '/api/user/login', [
            'email' => 'mail@email.com',
            'password' => 'password',
        ]);
        $content = $response->getContent();
        $content_json = json_decode($content);
        $this->assertEquals($content_json->success, true);
    }

    public function test_logout_user()
    {
        $user_data = [
            'email' => 'mail@email.com',
            'password' => bcrypt('password'),
        ];
        $user = User::factory(App\Model\User::class)->create($user_data);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->json('POST', '/api/user/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        
        $content = $response->getContent();
        $content_json = json_decode($content);
        $this->assertEquals($content_json->message, 'You have successfully logged out and the token was successfully deleted');
    }
}

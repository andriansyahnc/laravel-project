<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Groups;
use App\Models\UsersGroups;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function getToken($role = 'user') {
        $user_data = [
            'email' => 'mail@email.com',
            'password' => bcrypt('password'),
        ];
        $user = User::factory(App\Model\User::class)->create($user_data);
        $group = Groups::where('name', 'user')->first();
        $user_group = UsersGroups::factory(App\Model\UsersGroups::class)->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
        return $user->createToken('auth_token', [$role])->plainTextToken;
    }

    public function getHeader($role = 'user') {
        $token = $this->getToken($role);
        return [
            'Authorization' => "Bearer " . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}

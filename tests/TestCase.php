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
        $user = User::factory(User::class)->create($user_data);
        $group = $this->getGroup($role);
        $user_group = UsersGroups::factory(UsersGroups::class)->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
        return $user->createToken('auth_token', [$role])->plainTextToken;
    }

    public function getHeader($role = 'user', $user = null) {
        if ($user === null) {
            $token = $this->getToken($role);
        } else {
            $token = $user->createToken('auth_token', [$role])->plainTextToken;
        }
        return [
            'Authorization' => "Bearer " . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function getGroup($name) {
        return Groups::where('name', $name)->first();
    }

    public function generateOwners($count = 1) {
        $group = $this->getGroup('owner');
        
        $owners = [];
        for ($i = 0; $i < $count; $i++) {
            $user = User::factory(User::class)->create();
            $user_group = UsersGroups::factory(UsersGroups::class)->create([
                'user_id' => $user->id,
                'group_id' => $group->id,
            ]);
            $owners[] = $user;
        }

        if ($count === 1) {
            reset($owners);
        }
        return $owners;
    }
}

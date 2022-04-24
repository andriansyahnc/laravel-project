<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Groups;
use App\Models\UsersGroups;
use App\Models\UserPoints;
use Mockery as m;

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

    public function generateUsers($count = 1, $role_name = 'user', $point = 20) {
        $group = $this->getGroup($role_name);
        
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $user = User::factory(User::class)->create();
            $user_group = UsersGroups::factory(UsersGroups::class)->create([
                'user_id' => $user->id,
                'group_id' => $group->id,
            ]);
            if ($role_name !== 'owner') {
                UserPoints::factory(UserPoints::class)->create([
                    'point' => $point,
                    'user_id' => $user->id,
                ]);
            }
            $users[] = $user;
        }

        if ($count === 1) {
            return reset($users);
        }
        return $users;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }
}

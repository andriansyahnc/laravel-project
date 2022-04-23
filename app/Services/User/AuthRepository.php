<?php

namespace App\Services\User;

use App\Models\Users;
use App\Models\Groups;
use App\Models\UserPoints;

class AuthRepository
{
    public function store($data) 
    {
        $group = Groups::where('name', $data['role'])->first();

        $user = new Users();
        $user->name = $data['name'];
        $user->password = $data['password'];
        $user->email = $data['email'];
        $user->save();

        $user->users_groups()->create([
            'group_id' => $group->id,
        ]);

        if ($data['role'] === 'owner') {
            return $user;
        }

        $points = 20;
        if ($data['role'] === 'premium') {
            $points = 40;
        }

        $user->points()->create([
            'point' => $points,
            'user_id' => $user->id,
        ]);

        $user_point = UserPoints::where('user_id', $user->id)->first();

        $user_point->point_transaction()->create([
            'point' => $points,
            'type' => 'add',
        ]);

        return $user;
    }
}

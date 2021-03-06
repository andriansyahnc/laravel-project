<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\Groups;
use App\Models\UserPoints;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{
    public function store($data) 
    {
        $group = Groups::where('name', $data['role'])->first();

        $user = new User();
        $user->name = $data['name'];
        $user->password = Hash::make($data['password']);
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

    public function getByMail($email)
    {
        return User::where('email', $email)->firstOrFail();
    }
}

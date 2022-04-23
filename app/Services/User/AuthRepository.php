<?php

namespace App\Services\User;

use App\Models\Users;
use App\Models\UsersGroups;
use App\Models\Groups;

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

        return $user;
    }
}

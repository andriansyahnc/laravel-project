<?php

namespace App\Services\UserPoint;

use App\Models\UserPointTransactions;
use App\Models\UserPoints;
use App\Models\User;
use App\Models\Groups;
use App\Models\UsersGroups;

class UserPointRepository {
    
    public function subtract(UserPoints $points, $point) 
    {
        $points->point = $points->point - $point;
        $points->save();
        $this->addUserPointTransaction($points->id, 'deduct', $point);
    }

    public function add(UserPoints $points, $point)
    {
        $points->point = $points->point + $point;
        $points->save();
        $this->addUserPointTransaction($points->id, 'add', $point);
    }

    public function addUserPointTransaction($point_id, $type, $point) {
        $transaction = new UserPointTransactions();
        $transaction->user_points_id = $point_id;
        $transaction->point = $point;
        $transaction->type = $type;
        $transaction->save();
    }

    public function browseUserByRole($role)
    {
        $group = Groups::where('name', $role)->first();
        $user_groups = UsersGroups::where('group_id', $group->id)->get();
        $user_ids = [];
        foreach ($user_groups as $user_group) {
            $user_ids[] = $user_group->user->id;
        }
        return $user_ids;
    }

    public function rechargeAccount($user_ids, $point)
    {
        $user_points = UserPoints::whereIn('user_id', $user_ids)
            ->where('point', '<', $point)->get();
        \Log::info($user_ids);
        foreach ($user_points as $user_point) {
            $recharge_point = $point - $user_point->point;
            $this->add($user_point, $recharge_point);
        }
    }
}
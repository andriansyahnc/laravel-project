<?php

namespace App\Services\UserPoint;

use App\Models\UserPointTransactions;
use App\Models\UserPoints;

class UserPointRepository {
    
    public function subtract(UserPoints $points, $point) 
    {
        $points->point = $points->point - $point;
        $points->save();

        $transaction = new UserPointTransactions();
        $transaction->user_points_id = $points->id;
        $transaction->point = $point;
        $transaction->type = 'deduct';
        $transaction->save();
    }
}
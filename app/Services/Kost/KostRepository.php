<?php

namespace App\Services\Kost;

use App\Models\Kost;

class KostRepository
{
    public function store($data, $user_id) 
    {
        $kost = new Kost();
        $kost->name = $data["name"];
        $kost->description = $data["description"];
        $kost->room_area = $data["room_area"];
        $kost->location = $data["location"];
        $kost->price = $data["price"];
        $kost->user_id = $user_id;
        $kost->save();
        return $kost;
    }

    public function getByMail($email)
    {
        return User::where('email', $email)->firstOrFail();
    }
}

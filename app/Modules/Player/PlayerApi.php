<?php

namespace App\Modules\Player;

use App\Modules\Player\Models;

class PlayerApi
{
    public function create(string $name): Models\Player
    {
        return Models\Player::create([
            'name' => $name,
        ]);
    }
    
    public function exists(int $id): bool
    {
        return Models\Player::where('id', $id)->exists();
    }
}

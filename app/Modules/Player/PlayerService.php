<?php

namespace App\Modules\Player;

use App\Modules\Player\Requests\Commands;
use App\Modules\Player\Requests\Queries;
use App\Modules\Player\Models;

class PlayerService
{
    public function create(Commands\CreatePlayer $command): Models\Player
    {
        return Models\Player::create([
            'name' => $command->name,
        ]);
    }
    
    public function get(Queries\GetPlayer $query): Models\Player
    {
        return Models\Player::findOrFail($query->id);
    }
}

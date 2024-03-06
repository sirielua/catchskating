<?php

namespace App\Modules\Player\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'players';
    
    protected $fillable = [
        'name',
        'endurance',
        'agility',
        'tactics',
        'mmr',
        'points',
        'games_total',
        'games_as_catcher',
        'games_as_runner',
        'wins_total',
        'wins_as_catcher',
        'wins_as_runner',
        'time_catching',
        'time_running',
    ];
    
    protected $attributes = [
        'mmr' => 500,
    ];
}

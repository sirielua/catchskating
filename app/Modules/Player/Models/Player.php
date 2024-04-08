<?php

namespace App\Modules\Player\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Modules\User\Models\User;

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
        'loses_total',
        'loses_as_catcher',
        'loses_as_runner',
        'time_catching',
        'time_running',
        'last_played_at',
    ];
    
    protected $attributes = [
        'mmr' => 500,
    ];
    
    protected $casts = [
        'last_played_at' => 'datetime',
    ];
    
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'player_id');
    }
}

<?php

namespace App\Modules\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Player;

class GamePlayer extends Model
{
    protected $table = 'games_players';
    
    protected $fillable = [
        'game_id',
        'player_id',
        'catcher',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}

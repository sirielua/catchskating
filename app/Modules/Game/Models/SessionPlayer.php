<?php

namespace App\Modules\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Player;

class SessionPlayer extends Model
{
    protected $table = 'game_sessions_players';
    
    protected $fillable = [
        'session_id',
        'player_id',
        'condition',
        'running_streak',
        'resting_streak',
    ];

    protected $attributes = [
        'condition' => PlayerCondition::Ready,
        'running_streak' => 0,
    ];
    
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}

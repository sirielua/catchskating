<?php

namespace App\Modules\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Player\Models\Player;

class SessionPlayer extends Model
{
    protected $table = 'game_sessions_players';
    
    protected $fillable = [
        'session_id',
        'player_id',
        'name',
        'condition',
        'catching_streak',
        'running_streak',
        'resting_streak',
    ];

    protected $attributes = [
        'condition' => PlayerCondition::Ready,
        'catching_streak' => 0,
        'running_streak' => 0,
        'resting_streak' => 0,
    ];
    
    protected $casts = [
        'condition' => PlayerCondition::class,
    ];
    
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id');
    }
    
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}

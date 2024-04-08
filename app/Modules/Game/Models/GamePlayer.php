<?php

namespace App\Modules\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Player\Models\Player;

class GamePlayer extends Model
{
    protected $table = 'games_players';
    
    protected $fillable = [
        'game_id',
        'player_id',
        'role',
        'name',
    ];
    
    protected $casts = [
        'role' => GameRole::class,
    ];
    
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
    
    public function isCatcher(): bool
    {
        return GameRole::Catcher === $this->role; 
    }
    
    public function isRunner(): bool
    {
        return GameRole::Runner === $this->role; 
    }
}

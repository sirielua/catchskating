<?php

namespace App\Modules\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Player\Models\Player;

class Session extends Model
{
    protected $table = 'game_sessions';
    
    protected $fillable = [
        'status',
        'date',
        'description',
        'started_at',
        'ended_at',
    ];
    
    protected $casts = [
        'date' => 'datetime',
        'status' => SessionStatus::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
    
    protected $attributes = [
        'status' => SessionStatus::Pending,
    ];
    
    public function players(): HasMany
    {
        return $this->hasMany(SessionPlayer::class, 'session_id');
    }
    
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'session_id');
    }
    
    public function isPending(): bool
    {
        return SessionStatus::Pending === $this->status;
    }
    
    public function isActive(): bool
    {
        return SessionStatus::Active === $this->status;
    }
    
    public function isEnded(): bool
    {
        return SessionStatus::Ended === $this->status;
    }
    
    public function canBeContinued(): bool
    {
        if (SessionStatus::Ended !== $this->status) {
            return false;
        }
        
        return $this->started_at > (new \DateTimeImmutable('-1 day'));
    }
    
    public function hasPlayer(Player $player): bool
    {
        return in_array($player->id, $this->players->map(function (SessionPlayer $sessionPlayer) {
            return $sessionPlayer->player_id;
        })->all());
    }
}


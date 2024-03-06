<?php

namespace App\Modules\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $table = 'games';
    
    protected $fillable = [
        'session_id',
        'status',
        'catchers_count',
        'runners_count',
        'started_at',
        'stopped_at',
        'completed_at',
        'pause_duration',
        'duration',
        'catchers_win',
    ];
    
    protected $casts = [
        'status' => GameStatus::class,
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function players(): HasMany
    {
        return $this->hasOne(GamePlayer::class, 'game_id');
    }
    
    public function isDraft(): bool
    {
        return GameStatus::Draft === $this->status;
    }
    
    public function isOngoing(): bool
    {
        return GameStatus::Ongoing === $this->status;
    }
    
    public function isCompleted(): bool
    {
        return GameStatus::Completed === $this->status;
    }
    
    public function isAborted(): bool
    {
        return GameStatus::Aborted === $this->status;
    }
    
    public function isCatchersWin(): bool
    {
        return (bool)$this->catchers_win;
    }
    
    public function isRunnersWin(): bool
    {
        return !$this->catchers_win && ($this->catchers_win !== null);
    }
}


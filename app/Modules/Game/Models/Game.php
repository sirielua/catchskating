<?php

namespace App\Modules\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'duration',
        'pause_duration',
        'winner',
    ];
    
    protected $casts = [
        'status' => GameStatus::class,
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'completed_at' => 'datetime',
        'winner' => GameWinner::class,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id');
    }
    
    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'game_id');
    }
    
    public function catchers(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'game_id')->where('role', GameRole::Catcher);
    }
    
    public function runners(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'game_id')->where('role', GameRole::Runner);
    }
    
    public function isDraft(): bool
    {
        return GameStatus::Draft === $this->status;
    }
    
    public function isOngoing(): bool
    {
        return GameStatus::Ongoing === $this->status;
    }
    
    public function isStopped(): bool
    {
        return GameStatus::Stopped === $this->status;
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
        return $this->winner === GameWinner::Catchers;
    }
    
    public function isRunnersWin(): bool
    {
        return $this->winner === GameWinner::Runners;
    }
    
    public function calculateDuration(): ?int
    {
        if (null === $this->started_at) {
            return null;
        }
        
        $stoppedAt = $this->stopped_at?->getTimestamp() ?? time();
        return $stoppedAt - $this->started_at->getTimestamp() - $this->pause_duration;
    }
}


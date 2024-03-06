<?php

namespace App\Modules\Game\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    protected $table = 'game_sessions';
    
    protected $fillable = [
        'name',
        'date',
        'status',
        'opened_at',
        'closed_at',
    ];
    
    protected $casts = [
        'status' => SessionStatus::class,
        'date' => 'datetime',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];
    
    protected $attributes = [
        'status' => SessionStatus::Pending,
    ];
    
    public function players(): HasMany
    {
        return $this->hasOne(SessionPlayer::class, 'session_id');
    }
    
    public function isPending(): bool
    {
        return SessionStatus::Pending === $this->status;
    }
    
    public function isActive(): bool
    {
        return SessionStatus::Active === $this->status;
    }
    
    public function isClosed(): bool
    {
        return SessionStatus::Closed === $this->status;
    }
}


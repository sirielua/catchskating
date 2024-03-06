<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Player\Models\Player;

class TelegramUser extends Model
{
    protected $table = 'telegram_users';
    
    protected $fillable = [
        'telegram_id',
        'player_id',
    ];
    
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'player_id', 'player_id');
    }
    
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}

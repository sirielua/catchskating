<?php

namespace App\Modules\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Hashing\Hasher;

use App\Modules\Player\Models\Player;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'player_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => Role::class,
    ];
    
    protected $attributes = [
        'role' => Role::Player,
    ];
    
    public static function generatePassword(): string
    {
        return substr(md5(rand(0, 1000000)), 0, 5);
    }
    
    public function hashPassword(Hasher $hasher, string $password = null): void
    {
        if (empty($password)) {
            return;
        }

        $this->password = $hasher->needsRehash($password) ? $hasher->make($password) : $password;
    }
    
    public function telegram(): HasOne
    {
        return $this->hasOne(TelegramUser::class, 'player_id', 'player_id');
    }
    
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
    
    public function canOrganizeGames(): bool
    {
        return in_array($this->role, [Role::Organizer, Role::Admin]);
    }
}

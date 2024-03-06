<?php

namespace App\Modules\User;

use App\Modules\User\Requests\Commands;
use App\Modules\User\Models;
use App\Modules\Player\PlayerApi;

class RegistrationService
{
    public function __construct(
        private readonly PlayerApi $players,
    ) {}
    
    public function RegisterTelegramUser(Commands\RegisterTelegramUser $command): Models\TelegramUser
    {
        $this->guardExistsByTelegramId($command->telegramId);
        
        $player = $this->players->create($command->name);
        
        return Models\TelegramUser::create([
            'telegram_id' => $command->telegramId,
            'player_id' => $player->id,
        ]);
    }
    
    public function guardExistsByTelegramId(int $telegramId): void
    {
        if (true === Models\TelegramUser::where('telegram_id', $telegramId)->exists()) {
            throw new \DomainException('Player already registered');
        }
    }
}

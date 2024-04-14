<?php

namespace App\Modules\User;

use App\MessageBus\ApplicationMessageManager;
use Illuminate\Contracts\Hashing\Hasher;
use App\Modules\User\Requests\Commands;
use App\Modules\User\Models;
use App\Modules\Player\Requests\Commands\CreatePlayer as CreatePlayerCommand;

class RegistrationService
{
    public function __construct(
        private readonly ApplicationMessageManager $messageManager,
        private readonly Hasher $hasher,
    ) {}
    
    public function registerTelegramUser(Commands\RegisterTelegramUser $command): Models\TelegramUser
    {
        $this->guardExistsByTelegramId($command->id);
        
        $player = $this->messageManager->dispatch(new CreatePlayerCommand($command->name));
        
        $user = new Models\User([
            'name' => $command->name,
            'role' => Models\Role::Organizer,
            'player_id' => $player->id,
        ]);
        $user->hashPassword($this->hasher, $password = Models\User::generatePassword());
        $user->save();
        
        return Models\TelegramUser::create([
            'user_id' => $user->id,
            'telegram_id' => $command->id,
            'username' => $command->username,
            'first_name' => $command->firstName,
            'last_name' => $command->lastName,
        ]);
    }
    
    public function guardExistsByTelegramId(int $telegramId): void
    {
        if (true === Models\TelegramUser::where('telegram_id', $telegramId)->exists()) {
            throw new \DomainException('Player already registered');
        }
    }
}

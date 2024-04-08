<?php

namespace App\TelegramBot\Traits;

use App\MessageBus\DispatchesMessagesTrait;
use App\Modules\Player\Requests\Queries\GetPlayer;
use App\Modules\User\Requests\Queries\GetUserByTelegram;
use App\Modules\Game\Requests\Session\Queries\GetActualSession;
use App\Modules\Game\Requests\Session\Commands\AddPlayers;
use App\Modules\Game\Requests\Session\Commands\RemovePlayers;
use App\Modules\Game\Requests\Session\Commands\UpdatePlayerCondition;
use App\Modules\Player\Models\Player;
use App\Modules\Game\Models\Session;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait PlayersTrait
{
    use DispatchesMessagesTrait;
    
    public function getPlayer(int $id): ?Player
    {
        try {
            return $this->dispatchMessage(new GetPlayer($id));
        } catch (ModelNotFoundException) {
            return null;
        }
    }
    
    public function getPlayerByTelegram(int $id): ?Player
    {
        try {            
            return $this->dispatchMessage(new GetUserByTelegram($id))->player;
        } catch (ModelNotFoundException) {
            return null;
        }
    }
    
    public function getActualSession(int $playerId): ?Session
    {
        try {
            return $this->dispatchMessage(new GetActualSession($playerId));
        } catch (ModelNotFoundException) {
            return null;
        }
    }
    
    public function joinSession(int $sessionId, int $playerId): Session
    {
        return $this->dispatchMessage(new AddPlayers($sessionId, [$playerId]));
    }
    
    public function leaveSession(int $sessionId, int $playerId): Session
    {
        return $this->dispatchMessage(new RemovePlayers($sessionId, [$playerId]));
    }
    
    public function updateCondition(int $sessionId, int $playerId, string $condition): Session
    {
        return $this->dispatchMessage(
            new UpdatePlayerCondition($sessionId, $playerId, $condition)
        );
    }
}

<?php

namespace App\TelegramBot\Traits;

use App\MessageBus\DispatchesMessagesTrait;
use App\Modules\Game\Requests\Game\Queries;
use App\Modules\Game\Requests\Game\Commands;
use App\Modules\Game\Models\Game;
use App\Modules\Game\Models\GameWinner;

trait GamesTrait
{
    use DispatchesMessagesTrait;
    
    public function getGame(int $id): ?Game
    {
        try {
            return $this->dispatchMessage(new Queries\GetGame($id));
        } catch (ModelNotFoundException) {
            return null;
        }
    }
    
    public function draftGame(int $sessionId, int $catchersCount, int $runnersCount): Game
    {
        return $this->dispatchMessage(
            new Commands\CreateGame($sessionId, $catchersCount, $runnersCount),
        );
    }
    
    public function rerollGame(int $id): Game
    {
        return $this->dispatchMessage(new Commands\RerollGame($id));
    }
    
    public function startGame($id): Game
    {
        return $this->dispatchMessage(new Commands\StartGame($id));
    }
    
    public function stopGame($id): Game
    {
        return $this->dispatchMessage(new Commands\StopGame($id));
    }
    
    public function resumeGame($id): Game
    {
        return $this->dispatchMessage(new Commands\ResumeGame($id));
    }
    
    public function abortGame($id): Game
    {
        return $this->dispatchMessage(new Commands\AbortGame($id));
    }
    
    public function completeGame($id, bool $catchersWon, ?int $duration = null): Game
    {
        $winner = $catchersWon ? GameWinner::Catchers : GameWinner::Runners;
        return $this->dispatchMessage(
            new Commands\CompleteGame($id, $winner, $duration),
        );
    }
    
    public function repeatGame($id): Game
    {
        return $this->dispatchMessage(new Commands\RepeatGame($id));
    }
}

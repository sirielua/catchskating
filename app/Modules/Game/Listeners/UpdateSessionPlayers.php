<?php

namespace App\Modules\Game\Listeners;

use App\Modules\Game\Events\GameCompleted;
use App\Modules\Game\Models;

class UpdateSessionPlayers
{
    public function handle(GameCompleted $event): void
    {
        $game = $event->game;
        if (null === $game->session_id) {
            return;
        }
        
        $session = Models\Session::findOrFail($game->session_id);
        $this->updatePlayers($game, $session);
    }
    
    private function updatePlayers(Models\Game $game, Models\Session $session): void
    {
        $sessionPlayers = $this->getRealSessionPlayers($game, $session);
        foreach ($sessionPlayers as $sessionPlayer) {
            $this->updatePlayerData($sessionPlayer, $game);
        }
        
        $sessionPlayersData = array_map(function (Models\SessionPlayer $player) {
            return [
                'session_id' => $player->session_id,
                'player_id' => $player->player_id,
                'condition' => $player->condition,
                'running_streak' => $player->running_streak,
            ];
        }, $sessionPlayers);
        
        SessionPlayer::upsert($sessionPlayersData, ['session_id', 'player_id']);
    }
    
    private function getRealSessionPlayers(Models\Game $game, Models\Session $session): array
    {
        $sessionPlayers = $session->players()->all();
        $sessionPlayerIds = $session->players->map(function (Models\SessionPlayer $player) {
            return $player->player_id;
        })->all();
        
        foreach ($game->players as $gamePlayer) {
            if (!in_array($gamePlayer->player_id, $sessionPlayerIds)) {
                $sessionPlayers[] = new SessionPlayer([
                    'session_id' => $session->id,
                    'player_id' => $gamePlayer->player_id,
                ]);
            }
        }
    }
    
    private function updatePlayerData(Models\SessionPlayer $sessionPlayer, Models\Game $game): void
    {
        $gamePlayer = $this->getGamePlayerByPlayerId($sessionPlayer->player_id, $game);
        
        $this->updateStreaks($sessionPlayer, $gamePlayer);
        $this->updateCondition($sessionPlayer, $gamePlayer);
    }
    
    private function getGamePlayerByPlayerId($playerId, Models\Game $game): ?Models\GamePlayer
    {
        foreach ($game->players as $player) {
            if ($playerId === $player->player_id) {
                return $player;
            }
        }
        return null;
    }
    
    private function updateStreaks(Models\SessionPlayer $sessionPlayer, ?Models\GamePlayer $gamePlayer): void
    {
        if (null === $gamePlayer) {
            $sessionPlayer->running_streak = 0;
            $sessionPlayer->resting_streak += 1;
        } else {
            $sessionPlayer->running_streak = $gamePlayer->catcher ?
                0 : ($sessionPlayer->running_streak + 1);
            $sessionPlayer->resting_streak = 0;
        }
    }
    
    private function updateCondition(Models\SessionPlayer $sessionPlayer, ?Models\GamePlayer $gamePlayer): void
    {
        if ($gamePlayer?->catcher) {
            $sessionPlayer->condition = Models\PlayerCondition::Running;
        } elseif (
            (Models\PlayerCondition::Resting !== $sessionPlayer->condition)
            && ($sessionPlayer->running_streak + $sessionPlayer->resting_streak >= 2)
        ) {
            $sessionPlayer->condition = Models\PlayerCondition::Ready;
        }
    }
}

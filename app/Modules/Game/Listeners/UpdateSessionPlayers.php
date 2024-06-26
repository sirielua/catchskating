<?php

namespace App\Modules\Game\Listeners;

use App\Modules\Game\Events\GameCompleted;
use App\Modules\Game\Models;
use Illuminate\Support\Collection;

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
        $players = $this->getRealPlayers($game, $session);
        foreach ($players as $player) {
            $this->updatePlayerData($player, $game);
        }
        
        $data = $players->map(function (Models\SessionPlayer $player) {
            return [
                'session_id' => $player->session_id,
                'player_id' => $player->player_id,
                'name' => $player->name,
                'condition' => $player->condition,
                'catching_streak' => $player->catching_streak,
                'running_streak' => $player->running_streak,
                'resting_streak' => $player->resting_streak,
            ];
        })->all();
        
        Models\SessionPlayer::upsert(
            $data,
            ['session_id', 'player_id'],
            ['condition', 'catching_streak', 'running_streak', 'resting_streak'],
        );
    }
    
    private function getRealPlayers(Models\Game $game, Models\Session $session): Collection
    {
        $players = $session->players;
        
        $sessionPlayerIds = $players->map(function (Models\SessionPlayer $player) {
            return $player->player_id;
        })->all();
        
        foreach ($game->players as $gamePlayer) {
            if (!in_array($gamePlayer->player_id, $sessionPlayerIds)) {
                $players->push(new SessionPlayer([
                    'session_id' => $session->id,
                    'player_id' => $gamePlayer->player_id,
                    'name' => $gamePlayer->name,
                ]));
            }
        }
        return $players;
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
            $sessionPlayer->catching_streak = 0;
            $sessionPlayer->running_streak = 0;
            $sessionPlayer->resting_streak += 1;
        } elseif ($gamePlayer->isCatcher()) {
            $sessionPlayer->catching_streak += 1;
            $sessionPlayer->running_streak = 0;
            $sessionPlayer->resting_streak = 0;
        } elseif ($gamePlayer->isRunner()) {
            $sessionPlayer->catching_streak = 0;
            $sessionPlayer->running_streak += 1;
            $sessionPlayer->resting_streak = 0;
        }
    }
    
    private function updateCondition(Models\SessionPlayer $sessionPlayer, ?Models\GamePlayer $gamePlayer): void
    {
        if ($gamePlayer?->isCatcher()) {
            $sessionPlayer->condition = Models\PlayerCondition::Running;
        } elseif (
            (Models\PlayerCondition::Resting !== $sessionPlayer->condition)
            && ($sessionPlayer->running_streak + $sessionPlayer->resting_streak >= 2)
        ) {
            $sessionPlayer->condition = Models\PlayerCondition::Ready;
        }
    }
}

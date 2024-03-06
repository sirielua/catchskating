<?php

namespace App\Modules\Game\MatchMaker;

use App\Modules\Game\Models;
use Illuminate\Support\Collection;

class DefaultMatchMaker implements MatchMakerInterface
{
    #[\Override]
    public function suggestPlayers(Models\Game $game): Collection
    {
        $groupedPlayers = $this->groupPlayersByCondition($game->session->players());
        $suggested = new Collection();
        $suggested->merge($this->fetchCatchers($groupedPlayers, $game->catchers_count));
        $suggested->merge($this->fetchRunners($groupedPlayers, $game->runners_count));
        
        return $suggested->map(function (Models\GamePlayer $player) use ($game) {
            $player->game_id = $game->id;
            return $player;
        });
    }
    
    protected function groupPlayersByCondition(Collection $players): array
    {
        $conditions = [
            Models\PlayerCondition::Catching,
            Models\PlayerCondition::Ready,
            Models\PlayerCondition::Running,
            Models\PlayerCondition::Resting,
        ];
        
        $callback = function (Models\PlayerCondition $condition) use ($players) {
            return $players->filter(function (Models\SessionPlayer $player) use ($condition) {
                return $condition === $player->condition;
            })->all();
        };
        
        return array_map($callback, $conditions);
    }
    
    protected function fetchCatchers(array &$groupedPlayers, int $count): Collection
    {
        return $this->fetchPlayers($groupedPlayers, $count)
            ->map(function (Models\GamePlayer $player) {
                $player->catcher = true;
                return $player;
            });
    }
    
    protected function fetchRunners(array &$groupedPlayers, int $count): Collection
    {
        return $this->fetchPlayers($groupedPlayers, $count)
            ->map(function (Models\GamePlayer $player) {
                $player->catcher = false;
                return $player;
            });
    }
    
    protected function fetchPlayers(array &$groupedPlayers, int $count): Collection
    {
        $players = new Collection();
        $playersLeft = $count;
        
        foreach ($groupedPlayers as $key => $group) {
            $players->merge($group->shuffle()->shift($playersLeft));
            $playersLeft -= $players->count();
            
            if ($group->isEmpty()) {
                unset($groupedPlayers[$key]);
            } else {
                $groupedPlayers[$key] = $group;
            }
            
            if (0 === $playersLeft) {
                break;
            }
        }
        
        return $players->map(function (Models\SessionPlayer $player) {
            return new Models\GamePlayer([
                'player_id' => $player->player_id,
            ]);
        });
    }
}

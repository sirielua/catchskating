<?php

namespace App\Modules\Game\MatchMaker;

use App\Modules\Game\Models;
use Illuminate\Support\Collection;

class DefaultMatchMaker implements MatchMakerInterface
{
    #[\Override]
    public function suggestPlayers(Models\Game $game): Collection
    {
        $groupedPlayers = $this->groupPlayersByCondition($game->session->players);
        
        $catchers = $this->fetchCatchers($groupedPlayers, $game->catchers_count);
        $runners = $this->fetchRunners($groupedPlayers, $game->runners_count);
        
        return $catchers->merge($runners)->map(function (Models\GamePlayer $player) use ($game) {
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
            })->shuffle();
        };
        
        return array_map($callback, $conditions);
    }
    
    protected function fetchCatchers(array &$groupedPlayers, int $count): Collection
    {
        return $this->fetchPlayers($groupedPlayers, $count)
            ->map(function (Models\GamePlayer $player) {
                $player->role = Models\GameRole::Catcher;
                return $player;
            });
    }
    
    protected function fetchRunners(array &$groupedPlayers, int $count): Collection
    {
        return $this->fetchPlayers($groupedPlayers, $count)
            ->map(function (Models\GamePlayer $player) {
                $player->role = Models\GameRole::Runner;
                return $player;
            });
    }
    
    protected function fetchPlayers(array &$groupedPlayers, int $count): Collection
    {
        $players = new Collection();
        $playersLeft = $count;
        
        foreach ($groupedPlayers as $key => $group) {
            if ($group->isEmpty()) {
                unset($groupedPlayers[$key]);
                continue;
            }
            
            if (1 === $playersLeft) {
                $players->push($group->shift($playersLeft));
            } else {
                $players = $players->merge($group->shift($playersLeft));
            }
            
            $playersLeft = $count - $players->count();
            
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
                'name' => $player->name,
                'player_id' => $player->player_id,
            ]);
        });
    }
}

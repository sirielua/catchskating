<?php

namespace App\Modules\Player\Listeners;

use App\Modules\Game\Events\GameCompleted;
use App\Modules\Game\Models\Game;
use App\Modules\Game\Models\GamePlayer;
use App\Modules\Player\Models\Player;
use Illuminate\Support\Facades\DB;

class UpdatePlayers
{
    public function handle(GameCompleted $event): void
    {
        $game = $event->game;
        foreach ($game->players as $gamePlayer) {
            $player = $gamePlayer->player;
            if (null === $player) {
                continue;
            }
            $this->updatePlayer($player, $gamePlayer, $game);
        }
    }
    
    private function updatePlayer(Player $player, GamePlayer $gamePlayer, Game $game): void
    {
        $winAsCatcher = $gamePlayer->isCatcher() && $game->isCatchersWin();
        $winAsRunner = $gamePlayer->isRunner() && $game->isRunnersWin();
        $win = $winAsCatcher || $winAsRunner;
        $lose = !$winAsCatcher && !$winAsRunner;
        
        $increments = array_filter([
            'games_total' => 1,
            'games_as_catcher' => $gamePlayer->isCatcher() ? 1 : 0,
            'games_as_runner' => $gamePlayer->isRunner() ? 1 : 0,
            'wins_total' => $win ? 1 : 0,
            'wins_as_catcher' => $winAsCatcher ? 1 : 0,
            'wins_as_runner' => $winAsRunner ? 1 : 0,
            'loses_total' => $lose ? 1 : 0,
            'loses_as_catcher' => ($lose && $gamePlayer->isCatcher()) ? 1 : 0,
            'loses_as_runner' => ($lose && $gamePlayer->isRunner()) ? 1 : 0,
            'time_catching' => $gamePlayer->isCatcher() ? $game->duration : 0,
            'time_running' => $gamePlayer->isRunner() ? $game->duration : 0,
        ]);
        
        foreach ($increments as $attribute => $value) {
            $increments[$attribute] = DB::raw("`$attribute` + $value");
        }
        
        $player->update(
            array_merge($increments, ['last_played_at' => $game->completed_at])
        );
    }
}

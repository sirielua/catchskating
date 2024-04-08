<?php

namespace App\Modules\Game;

use App\Modules\Game\MatchMaker\MatchMakerInterface;
use App\Modules\Game\Models;
use App\Modules\Game\Requests\Game\Commands;
use App\Modules\Game\Requests\Game\Queries;
use App\Modules\Game\Events;

class GameService
{
    public function __construct(
        private readonly MatchMakerInterface $matchMaker,
    ) {}
    
    public function get(Queries\GetGame $query): Models\Game
    {
        return Models\Game::findOrFail($query->id);
    }
    
    public function create(Commands\CreateGame $command): Models\Game
    {
        $session = Models\Session::findOrFail($command->sessionId);
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsEnded($session);
        
        $lastGame = $session->games->last();
        if ($session->games->last()?->isDraft()) {
            $game = $lastGame;
            $game->update([
                'catchers_count' => $command->catchersCount,
                'runners_count' => $command->runnersCount,
            ]);
            Models\GamePlayer::where('game_id', $game->id)->delete();
        } elseif (is_null($lastGame) || $lastGame?->isCompleted() || $lastGame?->isAborted()) {
            $game = Models\Game::create([
                'session_id' => $session->id,
                'status' => Models\GameStatus::Draft,
                'catchers_count' => $command->catchersCount,
                'runners_count' => $command->runnersCount,
            ]);
        } else {
            throw new \DomainException('To start a new game, a current game must be completed or aborted');
        }
        
        $players = $this->matchMaker->suggestPlayers($game);
        foreach ($players as $player) {
            $player->save();
        }
        return $game;
    }
    
    private function guardGamesCannotBeModifiedIfTheSessionIsEnded(Models\Session $session): void
    {
        if ($session->isEnded()) {
            throw new \DomainException('Games cannot be modified if the session is ended');
        }
    }
    
    public function reroll(Commands\RerollGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isDraft()) {
            throw new \DomainException('A game can be re-rolled only while it is a draft');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsEnded($game->session);
        
        Models\GamePlayer::where('game_id', $game->id)->delete();
        $players = $this->matchMaker->suggestPlayers($game);
        foreach ($players as $player) {
            $player->save();
        }
        return $game;
    }
    
    public function start(Commands\StartGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isDraft()) {
            throw new \DomainException('A game can be started only while it is a draft');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsEnded($game->session);
        
        $game->update([
            'status' => Models\GameStatus::Ongoing,
            'started_at' => new \DateTimeImmutable(),
        ]);
        
        return $game;
    }
    
    public function stop(Commands\StopGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isOngoing()) {
            throw new \DomainException('The game can only be stopped while it is ongoing');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsEnded($game->session);
        
        $game->update([
            'status' => Models\GameStatus::Stopped,
            'stopped_at' => new \DateTimeImmutable(),
            'duration' => $game->calculateDuration(),
        ]);
        
        return $game;
    }
    
    public function resume(Commands\ResumeGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isStopped()) {
            throw new \DomainException('Only a stopped game can be resumed');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsEnded($game->session);
        
        $game->update([
            'status' => Models\GameStatus::Ongoing,
            'stopped_at' => null,
            'pause_duration' => $this->calculatePauseDuration($game),
        ]);
        
        return $game;
    }
    
    private function calculatePauseDuration(Models\Game $game): int
    {
        return $game->pause_duration + (time() - ($game->stopped_at?->getTimestamp() ?? 0));
    }
    
    public function abort(Commands\AbortGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isStopped()) {
            throw new \DomainException('Only a stopped game can be aborted');
        }
        
        $game->update([
            'status' => Models\GameStatus::Aborted,
            'completed_at' => new \DateTimeImmutable(),
            'duration' => $game->calculateDuration(),
        ]);
        
        return $game;
    }
    
    public function complete(Commands\CompleteGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isStopped()) {
            throw new \DomainException('Only a stopped game can be completed');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsEnded($game->session);
        
        $game->update([
            'status' => Models\GameStatus::Completed,
            'completed_at' => new \DateTimeImmutable(),
            'duration' => $command->duration ?? $game->calculateDuration(),
            'winner' => $command->winner,
        ]);
        
        Events\GameCompleted::dispatch($game);
        return $game;
    }
    
    public function repeat(Commands\RepeatGame $command): Models\Game
    {
        $prototype = Models\Game::findOrFail($command->id);
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsEnded($prototype->session);
        
        $game = new Models\Game([
            'session_id' => $prototype->session_id,
            'status' => Models\GameStatus::Draft,
            'catchers_count' => $prototype->catchers_count,
            'runners_count' => $prototype->runners_count,
        ]);
        $game->save();
        
        foreach ($prototype->players as $player) {
            Models\GamePlayer::create([
                'game_id' => $game->id,
                'player_id' => $player->player_id,
                'name' => $player->name,
                'role' => $player->role,
            ]);
        }
        
        return $game;
    }
}

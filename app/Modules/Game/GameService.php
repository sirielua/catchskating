<?php

namespace App\Modules\Game;

use App\Modules\Game\Models;
use App\Modules\Game\Requests\Commands\Game as Commands;
use App\Modules\Game\Events;

class GameService
{
    public function __construct(
        private readonly MatchMakerInterface $matchMaker,
    ) {}
    
    public function init(Commands\InitGame $command): Models\Game
    {
        $session = Models\Session::findOrFail($command->sessionId);
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsClosed($session);
        
        $game = new Models\Game([
            'session_id' => $session->id,
            'status' => Models\GameStatus::Draft,
            'catchers_count' => $command->catchersCount,
            'runners_count' => $command->runnersCount,
        ]);
        $game->save();
        
        $players = $this->matchMaker->suggestPlayers($game);
        foreach ($players as $player) {
            $player->save();
        }
        return $game;
    }
    
    private function guardGamesCannotBeModifiedIfTheSessionIsClosed(Models\Session $session): void
    {
        if ($session->isClosed()) {
            throw new \DomainException('Games cannot be modified if the session is closed');
        }
    }
    
    public function reroll(Commands\RerolGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isDraft()) {
            throw new \DomainException('Only non-draft games can be rerolled');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsClosed($game->session);
        
        if (
            ($game->catchersCount !== $command->catchersCount)
            || ($game->runnersCount !== $command->runnersCount)
        ) {
            $game->update([
                'catchers_count' => $command->catchersCount,
                'runners_count' => $command->runnersCount,
            ]);
        }
        
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
            throw new \DomainException('Only non-draft games can be started');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsClosed($game->session);
        
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
            throw new \DomainException('Only ongoing game can be stopped');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsClosed($game->session);
        
        $game->update([
            'status' => Models\GameStatus::Stopped,
            'stopped_at' => new \DateTimeImmutable(),
        ]);
        
        return $game;
    }
    
    public function resume(Commands\ResumeGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isStopped()) {
            throw new \DomainException('Only stopped game can be resumed');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsClosed($game->session);
        
        $game->update([
            'status' => Models\GameStatus::Ongoing,
            'stopped_at' => null,
            'pause_duration' => $this->calculatePauseDuration($game),
        ]);
        
        return $game;
    }
    
    private function calculatePauseDuration(Models\Game $game): int
    {
        return $game->pause_duration + (time() - $game->stopped_at()->getTimestamp());
    }
    
    public function complete(Commands\CompleteGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isStopped()) {
            throw new \DomainException('Only stopped game can completed');
        }
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsClosed($game->session);
        
        $game->update([
            'status' => Models\GameStatus::Completed,
            'completed_at' => new \DateTimeImmutable(),
            'duration' => $command->duration ?? $this->calculateGameDuration($game),
            'catchersWin' => (bool)$command->catchersWin,
        ]);
        
        Events\GameCompleted::dispatch($game);
        
        return $game;
    }
    
    private function calculateGameDuration(Models\Game $game): int
    {
        return time() - $game->started_at->getTimestamp() - $game->pause_duration;
    }
    
    public function abort(Commands\AbortGame $command): Models\Game
    {
        $game = Models\Game::findOrFail($command->id);
        
        if (!$game->isStopped()) {
            throw new \DomainException('Only stopped game can aborted');
        }
        
        $game->update([
            'status' => Models\GameStatus::Aborted,
            'completed_at' => new \DateTimeImmutable(),
        ]);
        
        return $game;
    }
    
    public function repeat(Commands\RepeatGame $command): Models\Game
    {
        $prototype = Models\Game::findOrFail($command->id);
        
        $this->guardGamesCannotBeModifiedIfTheSessionIsClosed($prototype->session);
        
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
                'catcher' => $player->catcher,
            ]);
        }
        
        return $game;
    }
}

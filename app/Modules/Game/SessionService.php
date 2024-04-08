<?php

namespace App\Modules\Game;

use App\Modules\Game\Requests\Session\Commands;
use App\Modules\Game\Requests\Session\Queries;
use App\Modules\Game\Models;
use App\Modules\Player\Models\Player;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\Paginator;

class SessionService
{
    public function get(Queries\GetSession $query): Models\Session
    {
        return Models\Session::findOrFail($query->id);
    }
    
    public function getActual(Queries\GetActualSession $query): ?Models\Session
    {
        return Models\Session::where('status', '!=', Models\SessionStatus::Ended)
            ->whereHas('players', function (Builder $qb) use ($query) {
                $qb->where('player_id', $query->playerId);
            })
            ->orderBy('date', 'asc')
            ->first();
    }
    
    public function getAvailable(Queries\GetAvailableSessions $query): Paginator
    {
        return Models\Session::where('status', '!=', Models\SessionStatus::Ended)
            ->orderBy('date', 'ASC')
            ->paginate(page: $query->page, perPage: $query->limit ?? 10);
    }
    
    public function create(Commands\CreateSession $command): Models\Session
    {
        $session = new Models\Session([
            'date' => $command->date,
            'description' => $command->description,
        ]);
        
        $this->guardSessionIsNotScheduledInThePast($session);
        
        $session->save();
        return $session;
    }
    
    private function guardSessionIsNotScheduledInThePast(Models\Session $session): void
    {
        if (!$session->isPending()) {
            return;
        }
        
        if ($session->date < (new \DateTimeImmutable())) {
            throw new \DomaineException('Game session can\'t be scheduled in the past');
        }
    }
    
    public function update(Commands\UpdateSession $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        $session->fill(array_filter([
            'date' => $command->date,
            'description' => $command->description,
            'status' => Models\SessionStatus::from($command->status),
        ], function ($value) {
            return null !== $value;
        }));
        
        $this->guardSessionIsNotScheduledInThePast($session);
        $session->save();
        return $session;
    }
    
    public function addPlayers(Commands\AddPlayers $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        $players = Player::whereIn('id', $command->players)->get()->all();
                
        Models\SessionPlayer::upsert(
            array_map(function (Player $player) use ($session) {
                return [
                    'session_id' => $session->id,
                    'player_id' => $player->id,
                    'name' => $player->name,
                    'condition' => Models\PlayerCondition::Ready,
                ];
            }, $players),
            ['session_id', 'player_id'],
        );
        
        return $session;
    }
    
    public function removePlayers(Commands\RemovePlayers $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        Models\SessionPlayer::where('session_id', $session->id)
            ->whereIn('player_id', $command->players)
            ->delete();
        
        return $session;
    }
    
    public function updatePlayerCondition(Commands\UpdatePlayerCondition $command): Models\Session
    {
        $sessionPlayer = Models\SessionPlayer::query()
            ->where('session_id', $command->sessionId)
            ->where('player_id', $command->playerId)
            ->firstOrFail();
        
        $sessionPlayer->update(['condition' => Models\PlayerCondition::from($command->condition)]);
        
        return $sessionPlayer->session;
    }
    
    public function start(Commands\StartSession $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        if ($session->isActive()) {
            return $session;
        }
        
        if (!$session->isPending()) {
            throw new \DomaineException('Only pending sessions can be started');
        }
        
        if ($session->date < (new \DateTimeImmutable('-1 day'))) {
            echo 7;
            throw new \DomaineException('Session scheduled more than 1 day ago can\'t be started');
        }
        
        $session->update([
            'status' => Models\SessionStatus::Active,
            'started_at' => new \DateTimeImmutable(),
        ]);
        
        return $session;
    }
    
    public function end(Commands\EndSession $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        if ($session->isEnded()) {
            return $session;
        }
        
        $session->update([
            'status' => Models\SessionStatus::Ended,
            'ended_at' => new \DateTimeImmutable(),
        ]);
        
        return $session;
    }
    
    public function continue(Commands\ContinueSession $command): Models\Session
    {
        $session = Models\Session::findOrFail($command->id);
        
        if ($session->isActive()) {
            return $session;
        }
        
        if (!$session->isEnded()) {
            throw new \DomaineException('Only ended sessions can be continued');
        }
        
        if (false === $session->canBeContinued()) {
            throw new \DomaineException('The game session started more than 1 day ago and can\'t be continued');
        }
        
        $session->update([
            'status' => Models\SessionStatus::Active,
            'ended_at' => null,
        ]);
        
        return $session;
    }
}
